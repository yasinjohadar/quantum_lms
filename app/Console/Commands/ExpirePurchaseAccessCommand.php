<?php

namespace App\Console\Commands;

use App\Services\PurchaseService;
use Illuminate\Console\Command;

class ExpirePurchaseAccessCommand extends Command
{
    protected $signature = 'purchases:expire-access';

    protected $description = 'إلغاء اشتراكات الصفوف والمواد تلقائياً عند انتهاء صلاحية الشراء';

    public function handle(PurchaseService $purchaseService): int
    {
        $this->info('بدء معالجة الاشتراكات المنتهية...');

        $count = $purchaseService->processExpiredPurchases();

        if ($count > 0) {
            $this->info("تم إلغاء {$count} اشتراك منتهي الصلاحية.");
        } else {
            $this->info('لا توجد اشتراكات منتهية تحتاج معالجة.');
        }

        return Command::SUCCESS;
    }
}
