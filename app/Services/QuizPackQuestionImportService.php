<?php

namespace App\Services;

use App\Http\Controllers\Admin\QuizController;
use App\Models\Quiz;
use App\Services\MathQuestionImport\MathQuestionPackPersister;
use App\Services\NerveTestImport\NerveTestParseException;
use App\Services\NerveTestImport\NerveTestParserFactory;
use App\Services\NerveTestImport\NerveTestQuestionPersister;
use App\Services\QuestionPackImport\QuestionPackParseException;
use App\Services\QuestionPackImport\QuestionPackParserFactory;
use App\Services\QuestionPackImport\QuestionPackPersister;
use App\Support\QuestionMarkupFormatter;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class QuizPackQuestionImportService
{
    public function __construct(
        protected QuizExcelQuestionImportService $quizExcelQuestionImportService,
        protected NerveTestParserFactory $nerveTestParserFactory,
        protected NerveTestQuestionPersister $nerveTestQuestionPersister,
        protected QuestionPackParserFactory $questionPackParserFactory,
        protected QuestionPackPersister $questionPackPersister,
        protected MathQuestionPackPersister $mathQuestionPackPersister,
    ) {}

    /**
     * @return array{count: int, attached_count: int, question_ids: array<int, int>}
     */
    public function importNerveTestAndAttach(Quiz $quiz, UploadedFile $file, string $format): array
    {
        $curriculum = $this->resolveCurriculum($quiz);

        try {
            $questions = $this->nerveTestParserFactory->parseUploadedFile($file, $format);
        } catch (NerveTestParseException $e) {
            throw ValidationException::withMessages(['file' => $e->getMessage()]);
        }

        $result = $this->nerveTestQuestionPersister->persist(
            $questions,
            $curriculum['subject_id'],
            $curriculum['unit_id'],
            (int) auth()->id()
        );

        return $this->attachPersistedQuestions($quiz, $result);
    }

    /**
     * @return array{count: int, attached_count: int, question_ids: array<int, int>}
     */
    public function importQuestionPackAndAttach(
        Quiz $quiz,
        UploadedFile $file,
        string $format,
        string $targetType
    ): array {
        $curriculum = $this->resolveCurriculum($quiz);

        try {
            $questions = $this->questionPackParserFactory->parseUploadedFile($file, $format, $targetType);
        } catch (QuestionPackParseException $e) {
            throw ValidationException::withMessages(['file' => $e->getMessage()]);
        }

        $result = $this->questionPackPersister->persist(
            $questions,
            $curriculum['subject_id'],
            $curriculum['unit_id'],
            (int) auth()->id()
        );

        return $this->attachPersistedQuestions($quiz, $result);
    }

    /**
     * استيراد أسئلة رياضيات (اختيار واحد A–D مع تنسيق KaTeX) وربطها بالاختبار
     * — نفس منطق MathQuestionImportController::import لكن مع الربط بالاختبار
     * بدل التوجيه لبنك الأسئلة.
     *
     * @return array{count: int, attached_count: int, question_ids: array<int, int>, suspicious_count: int}
     */
    public function importMathAndAttach(Quiz $quiz, UploadedFile $file, string $format): array
    {
        $curriculum = $this->resolveCurriculum($quiz);

        try {
            $questions = $this->questionPackParserFactory->parseUploadedFile($file, $format, 'single_choice');
        } catch (QuestionPackParseException $e) {
            throw ValidationException::withMessages(['file' => $e->getMessage()]);
        }

        $suspiciousCount = collect($questions)->filter(
            fn ($dto) => $this->questionHasSuspiciousBareLatex($dto)
        )->count();

        $result = $this->mathQuestionPackPersister->persist(
            $questions,
            $curriculum['subject_id'],
            $curriculum['unit_id'],
            (int) auth()->id()
        );

        return [
            ...$this->attachPersistedQuestions($quiz, $result),
            'suspicious_count' => $suspiciousCount,
        ];
    }

    /**
     * هل يحتوي هذا السؤال (بعد التطبيع) على أمر LaTeX عارٍ مشتبه به لا يمكن
     * إصلاحه بثقة عبر الأنماط النصية وحدها (مثل "frac2..." ملتصقة)؟ نفس فحص
     * MathQuestionImportController::toMathPreviewArray، مستخرَج هنا لإظهار
     * تنبيه للأدمن بعد الاستيراد مباشرة إلى الاختبار (بدون معاينة KaTeX منفصلة).
     */
    private function questionHasSuspiciousBareLatex(\App\DataTransferObjects\QuestionPack\QuestionPackQuestionData $dto): bool
    {
        $fields = [$dto->title, $dto->hint, $dto->explanation, ...array_values($dto->options)];

        foreach ($fields as $field) {
            if (QuestionMarkupFormatter::hasSuspiciousBareLatex(QuestionMarkupFormatter::deepNormalizeForStorage($field))) {
                return true;
            }
        }

        return false;
    }

    public function quizCurriculumForImport(Quiz $quiz): array
    {
        return $this->quizExcelQuestionImportService->quizCurriculumForImport($quiz);
    }

    /**
     * @return array{subject_id: int, unit_id: int|null, class_id: int|null}
     */
    protected function resolveCurriculum(Quiz $quiz): array
    {
        $curriculum = $this->quizCurriculumForImport($quiz);

        if (! $curriculum['can_import'] || ! $curriculum['subject_id']) {
            throw ValidationException::withMessages([
                'subject_id' => 'يجب تحديد المادة في بيانات الاختبار قبل استيراد الأسئلة.',
            ]);
        }

        return [
            'subject_id' => (int) $curriculum['subject_id'],
            'unit_id' => $curriculum['unit_id'] ? (int) $curriculum['unit_id'] : null,
            'class_id' => $curriculum['class_id'] ? (int) $curriculum['class_id'] : null,
        ];
    }

    /**
     * @param  array{count: int, question_ids: array<int, int>}  $result
     * @return array{count: int, attached_count: int, question_ids: array<int, int>}
     */
    protected function attachPersistedQuestions(Quiz $quiz, array $result): array
    {
        $attachedCount = 0;

        if ($result['count'] > 0) {
            $attachedCount = QuizController::attachQuestionsToQuiz(
                $quiz->id,
                $result['question_ids']
            );
            $quiz->refresh()->calculateTotalPoints();
        }

        return [
            'count' => $result['count'],
            'attached_count' => $attachedCount,
            'question_ids' => $result['question_ids'],
        ];
    }
}
