<?php

namespace App\Services;

use App\Http\Controllers\Admin\QuizController;
use App\Imports\QuestionsImport;
use App\Models\Quiz;
use App\Models\Subject;
use App\Models\Unit;
use App\Support\QuestionImportCurriculumValidator;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class QuizExcelQuestionImportService
{
    /**
     * @return array{
     *     success_count: int,
     *     error_count: int,
     *     attached_count: int,
     *     errors: array<int, mixed>,
     *     total: int
     * }
     */
    public function importAndAttach(Quiz $quiz, UploadedFile $file, array $columnMapping): array
    {
        $quiz->loadMissing(['subject', 'unit.section']);

        $subjectId = $this->resolveSubjectId($quiz);
        if (! $subjectId) {
            throw ValidationException::withMessages([
                'subject_id' => 'يجب تحديد المادة في بيانات الاختبار قبل استيراد الأسئلة.',
            ]);
        }

        $classId = $quiz->subject?->class_id;
        $unitId = $quiz->unit_id;

        $curriculumError = QuestionImportCurriculumValidator::validate(
            $classId ? (string) $classId : null,
            (string) $subjectId,
            $unitId ? (string) $unitId : null,
        );

        if ($curriculumError) {
            throw ValidationException::withMessages([
                'subject_id' => $curriculumError,
            ]);
        }

        $import = new QuestionsImport($columnMapping, $subjectId, $unitId);
        Excel::import($import, $file);

        $successCount = $import->getSuccessCount();
        $errorCount = $import->getErrorCount();
        $errors = $import->getErrors();
        $attachedCount = 0;

        if ($successCount > 0) {
            $attachedCount = QuizController::attachQuestionsToQuiz(
                $quiz->id,
                $import->getCreatedQuestionIds()
            );
            $quiz->refresh()->calculateTotalPoints();
        }

        return [
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'attached_count' => $attachedCount,
            'errors' => $errors,
            'total' => $successCount + $errorCount,
        ];
    }

    protected function resolveSubjectId(Quiz $quiz): ?int
    {
        if ($quiz->subject_id) {
            return (int) $quiz->subject_id;
        }

        if ($quiz->unit_id) {
            $unit = $quiz->relationLoaded('unit')
                ? $quiz->unit
                : Unit::with('section')->find($quiz->unit_id);

            return $unit?->section?->subject_id;
        }

        return null;
    }

    public function quizCurriculumForImport(Quiz $quiz): array
    {
        $quiz->loadMissing(['subject', 'unit']);

        $subjectId = $this->resolveSubjectId($quiz);
        $subject = $subjectId ? Subject::find($subjectId) : null;

        return [
            'subject_id' => $subjectId,
            'class_id' => $subject?->class_id,
            'unit_id' => $quiz->unit_id,
            'can_import' => $subjectId !== null,
        ];
    }
}
