<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AssignmentSubmissionFile;
use App\Models\AssignmentSubmissionAnswer;
use App\Models\User;
use App\Services\AssignmentGradingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AssignmentSubmissionService
{
    public function __construct(
        private AssignmentGradingService $gradingService
    ) {}

    /**
     * إنشاء إرسال جديد (مسودة)
     */
    public function createSubmission(Assignment $assignment, User $student): AssignmentSubmission
    {
        // الحصول على رقم المحاولة التالي
        $attemptNumber = $assignment->submissions()
            ->where('student_id', $student->id)
            ->max('attempt_number') ?? 0;
        $attemptNumber++;

        return AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'attempt_number' => $attemptNumber,
            'status' => AssignmentSubmission::STATUS_DRAFT,
        ]);
    }

    /**
     * إرسال الواجب
     */
    public function submitAssignment(Assignment $assignment, User $student, array $data): AssignmentSubmission
    {
        return DB::transaction(function () use ($assignment, $student, $data) {
            // إنشاء أو الحصول على الإرسال
            $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
                ->where('student_id', $student->id)
                ->where('status', AssignmentSubmission::STATUS_DRAFT)
                ->latest()
                ->first();

            if (!$submission) {
                $submission = $this->createSubmission($assignment, $student);
            }

            // رفع الملفات
            if (isset($data['files']) && is_array($data['files'])) {
                $this->uploadFiles($submission, $data['files'], $assignment);
            }

            // حفظ الإجابات (إن وجدت)
            if (isset($data['answers']) && is_array($data['answers'])) {
                $this->saveAnswers($submission, $data['answers']);
            }

            // تحديث حالة الإرسال
            $isLate = $assignment->due_date && now()->isAfter($assignment->due_date);
            
            $submission->update([
                'submitted_at' => now(),
                'is_late' => $isLate,
                'status' => AssignmentSubmission::STATUS_SUBMITTED,
            ]);

            // التصحيح التلقائي (إن كان مطلوباً)
            if (in_array($assignment->grading_type, ['auto', 'mixed'])) {
                $this->autoGradeSubmission($submission);
            }

            Log::info('Assignment submitted', [
                'submission_id' => $submission->id,
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
            ]);

            // إرسال Event للإشعارات والـ Gamification
            \Illuminate\Support\Facades\Event::dispatch(
                new \App\Events\AssignmentSubmitted($student, $assignment, $submission, [
                    'points' => 0, // سيتم حسابها من قبل GamificationService
                ])
            );

            // منح نقاط من خلال GamificationService
            if (class_exists(\App\Services\GamificationService::class)) {
                app(\App\Services\GamificationService::class)->processEvent(
                    $student,
                    'assignment_submitted',
                    [
                        'assignment_id' => $assignment->id,
                        'submission_id' => $submission->id,
                    ]
                );
            }

            return $submission->fresh();
        });
    }

    /**
     * إعادة إرسال الواجب
     */
    public function resubmitAssignment(Assignment $assignment, User $student, array $data): AssignmentSubmission
    {
        // التحقق من إمكانية إعادة الإرسال
        $lastSubmission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->latest()
            ->first();

        if (!$lastSubmission || !$lastSubmission->canResubmit()) {
            throw new \Exception('لا يمكن إعادة إرسال هذا الواجب');
        }

        // إنشاء إرسال جديد
        return $this->submitAssignment($assignment, $student, $data);
    }

    /**
     * رفع ملفات الإرسال
     */
    private function uploadFiles(AssignmentSubmission $submission, array $files, Assignment $assignment): void
    {
        $order = 0;
        foreach ($files as $file) {
            if (!$file->isValid()) {
                continue;
            }

            // التحقق من نوع الملف
            $fileExtension = strtolower($file->getClientOriginalExtension());
            if (!$assignment->isFileTypeAllowed($fileExtension)) {
                throw new \Exception("نوع الملف '{$fileExtension}' غير مسموح");
            }

            // التحقق من حجم الملف
            $fileSizeMB = $file->getSize() / 1024 / 1024;
            if ($fileSizeMB > $assignment->max_file_size) {
                throw new \Exception("حجم الملف يتجاوز الحد المسموح ({$assignment->max_file_size} MB)");
            }

            // رفع الملف
            $fileName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs(
                "assignments/{$assignment->id}/submissions/{$submission->id}",
                $fileName,
                'public'
            );

            AssignmentSubmissionFile::create([
                'submission_id' => $submission->id,
                'file_path' => $filePath,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $fileExtension,
                'file_size' => $file->getSize(),
                'order' => $order++,
            ]);
        }
    }

    /**
     * حفظ إجابات الطلاب
     */
    private function saveAnswers(AssignmentSubmission $submission, array $answers): void
    {
        foreach ($answers as $questionId => $answer) {
            AssignmentSubmissionAnswer::updateOrCreate(
                [
                    'submission_id' => $submission->id,
                    'question_id' => $questionId,
                ],
                [
                    'answer' => $answer,
                ]
            );
        }
    }

    /**
     * تصحيح تلقائي للإرسال
     */
    public function autoGradeSubmission(AssignmentSubmission $submission): void
    {
        $this->gradingService->autoGradeQuestions($submission);
    }

    /**
     * تصحيح يدوي للإرسال
     */
    public function manualGradeSubmission(AssignmentSubmission $submission, array $data): void
    {
        $this->gradingService->manualGradeSubmission($submission, $data);
    }

    /**
     * حساب الدرجة النهائية
     */
    public function calculateFinalScore(AssignmentSubmission $submission): float
    {
        return $this->gradingService->calculateFinalScore($submission);
    }

    /**
     * التحقق من إمكانية إعادة الإرسال
     */
    public function canResubmit(Assignment $assignment, User $student): bool
    {
        $lastSubmission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->latest()
            ->first();

        if (!$lastSubmission) {
            return true; // لم يتم إرسال أي محاولة بعد
        }

        return $lastSubmission->canResubmit();
    }
}

