<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\SystemSetting;
use App\Services\PointService;
use App\Services\GamificationNotificationService;
use App\Events\BadgeEarned;
use Illuminate\Support\Facades\Event;

class BadgeService
{
    public function __construct(
        private PointService $pointService
    ) {}

    /**
     * فحص ومنح الشارات
     */
    public function checkAndAwardBadges(User $user): array
    {
        $awardedBadges = [];

        // جلب جميع الشارات النشطة التلقائية
        $badges = Badge::active()
            ->automatic()
            ->get();

        foreach ($badges as $badge) {
            // التحقق من أن المستخدم لم يحصل على الشارة بعد
            if ($user->badges()->where('badge_id', $badge->id)->exists()) {
                continue;
            }

            // فحص المعايير
            if ($this->checkCriteria($user, $badge)) {
                $this->awardBadge($user, $badge);
                $awardedBadges[] = $badge;
            }
        }

        return $awardedBadges;
    }

    /**
     * فحص معايير الشارة
     */
    private function checkCriteria(User $user, Badge $badge): bool
    {
        $criteria = $badge->criteria ?? [];

        // فحص النقاط المطلوبة
        if (isset($criteria['points_required'])) {
            $totalPoints = $this->pointService->getUserTotalPoints($user);
            if ($totalPoints < $criteria['points_required']) {
                return false;
            }
        }

        // فحص عدد الدروس المكتملة
        if (isset($criteria['lessons_completed'])) {
            $lessonsCompleted = $user->lessonCompletions()
                ->where('status', 'completed')
                ->count();
            if ($lessonsCompleted < $criteria['lessons_completed']) {
                return false;
            }
        }

        // فحص عدد الاختبارات المكتملة
        if (isset($criteria['quizzes_completed'])) {
            $quizzesCompleted = $user->quizAttempts()
                ->completed()
                ->count();
            if ($quizzesCompleted < $criteria['quizzes_completed']) {
                return false;
            }
        }

        // فحص عدد الأسئلة الصحيحة
        if (isset($criteria['questions_correct'])) {
            $questionsCorrect = $user->questionAttempts()
                ->completed()
                ->whereHas('answer', function($q) {
                    $q->where('is_correct', true);
                })
                ->count();
            if ($questionsCorrect < $criteria['questions_correct']) {
                return false;
            }
        }

        return true;
    }

    /**
     * منح شارة للمستخدم
     */
    public function awardBadge(User $user, Badge $badge, array $metadata = []): UserBadge
    {
        // التحقق من عدم وجود الشارة مسبقاً
        $existingBadge = UserBadge::where('user_id', $user->id)
            ->where('badge_id', $badge->id)
            ->first();

        if ($existingBadge) {
            return $existingBadge;
        }

        // إنشاء سجل الشارة
        $userBadge = UserBadge::create([
            'user_id' => $user->id,
            'badge_id' => $badge->id,
            'earned_at' => now(),
            'metadata' => $metadata,
        ]);

        // إرسال Event
        Event::dispatch(new BadgeEarned($user, $badge, $metadata));

        // إرسال إشعار (سيتم التعامل معه عبر Listener)
        $notificationService = app(GamificationNotificationService::class);
        $notificationService->sendNotification(
            $user,
            'badge_earned',
            'شارة جديدة!',
            "لقد حصلت على شارة: {$badge->name}",
            ['badge_id' => $badge->id]
        );

        return $userBadge;
    }

    /**
     * شارات المستخدم
     */
    public function getUserBadges(User $user)
    {
        return $user->badges()
            ->orderByPivot('earned_at', 'desc')
            ->get();
    }

    /**
     * الشارات المتاحة
     */
    public function getAvailableBadges(User $user)
    {
        $userBadgeIds = $user->badges()->pluck('badge_id')->toArray();

        return Badge::active()
            ->whereNotIn('id', $userBadgeIds)
            ->orderBy('order')
            ->get();
    }
}

