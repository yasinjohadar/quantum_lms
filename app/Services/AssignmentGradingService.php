<?php

namespace App\Services;

use App\Models\AssignmentSubmission;
use App\Models\AssignmentSubmissionAnswer;
use App\Models\AssignmentGrade;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignmentGradingService
{
    /**
     * تصحيح إرسال كامل
     */
    public function gradeSubmission(AssignmentSubmission $submission, array $data, User $grader): AssignmentSubmission
    {
        return DB::transaction(function () use ($submission, $data, $grader) {
            $assignment = $submission->assignment;

            // التصحيح التلقائي (إن كان مطلوباً)
            if (in_array($assignment->grading_type, ['auto', 'mixed'])) {
                $this->autoGradeQuestions($submission);
            }

            // التصحيح اليدوي (إن كان مطلوباً)
            if (in_array($assignment->grading_type, ['manual', 'mixed'])) {
                $this->manualGradeSubmission($submission, $data);
            }

            // حساب الدرجة النهائية
            $totalScore = $this->calculateFinalScore($submission);

            // تطبيق خصم التأخير
            if ($submission->is_late && $assignment->allow_late_submission) {
                $penalty = ($totalScore * $assignment->late_penalty_percentage) / 100;
                $totalScore -= $penalty;
                $totalScore = max(0, $totalScore); // التأكد من عدم وجود درجات سالبة
            }

            // حساب النسبة المئوية
            $gradePercentage = ($totalScore / $assignment->max_score) * 100;

            // تحديث الإرسال
            $submission->update([
                'total_score' => $totalScore,
                'grade_percentage' => $gradePercentage,
                'graded_at' => now(),
                'graded_by' => $grader->id,
                'status' => AssignmentSubmission::STATUS_GRADED,
                'feedback' => $data['feedback'] ?? null,
            ]);

            Log::info('Assignment graded', [
                'submission_id' => $submission->id,
                'total_score' => $totalScore,
                'grader_id' => $grader->id,
            ]);

            // إرسال Event للإشعارات
            \Illuminate\Support\Facades\Event::dispatch(
                new \App\Events\AssignmentGraded($submission->student, $assignment, $submission, [
                    'score' => $totalScore,
                    'max_score' => $assignment->max_score,
                ])
            );

            // منح نقاط عند إكمال الواجب (إذا كانت الدرجة جيدة)
            if (class_exists(\App\Services\GamificationService::class)) {
                $gradePercentage = ($totalScore / $assignment->max_score) * 100;
                if ($gradePercentage >= 60) { // إذا كانت النسبة 60% أو أكثر
                    app(\App\Services\GamificationService::class)->processEvent(
                        $submission->student,
                        'assignment_completed',
                        [
                            'assignment_id' => $assignment->id,
                            'submission_id' => $submission->id,
                            'score' => $totalScore,
                            'percentage' => $gradePercentage,
                        ]
                    );
                }
            }

            return $submission->fresh();
        });
    }

    /**
     * تصحيح تلقائي للأسئلة
     */
    public function autoGradeQuestions(AssignmentSubmission $submission): void
    {
        $answers = $submission->answers;

        foreach ($answers as $answer) {
            if (!$answer->auto_graded_at) {
                $answer->autoGrade();
            }
        }
    }

    /**
     * تصحيح يدوي للإرسال
     */
    public function manualGradeSubmission(AssignmentSubmission $submission, array $data): void
    {
        // حذف الدرجات اليدوية السابقة
        $submission->grades()->delete();

        // إنشاء درجات يدوية جديدة
        if (isset($data['criteria']) && is_array($data['criteria'])) {
            AssignmentGrade::create([
                'submission_id' => $submission->id,
                'criteria' => $data['criteria'],
                'manual_score' => $data['manual_score'] ?? 0,
                'comments' => $data['comments'] ?? null,
                'graded_by' => auth()->id(),
                'graded_at' => now(),
            ]);
        } elseif (isset($data['manual_score'])) {
            // إذا تم إعطاء درجة مباشرة بدون معايير
            AssignmentGrade::create([
                'submission_id' => $submission->id,
                'manual_score' => $data['manual_score'],
                'comments' => $data['comments'] ?? null,
                'graded_by' => auth()->id(),
                'graded_at' => now(),
            ]);
        }
    }

    /**
     * تطبيق خصم التأخير
     */
    public function applyLatePenalty(AssignmentSubmission $submission): float
    {
        $assignment = $submission->assignment;

        if (!$submission->is_late || !$assignment->allow_late_submission) {
            return 0.0;
        }

        $totalScore = $this->calculateFinalScore($submission);
        $penalty = ($totalScore * $assignment->late_penalty_percentage) / 100;

        return (float) $penalty;
    }

    /**
     * حساب الدرجة النهائية
     */
    public function calculateFinalScore(AssignmentSubmission $submission): float
    {
        $totalScore = 0.0;

        // جمع درجات الأسئلة التلقائية
        $autoScore = $submission->answers()->sum('points_earned');
        $totalScore += $autoScore;

        // جمع الدرجات اليدوية
        $manualScore = $submission->grades()->sum('manual_score');
        $totalScore += $manualScore;

        return (float) $totalScore;
    }

    /**
     * إنشاء ملاحظات تلقائية
     */
    public function generateFeedback(AssignmentSubmission $submission): string
    {
        $assignment = $submission->assignment;
        $gradePercentage = ($submission->total_score / $assignment->max_score) * 100;

        $feedback = "الدرجة: {$submission->total_score} من {$assignment->max_score} ({$gradePercentage}%)";

        if ($submission->is_late) {
            $feedback .= "\nملاحظة: تم إرسال الواجب متأخراً.";
        }

        return $feedback;
    }

    /**
     * تصدير نتائج الواجب
     */
    public function exportResults(Assignment $assignment): array
    {
        $submissions = $assignment->submissions()
            ->with(['student', 'files', 'answers', 'grades'])
            ->get();

        $results = [];
        foreach ($submissions as $submission) {
            $results[] = [
                'student_name' => $submission->student->name,
                'student_email' => $submission->student->email,
                'attempt_number' => $submission->attempt_number,
                'submitted_at' => $submission->submitted_at?->format('Y-m-d H:i:s'),
                'is_late' => $submission->is_late ? 'نعم' : 'لا',
                'total_score' => $submission->total_score,
                'grade_percentage' => $submission->grade_percentage,
                'status' => $submission->getStatusLabel(),
                'feedback' => $submission->feedback,
            ];
        }

        return $results;
    }
}

