<?php

namespace App\Console\Commands;

use App\Models\StorageSyncBatch;
use App\Services\Storage\StorageMigrationService;
use Illuminate\Console\Command;

class StorageMigrateCommand extends Command
{
    protected $signature = 'storage:migrate 
                            {disk? : اسم الـ disk المنطقي (images, videos, library, ...) — بدون قيمة = ترحيل كل الأقراص التي لها mapping}
                            {--batch-size=50 : حجم الدفعة الواحدة}
                            {--sync : تشغيل متزامن (بدون طابور)}
                            {--cleanup : بعد اكتمال الترحيل، حذف الملفات المحلية التي أصبحت موجودة على السحابة}
                            {--delete-local : بعد كل رفع ناجح للسحابة، حذف النسخة المحلية مباشرة}
                            {--wait : مع الترحيل عبر الطابور، انتظر اكتمال الدفعة قبل أي خطوة تالية (مطلوب مع --cleanup عند عدم --sync)}
                            {--wait-timeout=7200 : أقصى وقت انتظار بالثواني}
                            {--verify : التحقق من الملفات المحلية مقابل السحابة بعد الترحيل}';

    protected $description = 'ترحيل الملفات من التخزين المحلي إلى السحابة (S3 وغيره عبر StorageDiskMapping)';

    public function handle(StorageMigrationService $migrationService): int
    {
        $disk = $this->argument('disk');
        $batchSize = (int) $this->option('batch-size');
        $sync = $this->option('sync');
        $cleanup = $this->option('cleanup');
        $deleteLocal = $this->option('delete-local');
        $wait = $this->option('wait');
        $verify = $this->option('verify');
        $waitTimeout = (int) $this->option('wait-timeout');

        if ($cleanup && ! $sync && ! $wait) {
            $this->error('عند استخدام الطابور (الوضع الافتراضي)، يجب إضافة --wait مع --cleanup حتى لا تُحذف ملفات قبل انتهاء الرفع للسحابة.');
            $this->line('أو نفّذ لاحقاً: php artisan storage:migrate-cleanup {disk}');
            $this->line('أو استخدم --sync مع --cleanup لترحيل متزامن ثم تنظيف.');

            return Command::FAILURE;
        }

        if ($disk) {
            $this->info("بدء ترحيل «{$disk}» إلى السحابة...");
            try {
                $batch = $migrationService->startMigration($disk, $batchSize, ! $sync, $deleteLocal);
            } catch (\Throwable $e) {
                $this->error($e->getMessage());

                return Command::FAILURE;
            }

            $this->info("تم إنشاء الدفعة #{$batch->id} ({$batch->total_files} ملف)");

            if (! $sync && $wait) {
                $completed = $this->waitAndReport($migrationService, $batch->id, $waitTimeout);
                if (! $completed) {
                    $this->warn('انتهت مهلة الانتظار قبل اكتمال الدفعة. يمكنك لاحقاً تشغيل: php artisan storage:migrate-cleanup '.$disk);
                }
            }

            if ($sync) {
                $batch = $batch->fresh();
            } elseif ($wait) {
                $batch = StorageSyncBatch::find($batch->id)?->fresh();
            }

            if ($cleanup && $batch && $batch->successful_files > 0) {
                $this->info('جاري حذف الملفات المحلية التي أُكد وجودها على السحابة...');
                $result = $migrationService->cleanupLocalAfterMigration($disk);
                $this->info("تم حذف {$result['deleted']} ملفاً محلياً");
            } elseif ($cleanup && $batch && $batch->successful_files === 0) {
                $this->warn('لم يُسجَّل أي ترحيل ناجح في الدفعة؛ تخطّي التنظيف.');
            }

            if ($verify) {
                $this->info('جاري التحقق...');
                $verification = $migrationService->verifyMigration($disk);
                $this->table(
                    ['المقياس', 'القيمة'],
                    [
                        ['الملفات المحلية المتبقية', $verification['total_local']],
                        ['موجودة على السحابة', $verification['synced_to_cloud']],
                        ['مفقودة من السحابة', $verification['missing_from_cloud']],
                        ['نسبة التطابق للمحلي', $verification['sync_percentage'].'%'],
                    ]
                );
            }
        } else {
            if ($cleanup && ! $sync) {
                $this->error('ترحيل «الكل» مع --cleanup يتطلب --sync (ترحيل متزامن) أو نفّذ التنظيف لكل قرص: php artisan storage:migrate-cleanup {disk}');

                return Command::FAILURE;
            }

            $this->info('بدء ترحيل جميع الأقراص التي لها تعيين سحابي نشط...');
            $results = $migrationService->migrateAll($batchSize, ! $sync, $deleteLocal);

            $rows = [];
            foreach ($results as $diskName => $result) {
                $rows[] = [
                    $diskName,
                    $result['success'] ? 'نعم' : 'لا',
                    $result['success'] ? $result['total_files'] : '-',
                    $result['success'] ? "Batch #{$result['batch_id']}" : ($result['error'] ?? ''),
                ];
            }

            $this->table(['الـ Disk', 'تم البدء', 'عدد الملفات', 'الدفعة / الخطأ'], $rows);

            if ($sync && $cleanup) {
                foreach ($results as $diskName => $result) {
                    if (empty($result['success'])) {
                        continue;
                    }
                    $this->info("تنظيف محلي لـ {$diskName}...");
                    $c = $migrationService->cleanupLocalAfterMigration($diskName);
                    $this->info("  → حذف {$c['deleted']} ملفاً");
                }
            }

            if ($verify) {
                foreach (array_keys($results) as $diskName) {
                    if (empty($results[$diskName]['success'])) {
                        continue;
                    }
                    $this->info("تحقق: {$diskName}");
                    $v = $migrationService->verifyMigration($diskName);
                    $this->line("  محلي متبقي: {$v['total_local']} | على السحابة: {$v['synced_to_cloud']} | ناقص: {$v['missing_from_cloud']}");
                }
            }
        }

        return Command::SUCCESS;
    }

    private function waitAndReport(StorageMigrationService $service, int $batchId, int $timeoutSeconds): bool
    {
        $bar = $this->output->createProgressBar(100);
        $bar->start();

        $deadline = time() + max(10, $timeoutSeconds);

        do {
            $status = $service->getBatchStatus($batchId);
            if ($status) {
                $bar->setProgress((int) min(100, $status['progress_percentage']));
                if ($status['is_complete']) {
                    $bar->finish();
                    $this->newLine(2);

                    return true;
                }
            }
            sleep(2);
        } while (time() < $deadline);

        $bar->finish();
        $this->newLine(2);

        return false;
    }
}
