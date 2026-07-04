<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\BuildsQuestionBankIndex;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Services\QuestionPackImport\QuestionPackParseException;
use App\Services\QuestionPackImport\QuestionPackParserFactory;
use App\Services\QuestionPackImport\QuestionPackPersister;
use App\Support\QuestionImportCurriculumValidator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuestionPackImportController extends Controller
{
    use BuildsQuestionBankIndex;

    public function __construct()
    {
        $this->middleware(['permission:question-import']);
    }

    public function parse(Request $request, QuestionPackParserFactory $parserFactory)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', $this->packFileRule()],
            'format' => ['required', Rule::in(['md', 'csv'])],
            'target_type' => ['required', Rule::in(['single_choice', 'fill_blanks'])],
        ]);

        try {
            $questions = $parserFactory->parseUploadedFile(
                $request->file('file'),
                $request->input('format'),
                $request->input('target_type')
            );

            return response()->json([
                'count' => count($questions),
                'target_type' => $request->input('target_type'),
                'questions' => collect($questions)->map(fn ($q) => $q->toPreviewArray())->values(),
            ]);
        } catch (QuestionPackParseException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function import(
        Request $request,
        QuestionPackParserFactory $parserFactory,
        QuestionPackPersister $persister
    ) {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', $this->packFileRule()],
            'format' => ['required', Rule::in(['md', 'csv'])],
            'target_type' => ['required', Rule::in(['single_choice', 'fill_blanks'])],
            'class_id' => ['nullable', 'exists:classes,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
        ]);

        $subjectId = $request->filled('subject_id') ? (int) $request->input('subject_id') : null;

        if (! $subjectId) {
            return redirect()
                ->back()
                ->with('error', 'اختر المادة من قسم «الربط بالمنهج» قبل الاستيراد.');
        }

        $subject = Subject::find($subjectId);
        if (! $subject) {
            return redirect()->back()->with('error', 'المادة المحددة غير صالحة.');
        }

        $this->authorizeManagedSubjectAccess(auth()->user(), $subject);

        $curriculumError = QuestionImportCurriculumValidator::validate(
            $request->input('class_id'),
            (string) $subjectId,
            $request->input('unit_id')
        );
        if ($curriculumError) {
            return redirect()->back()->withErrors(['subject_id' => $curriculumError]);
        }

        $unitId = $request->filled('unit_id') ? (int) $request->input('unit_id') : null;
        $targetType = $request->input('target_type');
        $typeLabel = $targetType === 'fill_blanks' ? 'املأ الفراغ' : 'اختيار من متعدد';

        try {
            $questions = $parserFactory->parseUploadedFile(
                $request->file('file'),
                $request->input('format'),
                $targetType
            );
            $result = $persister->persist($questions, $subjectId, $unitId, (int) auth()->id());
            $count = $result['count'];

            return redirect()
                ->route('admin.subjects.questions.index', $subjectId)
                ->with('success', "تم استيراد {$count} سؤال ({$typeLabel}) من الحزمة بنجاح.")
                ->with('import_summary', [
                    'success' => $count,
                    'errors' => 0,
                    'total' => $count,
                ]);
        } catch (QuestionPackParseException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->with('error', 'حدث خطأ أثناء الاستيراد: '.$e->getMessage());
        }
    }

    protected function packFileRule(): \Closure
    {
        return function (string $attribute, $value, \Closure $fail): void {
            $ext = strtolower($value->getClientOriginalExtension());
            if (! in_array($ext, ['md', 'csv', 'txt'], true)) {
                $fail('الصيغ المدعومة: .md أو .csv فقط.');
            }
        };
    }
}
