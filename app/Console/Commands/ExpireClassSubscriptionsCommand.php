<?php

namespace App\Console\Commands;

use App\Services\ClassSubscriptionExpirationService;
use Illuminate\Console\Command;

class ExpireClassSubscriptionsCommand extends Command
{
    protected $signature = 'classes:expire-subscriptions';

    protected $description = 'إلغاء اشتراكات الصفوف المنتهية لجميع الطلاب ومواد الباقة';

    public function handle(ClassSubscriptionExpirationService $expirationService): int
    {
        $this->info('بدء معالجة الصفوف منتهية الاشتراك...');

        $count = $expirationService->processExpiredClasses();

        if ($count > 0) {
            $this->info("تم معالجة {$count} صف منتهي الاشتراك.");
        } else {
            $this->info('لا توجد صفوف منتهية الاشتراك تحتاج معالجة.');
        }

        return Command::SUCCESS;
    }
}
