<?php

namespace App\Console\Commands;

use App\Services\Auth\PendingPhoneRegistrationCleanup;
use Illuminate\Console\Command;

class PruneUnverifiedPhoneRegistrationsCommand extends Command
{
    protected $signature = 'users:prune-unverified-phone-registrations';

    protected $description = 'حذف نهائياً حسابات التسجيل التي لم يُفعَّل فيها الهاتف بعد انتهاء صلاحية كل أكواد التحقق';

    public function handle(PendingPhoneRegistrationCleanup $cleanup): int
    {
        $this->info('بدء حذف حسابات التسجيل غير المُفعّلة (انتهاء OTP)...');

        $count = $cleanup->purgeAll();

        if ($count > 0) {
            $this->info("تم حذف {$count} حساباً.");
        } else {
            $this->info('لا توجد حسابات مطابقة للحذف.');
        }

        return Command::SUCCESS;
    }
}
