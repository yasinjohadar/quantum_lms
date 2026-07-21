<?php

namespace App\Http\Controllers\Admin;

use App\DataTransferObjects\QuestionPack\QuestionPackQuestionData;
use App\Http\Controllers\Admin\Concerns\BuildsQuestionBankIndex;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Services\MathQuestionImport\MathQuestionPackPersister;
use App\Services\QuestionPackImport\QuestionPackParseException;
use App\Services\QuestionPackImport\QuestionPackParserFactory;
use App\Support\QuestionImportCurriculumValidator;
use App\Support\QuestionMarkupFormatter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * استيراد مخصّص لأسئلة الرياضيات من ملفات بصيغة Question/Hint/Option A-D/
 * Correct Answer/Rationale (نفس تنسيق حزمة الأسئلة) — يفرض النوع "اختيار واحد"
 * دائماً، ويعرض معاينة مرسومة عبر KaTeX قبل التأكيد حتى يتأكد الأدمن من ظهور
 * المعادلات بشكل صحيح قبل الحفظ.
 */
class MathQuestionImportController extends Controller
{
    use BuildsQuestionBankIndex;

    public function __construct()
    {
        $this->middleware(['permission:question-import']);
    }

    public function parse(Request $request, QuestionPackParserFactory $parserFactory)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', $this->mathFileRule()],
            'format' => ['required', Rule::in(['md', 'csv'])],
        ]);

        try {
            $questions = $parserFactory->parseUploadedFile(
                $request->file('file'),
                $request->input('format'),
                'single_choice'
            );

            $previews = collect($questions)->map(fn ($q) => $this->toMathPreviewArray($q))->values();

            return response()->json([
                'count' => count($questions),
                'questions' => $previews,
                'suspicious_count' => $previews->filter(fn ($p) => $p['has_warning'])->count(),
            ]);
        } catch (QuestionPackParseException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function import(
        Request $request,
        QuestionPackParserFactory $parserFactory,
        MathQuestionPackPersister $persister
    ) {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', $this->mathFileRule()],
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
            $questions = $parserFactory->parseUploadedFile(
                $request->file('file'),
                $request->input('format'),
                'single_choice'
            );
            $suspiciousCount = collect($questions)->filter(
                fn ($q) => $this->toMathPreviewArray($q)['has_warning']
            )->count();

            $result = $persister->persist($questions, $subjectId, $unitId, (int) auth()->id());
            $count = $result['count'];

            $message = "تم استيراد {$count} سؤال رياضيات بنجاح، مع تنسيق المعادلات لعرضها عبر KaTeX.";
            if ($suspiciousCount > 0) {
                $message .= " تنبيه: {$suspiciousCount} منها يحتوي معادلة يصعب تفسيرها تلقائياً — راجعها أو شغّل أداة «إصلاح عرض الرياضيات (AI)».";
            }

            return redirect()
                ->route('admin.subjects.questions.index', $subjectId)
                ->with($suspiciousCount > 0 ? 'warning' : 'success', $message)
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

    /**
     * يبني صفاً للمعاينة يحتوي HTML مُنسَّقاً جاهزاً لعرضه عبر KaTeX
     * (نفس خطوتي normalizeForStorage + format_question_markup المُطبَّقتين وقت
     * الحفظ والعرض الفعليين) بدل النص الخام، ليتأكد الأدمن من صحة المعادلات
     * قبل تأكيد الاستيراد.
     */
    protected function toMathPreviewArray(QuestionPackQuestionData $dto): array
    {
        $titleStored = QuestionMarkupFormatter::deepNormalizeForStorage($dto->title);
        $hintStored = QuestionMarkupFormatter::deepNormalizeForStorage($dto->hint);
        $explanationStored = QuestionMarkupFormatter::deepNormalizeForStorage($dto->explanation);

        $hasWarning = QuestionMarkupFormatter::hasSuspiciousBareLatex($titleStored)
            || QuestionMarkupFormatter::hasSuspiciousBareLatex($hintStored)
            || QuestionMarkupFormatter::hasSuspiciousBareLatex($explanationStored);

        $options = [];
        foreach (['A', 'B', 'C', 'D'] as $letter) {
            if (empty($dto->options[$letter])) {
                continue;
            }

            $stored = QuestionMarkupFormatter::deepNormalizeForStorage($dto->options[$letter]);
            $optionWarning = QuestionMarkupFormatter::hasSuspiciousBareLatex($stored);
            $hasWarning = $hasWarning || $optionWarning;

            $options[] = [
                'letter' => $letter,
                'is_correct' => strtoupper($dto->correctLetter) === $letter,
                'html' => format_question_markup($stored),
                'has_warning' => $optionWarning,
            ];
        }

        return [
            'number' => $dto->number,
            'title_html' => format_question_markup($titleStored),
            'hint_html' => $hintStored !== '' ? format_question_markup($hintStored) : null,
            'explanation_html' => $explanationStored !== '' ? format_question_markup($explanationStored) : null,
            'options' => $options,
            'correct_letter' => strtoupper($dto->correctLetter),
            /**
             * صحيح إن احتوى أي جزء من هذا السؤال (بعد التطبيع) على أمر LaTeX عارٍ
             * مشتبه به (مثل "frac2..." الملتصقة) لا يمكن إصلاحه بثقة عبر الأنماط
             * النصية وحدها — يحتاج مراجعة يدوية أو تشغيل أداة «إصلاح AI» بعد
             * الاستيراد (Admin ▸ بنك الأسئلة ▸ إصلاح عرض الرياضيات) حتى لا يبقى
             * السؤال معطلاً بصمت للطالب.
             */
            'has_warning' => $hasWarning,
        ];
    }

    protected function mathFileRule(): \Closure
    {
        return function (string $attribute, $value, \Closure $fail): void {
            $ext = strtolower($value->getClientOriginalExtension());
            if (! in_array($ext, ['md', 'csv', 'txt'], true)) {
                $fail('الصيغ المدعومة: .md أو .csv فقط.');
            }
        };
    }
}
