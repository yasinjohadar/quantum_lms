<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\BuildsQuestionBankIndex;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Services\NerveTestImport\NerveTestParseException;
use App\Services\NerveTestImport\NerveTestParserFactory;
use App\Services\NerveTestImport\NerveTestQuestionPersister;
use App\Support\QuestionImportCurriculumValidator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NerveTestQuestionImportController extends Controller
{
    use BuildsQuestionBankIndex;

    public function __construct()
    {
        $this->middleware(['permission:question-import']);
    }

    public function parse(Request $request, NerveTestParserFactory $parserFactory)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', $this->nerveTestFileRule()],
            'format' => ['required', Rule::in(['md', 'csv'])],
        ]);

        try {
            $questions = $parserFactory->parseUploadedFile($request->file('file'), $request->input('format'));

            return response()->json([
                'count' => count($questions),
                'questions' => collect($questions)->map(fn ($q) => $q->toPreviewArray())->values(),
            ]);
        } catch (NerveTestParseException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function import(
        Request $request,
        NerveTestParserFactory $parserFactory,
        NerveTestQuestionPersister $persister
    ) {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', $this->nerveTestFileRule()],
            'format' => ['required', Rule::in(['md', 'csv'])],
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

        try {
            $questions = $parserFactory->parseUploadedFile($request->file('file'), $request->input('format'));
            $count = $persister->persist($questions, $subjectId, $unitId, (int) auth()->id());

            return redirect()
                ->route('admin.subjects.questions.index', $subjectId)
                ->with('success', "تم استيراد {$count} سؤال من حزمة اختبار الأعصاب بنجاح.")
                ->with('import_summary', [
                    'success' => $count,
                    'errors' => 0,
                    'total' => $count,
                ]);
        } catch (NerveTestParseException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->with('error', 'حدث خطأ أثناء الاستيراد: '.$e->getMessage());
        }
    }

    protected function nerveTestFileRule(): \Closure
    {
        return function (string $attribute, $value, \Closure $fail): void {
            $ext = strtolower($value->getClientOriginalExtension());
            if (! in_array($ext, ['md', 'csv', 'txt'], true)) {
                $fail('الصيغ المدعومة: .md أو .csv فقط.');
            }
        };
    }
}
