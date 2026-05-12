<?php

namespace App\Console\Commands;

use App\Services\Storage\StorageMigrationService;
use Illuminate\Console\Command;

class StorageMigrateCommand extends Command
{
    protected $signature = 'storage:migrate 
                            {disk? : اسم الـ disk المراد ترحيله (بدون اسم = الكل)}
                            {--batch-size=50 : حجم الدفعة الواحدة}
                            {--sync : تشغيل متزامن (بدون queue)}
                            {--cleanup : حذف الملفات المحلية بعد الترحيل}
                            {--verify : التحقق بعد الترحيل}';

    protected $description = 'ترحيل الملفات من التخزين المحلي إلى السحابة';

    public function handle(StorageMigrationService $migrationService): int
    {
        $disk = $this->argument('disk');
        $batchSize = (int) $this->option('batch-size');
        $sync = $this->option('sync');
        $cleanup = $this->option('cleanup');
        $verify = $this->option('verify');

        if ($disk) {
            $this->info("بدء ترحيل {$disk} إلى السحابة...");
            $batch = $migrationService->startMigration($disk, $batchSize, !$sync);
            $this->info("تم إنشاء الدفعة #{$batch->id} ({$batch->total_files} ملف)");
            
            if ($sync) {
                $this->waitAndReport($migrationService, $batch->id);
            }

            if ($cleanup && $batch->successful_files > 0) {
                $this->info('جاري حذف الملفات المحلية...');
                $result = $migrationService->cleanupLocalAfterMigration($disk);
                $this->info("تم حذف {$result['deleted']} ملف محلي");
            }

            if ($verify) {
                $this->info('جاري التحقق...');
                $verification = $migrationService->verifyMigration($disk);
                $this->table(
                    ['المقياس', 'القيمة'],
                    [
                        ['الملفات المحلية', $verification['total_local']],
                        ['تم ترحيلها', $verification['synced_to_cloud']],
                        ['مفقودة من السحابة', $verification['missing_from_cloud']],
                        ['نسبة المزامنة', $verification['sync_percentage'] . '%'],
                    ]
                );
            }
        } else {
            $this->info('بدء ترحيل جميع الملفات إلى السحابة...');
            $results = $migrationService->migrateAll($batchSize, !$sync);
            
            $rows = [];
            foreach ($results as $diskName => $result) {
                $rows[] = [
                    $diskName,
                    $result['success'] ? 'نعم' : 'لا',
                    $result['success'] ? $result['total_files'] : '-',
                    $result['success'] ? "Batch #{$result['batch_id']}" : $result['error'],
                ];
            }
            
            $this->table(['الـ Disk', 'تم البدء', 'عدد الملفات', 'الدفعة'], $rows);
        }

        return Command::SUCCESS;
    }

    private function waitAndReport(StorageMigrationService $service, int $batchId): void
    {
        $bar = $this->output->createProgressBar(100);
        $bar->start();

        do {
            $status = $service->getBatchStatus($batchId);
            if ($status) {
                $bar->setProgress($status['progress_percentage']);
            }
            sleep(2);
        } while ($status && !$status['is_complete']);

        $bar->finish();
        $this->newLine(2);
    }
}
