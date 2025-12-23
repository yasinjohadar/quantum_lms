<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Storage\AppStorageManager;
use App\Models\StorageDiskMapping;

class TestAppStorageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:storage:test {disk? : اسم الـ disk لاختباره}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'اختبار إعدادات تخزين التطبيق والتخزين الاحتياطي (Failover) لكل disk أو لاسم محدد';

    /**
     * Execute the console command.
     */
    public function handle(AppStorageManager $storageManager): int
    {
        $disk = $this->argument('disk');

        if ($disk) {
            return $this->testSingleDisk($storageManager, $disk);
        }

        $this->info('بدء اختبار جميع الـ disks النشطة ...');

        $mappings = StorageDiskMapping::where('is_active', true)->get();
        if ($mappings->isEmpty()) {
            $this->warn('لا توجد Disk Mappings مفعّلة.');
            return self::SUCCESS;
        }

        foreach ($mappings as $mapping) {
            $this->line('');
            $this->line('--------------------------------------');
            $this->info("اختبار الـ disk: {$mapping->disk_name}");
            $this->testSingleDisk($storageManager, $mapping->disk_name);
        }

        $this->line('');
        $this->info('اكتمل اختبار جميع الـ disks.');

        return self::SUCCESS;
    }

    protected function testSingleDisk(AppStorageManager $storageManager, string $disk): int
    {
        $this->line("اختبار disk: {$disk}");

        $testPath = 'storage-test/' . uniqid('test_', true) . '.txt';
        $content = 'Quantum LMS storage test at ' . now();

        try {
            $this->line(' - محاولة التخزين مع Auto-failover ...');
            $stored = $storageManager->storeWithFailover($disk, $testPath, $content, 'test');

            if (!$stored) {
                $this->error('   فشل التخزين.');
                return self::FAILURE;
            }

            $this->info('   تم التخزين بنجاح.');

            $this->line(' - محاولة القراءة ...');
            $read = $storageManager->retrieve($disk, $testPath);
            if ($read !== $content) {
                $this->warn('   تم الاسترجاع لكن المحتوى مختلف.');
            } else {
                $this->info('   تم الاسترجاع بنجاح.');
            }

            $this->line(' - محاولة الحذف ...');
            $deleted = $storageManager->delete($disk, $testPath);
            if ($deleted) {
                $this->info('   تم الحذف بنجاح.');
            } else {
                $this->warn('   لم يتمكن من حذف الملف (تحقق يدوياً).');
            }

            $this->info("تم اختبار disk {$disk} بدون أخطاء. راجع سجل AuditLog لمعرفة ما إذا تم استخدام Failover.");
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("خطأ أثناء اختبار disk {$disk}: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
