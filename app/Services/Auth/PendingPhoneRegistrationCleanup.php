<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * يستهدف حسابات الطلاب القديمة التي أُنشئت قبل التحقق بالهاتف ثم تُركت دون إكمال OTP.
 * مسار التسجيل الحالي يعتمد على جلسة pending_registration ولا ينشئ صفاً في جدول users حتى نجاح التحقق،
 * لذا لا يُطبَّق هذا التنظيف على التسجيلات الجديدة.
 */
class PendingPhoneRegistrationCleanup
{
    /**
     * مستخدمون سجّلوا بالتحقق بالهاتف ولم يُفعَّل الرقم، ولا يوجد OTP تحقق صالح.
     */
    public function candidatesQuery(): Builder
    {
        return User::query()
            ->whereNull('phone_verified_at')
            ->where('is_active', false)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->whereHas('roles', function ($q) {
                $q->where('name', 'student');
            })
            ->whereDoesntHave('otpCodes', function ($q) {
                $q->where('type', 'verification')
                    ->valid();
            });
    }

    public function shouldPurge(User $user): bool
    {
        if ($user->phone_verified_at !== null) {
            return false;
        }

        if ($user->is_active !== false) {
            return false;
        }

        if (blank($user->phone)) {
            return false;
        }

        if (! $user->hasRole('student')) {
            return false;
        }

        return ! $user->otpCodes()
            ->where('type', 'verification')
            ->valid()
            ->exists();
    }

    /**
     * حذف نهائي من قاعدة البيانات (تجاوز SoftDeletes).
     */
    public function purge(User $user): bool
    {
        if (! $this->shouldPurge($user)) {
            return false;
        }

        $userId = $user->id;

        DB::transaction(function () use ($user) {
            $user->syncRoles([]);
            if (method_exists($user, 'syncPermissions')) {
                $user->syncPermissions([]);
            }
            $user->forceDelete();
        });

        Log::info('Pruned unverified phone registration user', [
            'user_id' => $userId,
        ]);

        return true;
    }

    /**
     * @return int عدد الحسابات المحذوفة
     */
    public function purgeAll(): int
    {
        $deleted = 0;

        $this->candidatesQuery()->orderBy('id')->chunkById(100, function ($users) use (&$deleted) {
            foreach ($users as $user) {
                if ($this->purge($user)) {
                    $deleted++;
                }
            }
        });

        return $deleted;
    }
}
