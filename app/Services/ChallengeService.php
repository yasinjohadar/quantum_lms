<?php

namespace App\Services;

use App\Models\Challenge;
use App\Models\User;
use App\Models\UserChallenge;
use App\Services\PointService;
use App\Services\GamificationNotificationService;

class ChallengeService
{
    public function __construct(
        private PointService $pointService
    ) {}

    /**
     * التحديات النشطة
     */
    public function getActiveChallenges(User $user = null)
    {
        $query = Challenge::active()
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());

        if ($user) {
            $query->with(['userChallenges' => function($q) use ($user) {
                $q->where('user_id', $user->id);
            }]);
        }

        return $query->orderBy('start_date', 'desc')->get();
    }

    /**
     * فحص تقدم التحدي
     */
    public function checkChallengeProgress(User $user, Challenge $challenge): void
    {
        $userChallenge = UserChallenge::firstOrCreate([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
        ], [
            'progress' => 0,
        ]);

        $criteria = $challenge->criteria ?? [];
        $progress = $this->calculateProgress($user, $challenge, $criteria);

        $userChallenge->progress = $progress;
        $userChallenge->save();

        // فحص إذا تم إكمال التحدي
        if ($progress >= 100 && !$userChallenge->is_completed) {
            $this->completeChallenge($user, $challenge, $userChallenge);
        }
    }

    /**
     * حساب التقدم
     */
    private function calculateProgress(User $user, Challenge $challenge, array $criteria): int
    {
        $type = $challenge->type;
        $progress = 0;

        switch ($type) {
            case 'weekly':
            case 'monthly':
            case 'custom':
                // فحص معايير التحدي
                if (isset($criteria['lessons_attended'])) {
                    $target = $criteria['lessons_attended'];
                    $startDate = $challenge->start_date;
                    $endDate = $challenge->end_date;
                    
                    $current = $user->lessonCompletions()
                        ->where('status', 'attended')
                        ->whereBetween('marked_at', [$startDate, $endDate])
                        ->count();
                    
                    $progress = $target > 0 ? min(100, ($current / $target) * 100) : 0;
                }

                if (isset($criteria['quizzes_completed'])) {
                    $target = $criteria['quizzes_completed'];
                    $startDate = $challenge->start_date;
                    $endDate = $challenge->end_date;
                    
                    $current = $user->quizAttempts()
                        ->completed()
                        ->whereBetween('finished_at', [$startDate, $endDate])
                        ->count();
                    
                    $progress = max($progress, $target > 0 ? min(100, ($current / $target) * 100) : 0);
                }
                break;
        }

        return (int) $progress;
    }

    /**
     * إكمال التحدي
     */
    public function completeChallenge(User $user, Challenge $challenge, UserChallenge $userChallenge): void
    {
        $userChallenge->completed_at = now();
        $userChallenge->progress = 100;
        $userChallenge->save();

        // منح المكافآت
        $rewards = $challenge->rewards ?? [];
        
        if (isset($rewards['points']) && $rewards['points'] > 0) {
            $this->pointService->awardPoints(
                $user,
                'challenge',
                $rewards['points'],
                $challenge,
                ['challenge_id' => $challenge->id]
            );
        }

        // إرسال إشعار
        $notificationService = app(GamificationNotificationService::class);
        $notificationService->sendNotification(
            $user,
            'challenge_completed',
            'تحدي مكتمل!',
            "تهانينا! لقد أكملت التحدي: {$challenge->name}",
            ['challenge_id' => $challenge->id]
        );
    }

    /**
     * تحديات المستخدم
     */
    public function getUserChallenges(User $user)
    {
        return Challenge::whereHas('userChallenges', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->with(['userChallenges' => function($q) use ($user) {
            $q->where('user_id', $user->id);
        }])
        ->get();
    }
}

