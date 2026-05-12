<?php

namespace App\Console\Commands;

use App\Services\Storage\StorageMigrationService;
use Illuminate\Console\Command;

class StorageMigrateCleanupCommand extends Command
{
    protected $signature = 'storage:migrate-cleanup 
                            {disk : اسم الـ disk المنطقي (images, videos, library, ...)}
                            {--verify : عرض إحصاءات التحقق بعد الحذف}
                            {--force : تنفيذ الحذف دون سؤال (مفيد في السكربتات وCI)}';

    protected $description = 'حذف الملفات المحلية التي يوجد لها نسخة على السحابة (بعد اكتمال الترحيل)';

    public function handle(StorageMigrationService $migrationService): int
    {
        $disk = $this->argument('disk');

        if (! \App\Models\StorageDiskMapping::where('disk_name', $disk)->where('is_active', true)->exists()) {
            $this->error("لا يوجد تعيين نشط للقرص «{$disk}». لن يُحذف شيء.");

            return Command::FAILURE;
        }

        $this->warn('سيتم حذف أي ملف محلي يظهر أنه موجود على السحابة. تأكد من اكتمال الترحيل أولاً.');
        if (! $this->option('force') && ! $this->confirm('هل تريد المتابعة؟')) {
            return Command::SUCCESS;
        }

        $result = $migrationService->cleanupLocalAfterMigration($disk);
        $this->info("تم حذف {$result['deleted']} ملفاً محلياً.");

        if (! empty($result['errors'])) {
            $this->warn('حدثت أخطاء جزئية: '.count($result['errors']));
        }

        if ($this->option('verify')) {
            $v = $migrationService->verifyMigration($disk);
            $this->table(
                ['المقياس', 'القيمة'],
                [
                    ['محلي متبقي', $v['total_local']],
                    ['موجود على السحابة', $v['synced_to_cloud']],
                    ['مفقود من السحابة', $v['missing_from_cloud']],
                ]
            );
        }

        return Command::SUCCESS;
    }
}
