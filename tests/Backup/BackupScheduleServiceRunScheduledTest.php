<?php

namespace Tests\Backup;

use App\Jobs\CreateBackupJob;
use App\Models\BackupSchedule;
use App\Services\Backup\BackupScheduleService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

class BackupScheduleServiceRunScheduledTest extends BackupTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // اختبارات هذا الملف تعتمد على runScheduledBackups() التي تفحص *كل* الجدولات
        // النشطة المستحقة في قاعدة البيانات المشتركة — نعطّل أي جدولات حقيقية موجودة
        // مسبقاً مؤقتاً (داخل معاملة هذا الاختبار فقط) لضمان عزل تام عن بياناتها.
        BackupSchedule::query()->update(['is_active' => false]);
    }

    public function test_run_scheduled_backups_only_picks_up_due_active_schedules(): void
    {
        Queue::fake();
        $config = $this->makeStorageConfig();

        $due = BackupSchedule::factory()->dueNow()->create(['storage_drivers' => [$config->id]]);
        $notDue = BackupSchedule::factory()->notDue()->create(['storage_drivers' => [$config->id]]);
        $inactive = BackupSchedule::factory()->dueNow()->create(['is_active' => false, 'storage_drivers' => [$config->id]]);

        $service = app(BackupScheduleService::class);
        $count = $service->runScheduledBackups();

        $this->assertSame(1, $count);
        $due->refresh();
        $notDue->refresh();
        $inactive->refresh();
        $this->assertNotNull($due->last_run_at);
        $this->assertNull($notDue->last_run_at);
        $this->assertNull($inactive->last_run_at);
    }

    public function test_run_scheduled_backups_continues_past_a_failing_schedule(): void
    {
        Queue::fake();

        // "الصحية": تخزين مُحدَّد صراحةً — executeSchedule() لا يعيد التحقق من is_active
        // للمعرّفات المُحدَّدة صراحةً (فقط مسار fallback الفارغ يتحقق)، فتبقى تعمل
        // حتى لو أصبح is_active=false لاحقاً على كل الإعدادات.
        $healthyConfig = $this->makeStorageConfig();
        $healthy = BackupSchedule::factory()->dueNow()->create(['storage_drivers' => [$healthyConfig->id]]);

        // "الفاشلة": storage_drivers فارغة عمداً — تضطر لمسار fallback الذي يفشل
        // فعلياً عندما لا يوجد أي AppStorageConfig نشط إطلاقاً.
        $failing = BackupSchedule::factory()->dueNow()->create(['storage_drivers' => []]);

        \App\Models\AppStorageConfig::query()->update(['is_active' => false]);

        $service = app(BackupScheduleService::class);
        $count = $service->runScheduledBackups();

        $this->assertSame(1, $count);
        $this->assertNotNull($healthy->fresh()->last_run_at);
        $this->assertNotNull($failing->fresh()->last_run_at, 'يجب تحديث last_run_at حتى عند الفشل (finally block).');
    }

    public function test_run_scheduled_backups_skipped_when_global_lock_held(): void
    {
        $lock = Cache::lock('backup-run-scheduled-global', 3600);
        $this->assertTrue($lock->get());

        $config = $this->makeStorageConfig();
        BackupSchedule::factory()->dueNow()->create(['storage_drivers' => [$config->id]]);

        $service = app(BackupScheduleService::class);

        try {
            $count = $service->runScheduledBackups();
            $this->assertSame(0, $count);
        } finally {
            $lock->release();
        }
    }

    public function test_count_overdue_schedules_only_counts_active_past_grace_window(): void
    {
        $config = $this->makeStorageConfig();

        $overdue = BackupSchedule::factory()->create([
            'is_active' => true,
            'next_run_at' => now()->subMinutes(30),
            'storage_drivers' => [$config->id],
        ]);
        $withinGrace = BackupSchedule::factory()->create([
            'is_active' => true,
            'next_run_at' => now()->subMinutes(2),
            'storage_drivers' => [$config->id],
        ]);
        $inactiveOverdue = BackupSchedule::factory()->create([
            'is_active' => false,
            'next_run_at' => now()->subMinutes(30),
            'storage_drivers' => [$config->id],
        ]);

        $service = app(BackupScheduleService::class);

        $this->assertSame(1, $service->countOverdueSchedules(10));
    }
}
