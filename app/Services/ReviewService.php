<?php

namespace App\Services;

use App\Models\Review;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\User;
use App\Events\ReviewCreated;
use App\Events\ReviewApproved;
use App\Events\ReviewRejected;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReviewService
{
    /**
     * إنشاء تقييم جديد
     */
    public function createReview(
        User $user,
        $reviewable,
        int $rating,
        ?string $title = null,
        ?string $comment = null,
        bool $isAnonymous = false
    ): Review {
        // التحقق من إمكانية التقييم
        if (!$this->canUserReview($user, $reviewable)) {
            throw new \Exception('لا يمكنك تقييم هذا العنصر');
        }

        // التحقق من وجود تقييم سابق (غير محذوف)
        $existingReview = Review::where('user_id', $user->id)
            ->where('reviewable_type', get_class($reviewable))
            ->where('reviewable_id', $reviewable->id)
            ->whereNull('deleted_at')
            ->first();

        if ($existingReview) {
            throw new \Exception('لديك تقييم سابق لهذا العنصر. يمكنك تعديله بدلاً من إنشاء تقييم جديد');
        }

        // تحديد الحالة الافتراضية
        $status = Review::STATUS_PENDING;
        if ($reviewable instanceof Subject && !$reviewable->reviews_require_approval) {
            $status = Review::STATUS_APPROVED;
        } elseif ($reviewable instanceof SchoolClass && !$reviewable->reviews_require_approval) {
            $status = Review::STATUS_APPROVED;
        }

        return DB::transaction(function () use ($user, $reviewable, $rating, $title, $comment, $isAnonymous, $status) {
            $review = Review::create([
                'reviewable_type' => get_class($reviewable),
                'reviewable_id' => $reviewable->id,
                'user_id' => $user->id,
                'rating' => $rating,
                'title' => $title,
                'comment' => $comment,
                'status' => $status,
                'is_anonymous' => $isAnonymous,
            ]);

            // إذا كان التقييم معتمد تلقائياً، تحديث الحالة
            if ($status === Review::STATUS_APPROVED) {
                $review->approve();
            }

            // إرسال Event
            event(new ReviewCreated($user, $review));

            return $review;
        });
    }

    /**
     * تحديث تقييم
     */
    public function updateReview(
        Review $review,
        int $rating,
        ?string $title = null,
        ?string $comment = null,
        bool $isAnonymous = false
    ): Review {
        // إذا كان التقييم معتمد، إرجاعه إلى قيد المراجعة
        if ($review->isApproved()) {
            $review->status = Review::STATUS_PENDING;
            $review->approved_by = null;
            $review->approved_at = null;
        }

        $review->update([
            'rating' => $rating,
            'title' => $title,
            'comment' => $comment,
            'is_anonymous' => $isAnonymous,
        ]);

        return $review->fresh();
    }

    /**
     * الموافقة على تقييم
     */
    public function approveReview(Review $review, ?User $approver = null): Review
    {
        $review->approve($approver);
        $review = $review->fresh();
        
        // إرسال Event
        event(new ReviewApproved($review->user, $review));
        
        return $review;
    }

    /**
     * رفض تقييم
     */
    public function rejectReview(Review $review, string $reason, ?User $rejector = null): Review
    {
        $review->reject($reason, $rejector);
        $review = $review->fresh();
        
        // إرسال Event
        event(new ReviewRejected($review->user, $review, $reason));
        
        return $review;
    }

    /**
     * حساب متوسط التقييم
     */
    public function getAverageRating($reviewable): float
    {
        $reviews = Review::where('reviewable_type', get_class($reviewable))
            ->where('reviewable_id', $reviewable->id)
            ->approved()
            ->get();

        if ($reviews->isEmpty()) {
            return 0;
        }

        return round($reviews->avg('rating'), 2);
    }

    /**
     * إحصائيات التقييمات
     */
    public function getReviewStats($reviewable): array
    {
        $reviews = Review::where('reviewable_type', get_class($reviewable))
            ->where('reviewable_id', $reviewable->id)
            ->approved()
            ->get();

        $total = $reviews->count();
        $average = $total > 0 ? round($reviews->avg('rating'), 2) : 0;

        // توزيع التقييمات
        $distribution = [
            5 => 0,
            4 => 0,
            3 => 0,
            2 => 0,
            1 => 0,
        ];

        foreach ($reviews as $review) {
            $distribution[$review->rating]++;
        }

        // النسب المئوية
        $percentages = [];
        foreach ($distribution as $rating => $count) {
            $percentages[$rating] = $total > 0 ? round(($count / $total) * 100, 1) : 0;
        }

        return [
            'total' => $total,
            'average' => $average,
            'distribution' => $distribution,
            'percentages' => $percentages,
        ];
    }

    /**
     * التحقق من إمكانية التقييم
     */
    public function canUserReview(User $user, $reviewable): bool
    {
        // التحقق من تفعيل التقييمات
        if ($reviewable instanceof Subject && !$reviewable->reviews_enabled) {
            return false;
        }

        if ($reviewable instanceof SchoolClass && !$reviewable->reviews_enabled) {
            return false;
        }

        // التحقق من التسجيل في المادة (إذا كان تقييم مادة)
        if ($reviewable instanceof Subject) {
            $isEnrolled = $user->subjects()
                ->where('subjects.id', $reviewable->id)
                ->where('enrollments.status', 'active')
                ->exists();

            if (!$isEnrolled) {
                return false;
            }
        }

        // التحقق من التسجيل في صف يحتوي على المادة (إذا كان تقييم صف)
        if ($reviewable instanceof SchoolClass) {
            $hasEnrolledSubject = $user->subjects()
                ->where('subjects.class_id', $reviewable->id)
                ->where('enrollments.status', 'active')
                ->exists();

            if (!$hasEnrolledSubject) {
                return false;
            }
        }

        return true;
    }

    /**
     * إحصائيات عامة للتقييمات
     */
    public function getGeneralStats(): array
    {
        $totalReviews = Review::count();
        $pendingReviews = Review::pending()->count();
        $approvedReviews = Review::approved()->count();
        $rejectedReviews = Review::rejected()->count();

        $subjectReviews = Review::where('reviewable_type', Subject::class)->count();
        $classReviews = Review::where('reviewable_type', SchoolClass::class)->count();

        $averageRating = Review::approved()->avg('rating') ?? 0;

        return [
            'total' => $totalReviews,
            'pending' => $pendingReviews,
            'approved' => $approvedReviews,
            'rejected' => $rejectedReviews,
            'subject_reviews' => $subjectReviews,
            'class_reviews' => $classReviews,
            'average_rating' => round($averageRating, 2),
        ];
    }
}

