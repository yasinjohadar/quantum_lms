<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignmentService
{
    /**
     * إنشاء واجب جديد
     */
    public function createAssignment(array $data, User $creator): Assignment
    {
        return DB::transaction(function () use ($data, $creator) {
            $data['created_by'] = $creator->id;
            
            $assignment = Assignment::create($data);
            
            Log::info('Assignment created', ['assignment_id' => $assignment->id, 'creator_id' => $creator->id]);
            
            return $assignment;
        });
    }

    /**
     * تحديث واجب موجود
     */
    public function updateAssignment(Assignment $assignment, array $data): Assignment
    {
        return DB::transaction(function () use ($assignment, $data) {
            $assignment->update($data);
            
            Log::info('Assignment updated', ['assignment_id' => $assignment->id]);
            
            return $assignment->fresh();
        });
    }

    /**
     * نشر واجب
     */
    public function publishAssignment(Assignment $assignment): Assignment
    {
        $assignment->update([
            'is_published' => true,
            'published_at' => now(),
        ]);
        
        return $assignment->fresh();
    }

    /**
     * إلغاء نشر واجب
     */
    public function unpublishAssignment(Assignment $assignment): Assignment
    {
        $assignment->update([
            'is_published' => false,
            'published_at' => null,
        ]);
        
        return $assignment->fresh();
    }

    /**
     * التحقق من إمكانية إرسال الطالب للواجب
     */
    public function canStudentSubmit(Assignment $assignment, User $student): bool
    {
        // التحقق من النشر
        if (!$assignment->is_published) {
            return false;
        }

        // التحقق من موعد التسليم
        if ($assignment->isOverdue() && !$assignment->allow_late_submission) {
            return false;
        }

        // التحقق من عدد المحاولات
        $submissionsCount = $assignment->submissions()
            ->where('student_id', $student->id)
            ->count();

        if ($submissionsCount >= $assignment->max_attempts) {
            return false;
        }

        // التحقق من التسجيل في المادة/الوحدة/الدرس
        // TODO: إضافة منطق التحقق من التسجيل

        return true;
    }

    /**
     * حساب خصم التأخير
     */
    public function calculateLatePenalty(Assignment $assignment, float $score): float
    {
        if (!$assignment->allow_late_submission || $assignment->late_penalty_percentage <= 0) {
            return 0.0;
        }

        $penalty = ($score * $assignment->late_penalty_percentage) / 100;
        return (float) $penalty;
    }

    /**
     * الحصول على واجبات الطالب
     */
    public function getStudentAssignments(User $student, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Assignment::query()
            ->published()
            ->with(['assignable', 'creator', 'submissions' => function($q) use ($student) {
                $q->where('student_id', $student->id);
            }]);

        // فلترة حسب النوع
        if (isset($filters['type']) && $filters['type'] !== 'all') {
            $query->where('assignable_type', $filters['type']);
        }

        // فلترة حسب الحالة
        if (isset($filters['status'])) {
            if ($filters['status'] === 'upcoming') {
                $query->upcoming();
            } elseif ($filters['status'] === 'overdue') {
                $query->overdue();
            }
        }

        return $query->orderBy('due_date', 'asc')
            ->paginate($filters['per_page'] ?? 20);
    }

    /**
     * الحصول على إحصائيات الواجب
     */
    public function getAssignmentStats(Assignment $assignment): array
    {
        $totalSubmissions = $assignment->submissions()->count();
        $gradedSubmissions = $assignment->submissions()->where('status', AssignmentSubmission::STATUS_GRADED)->count();
        $averageScore = $assignment->submissions()
            ->whereNotNull('total_score')
            ->avg('total_score') ?? 0;

        return [
            'total_submissions' => $totalSubmissions,
            'graded_submissions' => $gradedSubmissions,
            'pending_submissions' => $totalSubmissions - $gradedSubmissions,
            'average_score' => round($averageScore, 2),
        ];
    }
}

