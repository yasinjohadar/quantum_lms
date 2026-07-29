<?php

namespace App\Services;

use App\Models\PointTransaction;
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
        // كل الفحوص والخصم داخل معاملة واحدة مع أقفال صفوف لمنع:
        //  - الصرف المزدوج عند طلبين متزامنين (كان الفحص خارج المعاملة + رصيد مخزَّن
        //    مؤقتاً 120ث فيمرّ الطلبان معاً ويخصمان معاً → رصيد سالب).
        //  - تجاوز المخزون.
        // ترتيب القفل ثابت (المستخدم ثم المكافأة) لتفادي التعارض (deadlock).
        return DB::transaction(function () use ($user, $reward) {
            // قفل المستخدم لتسلسل كل عمليات استبداله (يمنع الصرف المزدوج عبر مكافآت مختلفة)
            User::whereKey($user->id)->lockForUpdate()->first();

            // قفل صف المكافأة وإعادة قراءته حديثاً داخل المعاملة
            $reward = Reward::whereKey($reward->id)->lockForUpdate()->firstOrFail();

            if (! $reward->is_available) {
                throw new \Exception('المكافأة غير متاحة');
            }

            // فحص المخزون داخل القفل
            if ($reward->quantity_available !== null
                && $reward->quantity_claimed >= $reward->quantity_available) {
                throw new \Exception('نفد مخزون المكافأة');
            }

            // قراءة الرصيد حديثاً من قاعدة البيانات (تجاوز الكاش) داخل القفل
            $totalPoints = (int) PointTransaction::where('user_id', $user->id)->sum('points');
            if ($totalPoints < $reward->points_cost) {
                throw new \Exception('نقاط غير كافية');
            }

            // خصم النقاط — بدون تمرير المكافأة كـ source حتى لا تُفعِّل حماية التكرار
            // (idempotency) فتمنع استبدال نفس المكافأة أكثر من مرة إن كان مسموحاً.
            $this->pointService->awardPoints(
                $user,
                'reward',
                -$reward->points_cost, // سالب للخصم
                null,
                ['reward_id' => $reward->id, 'action' => 'claim']
            );

            // إنشاء سجل الاستبدال
            $userReward = UserReward::create([
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'claimed_at' => now(),
                'status' => 'pending',
            ]);

            // زيادة الكمية المستبدلة ذرّياً
            if ($reward->quantity_available !== null) {
                $reward->increment('quantity_claimed');
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

