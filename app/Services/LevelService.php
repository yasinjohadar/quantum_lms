<?php

namespace App\Services;

use App\Models\Level;
use App\Models\User;
use App\Models\UserLevel;
use App\Services\PointService;
use App\Services\GamificationNotificationService;
use App\Events\LevelUp;
use Illuminate\Support\Facades\Event;

class LevelService
{
    public function __construct(
        private PointService $pointService
    ) {}

    /**
     * حساب المستوى الحالي
     */
    public function calculateLevel(User $user): ?Level
    {
        $totalPoints = $this->pointService->getUserTotalPoints($user);

        // جلب المستوى المناسب
        $level = Level::where('points_required', '<=', $totalPoints)
            ->orderBy('points_required', 'desc')
            ->first();

        return $level;
    }

    /**
     * فحص ترقية المستوى
     */
    public function checkLevelUp(User $user): ?Level
    {
        $currentLevel = $this->getUserLevel($user);
        $newLevel = $this->calculateLevel($user);

        if (!$newLevel) {
            return null;
        }

        // التحقق من وجود ترقية
        if ($currentLevel && $currentLevel->id === $newLevel->id) {
            return null;
        }

        // تحديث مستوى المستخدم
        $this->updateUserLevel($user, $newLevel);

        $totalPoints = $this->pointService->getUserTotalPoints($user);

        // إرسال Event
        Event::dispatch(new LevelUp($user, $newLevel, [
            'total_points' => $totalPoints,
            'previous_level' => $currentLevel?->id,
        ]));

        // إرسال إشعار (سيتم التعامل معه عبر Listener)
        $notificationService = app(GamificationNotificationService::class);
        $notificationService->sendNotification(
            $user,
            'level_up',
            'ترقية مستوى!',
            "تهانينا! لقد وصلت إلى المستوى: {$newLevel->name}",
            ['level_id' => $newLevel->id, 'level_number' => $newLevel->level_number]
        );

        return $newLevel;
    }

    /**
     * تحديث مستوى المستخدم
     */
    private function updateUserLevel(User $user, Level $level): void
    {
        $totalPoints = $this->pointService->getUserTotalPoints($user);

        $userLevel = $user->userLevel;
        if (!$userLevel) {
            UserLevel::create([
                'user_id' => $user->id,
                'level_id' => $level->id,
                'current_points' => $totalPoints,
                'total_points_earned' => $totalPoints,
                'reached_at' => now(),
            ]);
        } else {
            $userLevel->level_id = $level->id;
            $userLevel->current_points = $totalPoints;
            $userLevel->total_points_earned = $totalPoints;
            if ($userLevel->level_id !== $level->id) {
                $userLevel->reached_at = now();
            }
            $userLevel->save();
        }
    }

    /**
     * مستوى المستخدم الحالي
     */
    public function getUserLevel(User $user): ?Level
    {
        $userLevel = $user->userLevel;
        return $userLevel ? $userLevel->level : null;
    }

    /**
     * تقدم المستوى
     */
    public function getLevelProgress(User $user): array
    {
        $currentLevel = $this->getUserLevel($user);
        $totalPoints = $this->pointService->getUserTotalPoints($user);

        if (!$currentLevel) {
            // جلب أول مستوى
            $firstLevel = Level::orderBy('level_number')->first();
            return [
                'current_level' => null,
                'next_level' => $firstLevel,
                'current_points' => $totalPoints,
                'points_required' => $firstLevel ? $firstLevel->points_required : 0,
                'progress_percentage' => 0,
            ];
        }

        // جلب المستوى التالي
        $nextLevel = Level::where('level_number', '>', $currentLevel->level_number)
            ->orderBy('level_number')
            ->first();

        if (!$nextLevel) {
            return [
                'current_level' => $currentLevel,
                'next_level' => null,
                'current_points' => $totalPoints,
                'points_required' => $currentLevel->points_required,
                'progress_percentage' => 100,
            ];
        }

        $pointsInCurrentLevel = $totalPoints - $currentLevel->points_required;
        $pointsNeededForNext = $nextLevel->points_required - $currentLevel->points_required;
        $progressPercentage = $pointsNeededForNext > 0 
            ? min(100, ($pointsInCurrentLevel / $pointsNeededForNext) * 100) 
            : 100;

        return [
            'current_level' => $currentLevel,
            'next_level' => $nextLevel,
            'current_points' => $totalPoints,
            'points_required' => $nextLevel->points_required,
            'points_in_current_level' => $pointsInCurrentLevel,
            'points_needed_for_next' => $pointsNeededForNext,
            'progress_percentage' => $progressPercentage,
        ];
    }
}

