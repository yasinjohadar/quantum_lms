<?php

namespace App\Console\Commands;

use App\Services\Backup\BackupNotificationService;
use App\Services\Backup\BackupService;
use Illuminate\Console\Command;

class CleanupExpiredBackupsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:cleanup-expired {--dry-run : معاينة ما سيُحذف بدون حذف فعلي}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'حذف النسخ الاحتياطية المنتهية الصلاحية';

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backupService, BackupNotificationService $notificationService): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info($dryRun
            ? 'معاينة (dry-run) — لن يُحذف شيء فعلياً...'
            : 'بدء تنظيف النسخ الاحتياطية المنتهية الصلاحية...');

        $summary = $backupService->cleanupExpiredBackups($dryRun);

        if ($summary['deleted'] === 0 && $summary['failed'] === 0) {
            $this->info('لا توجد نسخ احتياطية منتهية الصلاحية.');

            return Command::SUCCESS;
        }

        $sizeLabel = $this->formatBytes($summary['total_bytes_freed']);

        if ($dryRun) {
            $this->info("سيُحذف {$summary['deleted']} نسخة ({$sizeLabel}) لو تم التشغيل الفعلي.");
        } else {
            $this->info("تم حذف {$summary['deleted']} نسخة احتياطية منتهية الصلاحية ({$sizeLabel}).");
        }

        if ($summary['failed'] > 0) {
            $this->error("فشل حذف {$summary['failed']} نسخة:");
            foreach ($summary['failed_ids'] as $failure) {
                $this->line("  - #{$failure['id']}: {$failure['reason']}");
            }
        }

        if (! $dryRun) {
            $notificationService->notifyCleanupSummary($summary);
        }

        return $summary['failed'] > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        $value = (float) $bytes;

        while ($value > 1024 && $i < count($units) - 1) {
            $value /= 1024;
            $i++;
        }

        return round($value, 2) . ' ' . $units[$i];
    }
}
