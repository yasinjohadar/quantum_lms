<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\User;
use App\Models\UserAchievement;
use App\Services\PointService;
use App\Services\GamificationNotificationService;
use App\Events\AchievementUnlocked;
use Illuminate\Support\Facades\Event;

class AchievementService
{
    public function __construct(
        private PointService $pointService
    ) {}

    /**
     * فحص وفتح الإنجازات
     */
    public function checkAndUnlockAchievements(User $user, string $eventType = null): array
    {
        $unlockedAchievements = [];

        // جلب جميع الإنجازات النشطة
        $achievements = Achievement::active()->get();

        foreach ($achievements as $achievement) {
            // التحقق من أن الإنجاز لم يُفتح بعد
            $userAchievement = UserAchievement::where('user_id', $user->id)
                ->where('achievement_id', $achievement->id)
                ->first();

            if ($userAchievement && $userAchievement->is_completed) {
                continue;
            }

            // فحص التقدم
            $progress = $this->calculateProgress($user, $achievement, $eventType);

            if (!$userAchievement) {
                // إنشاء سجل جديد
                $userAchievement = UserAchievement::create([
                    'user_id' => $user->id,
                    'achievement_id' => $achievement->id,
                    'progress' => $progress,
                ]);
            } else {
                // تحديث التقدم
                $userAchievement->progress = $progress;
                $userAchievement->save();
            }

            // فحص إذا تم إكمال الإنجاز
            if ($progress >= 100 && !$userAchievement->is_completed) {
                $this->unlockAchievement($user, $achievement, $userAchievement);
                $unlockedAchievements[] = $achievement;
            }
        }

        return $unlockedAchievements;
    }

    /**
     * حساب التقدم نحو الإنجاز
     */
    private function calculateProgress(User $user, Achievement $achievement, ?string $eventType): int
    {
        $criteria = $achievement->criteria ?? [];
        $type = $achievement->type;
        $progress = 0;

        switch ($type) {
            case 'attendance':
                $target = $criteria['lessons_attended'] ?? 0;
                $current = $user->lessonCompletions()
                    ->where('status', 'attended')
                    ->count();
                $progress = $target > 0 ? min(100, ($current / $target) * 100) : 0;
                break;

            case 'quiz':
                $target = $criteria['quizzes_completed'] ?? 0;
                $current = $user->quizAttempts()
                    ->completed()
                    ->count();
                $progress = $target > 0 ? min(100, ($current / $target) * 100) : 0;
                break;

            case 'course':
                $target = $criteria['courses_completed'] ?? 0;
                $current = $user->subjects()
                    ->wherePivot('status', 'completed')
                    ->count();
                $progress = $target > 0 ? min(100, ($current / $target) * 100) : 0;
                break;

            case 'streak':
                $target = $criteria['days'] ?? 0;
                $current = $this->calculateStreak($user);
                $progress = $target > 0 ? min(100, ($current / $target) * 100) : 0;
                break;
        }

        return (int) $progress;
    }

    /**
     * حساب سلسلة الحضور
     */
    private function calculateStreak(User $user): int
    {
        // حساب أيام الحضور المتتالية
        $completions = $user->lessonCompletions()
            ->orderBy('marked_at', 'desc')
            ->get()
            ->groupBy(function($item) {
                return $item->marked_at->format('Y-m-d');
            });

        $streak = 0;
        $currentDate = now()->startOfDay();

        foreach ($completions as $date => $items) {
            $dateObj = \Carbon\Carbon::parse($date)->startOfDay();
            $diff = $currentDate->diffInDays($dateObj);

            if ($diff === $streak) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * فتح إنجاز
     */
    public function unlockAchievement(User $user, Achievement $achievement, UserAchievement $userAchievement): void
    {
        $userAchievement->completed_at = now();
        $userAchievement->progress = 100;
        $userAchievement->save();

        // منح نقاط المكافأة
        if ($achievement->points_reward > 0) {
            $this->pointService->awardPoints(
                $user,
                'achievement',
                $achievement->points_reward,
                $achievement,
                ['achievement_id' => $achievement->id]
            );
        }

        // منح شارة مرتبطة إن وجدت
        if ($achievement->badge_id) {
            $badgeService = app(BadgeService::class);
            $badgeService->awardBadge($user, $achievement->badge);
        }

        // إرسال Event
        Event::dispatch(new AchievementUnlocked($user, $achievement, [
            'user_achievement_id' => $userAchievement->id,
        ]));

        // إرسال إشعار (سيتم التعامل معه عبر Listener)
        $notificationService = app(GamificationNotificationService::class);
        $notificationService->sendNotification(
            $user,
            'achievement_unlocked',
            'إنجاز جديد!',
            "لقد فتحت إنجاز: {$achievement->name}",
            ['achievement_id' => $achievement->id]
        );
    }

    /**
     * إنجازات المستخدم
     */
    public function getUserAchievements(User $user)
    {
        return $user->achievements()
            ->orderByPivot('completed_at', 'desc')
            ->get();
    }

    /**
     * تقدم نحو إنجاز
     */
    public function getAchievementProgress(User $user, Achievement $achievement): ?UserAchievement
    {
        return UserAchievement::where('user_id', $user->id)
            ->where('achievement_id', $achievement->id)
            ->first();
    }
}

