<?php

namespace App\Services;

use App\Models\Reward;
use App\Models\User;
use App\Models\UserReward;
use App\Services\PointService;
use Illuminate\Support\Facades\DB;

class RewardService
{
    public function __construct(
        private PointService $pointService
    ) {}

    /**
     * المكافآت المتاحة
     */
    public function getAvailableRewards()
    {
        return Reward::active()
            ->available()
            ->orderBy('points_cost')
            ->get();
    }

    /**
     * استبدال مكافأة
     */
    public function claimReward(User $user, Reward $reward): UserReward
    {
        // التحقق من توفر المكافأة
        if (!$reward->is_available) {
            throw new \Exception('المكافأة غير متاحة');
        }

        // التحقق من نقاط المستخدم
        $totalPoints = $this->pointService->getUserTotalPoints($user);
        if ($totalPoints < $reward->points_cost) {
            throw new \Exception('نقاط غير كافية');
        }

        return DB::transaction(function() use ($user, $reward) {
            // خصم النقاط
            $this->pointService->awardPoints(
                $user,
                'reward',
                -$reward->points_cost, // سالب للخصم
                $reward,
                ['reward_id' => $reward->id, 'action' => 'claim']
            );

            // إنشاء سجل الاستبدال
            $userReward = UserReward::create([
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'claimed_at' => now(),
                'status' => 'pending',
            ]);

            // تحديث الكمية المستبدلة
            if ($reward->quantity_available !== null) {
                $reward->quantity_claimed++;
                $reward->save();
            }

            return $userReward;
        });
    }

    /**
     * مكافآت المستخدم
     */
    public function getUserRewards(User $user)
    {
        return $user->rewards()
            ->orderByPivot('claimed_at', 'desc')
            ->get();
    }

    /**
     * تحديث حالة المكافأة
     */
    public function updateRewardStatus(UserReward $userReward, string $status): void
    {
        $userReward->status = $status;
        $userReward->save();
    }
}

