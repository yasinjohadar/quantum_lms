<?php

namespace Tests\Backup;

use App\Jobs\CreateBackupJob;
use App\Models\AppStorageConfig;
use App\Models\BackupSchedule;
use App\Models\User;
use App\Services\Backup\BackupScheduleService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

class BackupScheduleServiceTest extends BackupTestCase
{
    public function test_create_schedule_computes_next_run_at(): void
    {
        $user = User::factory()->create();
        $config = $this->makeStorageConfig();
        $service = app(BackupScheduleService::class);

        $schedule = $service->createSchedule([
            'name' => 'qa_schedule_create',
            'backup_type' => 'database',
            'frequency' => 'daily',
            'time' => '02:00',
            'storage_drivers' => [$config->id],
            'compression_types' => ['zip'],
            'retention_days' => 30,
            'created_by' => $user->id,
        ]);

        $this->assertNotNull($schedule->next_run_at);
    }

    public function test_update_schedule_recomputes_next_run_at(): void
    {
        $schedule = BackupSchedule::factory()->create(['frequency' => 'daily', 'time' => '02:00', 'next_run_at' => now()->addDay()]);
        $service = app(BackupScheduleService::class);

        $updated = $service->updateSchedule($schedule, ['time' => '23:59']);

        $this->assertSame('23:59:00', $updated->time);
        $this->assertNotNull($updated->next_run_at);
    }

    public function test_execute_schedule_fans_out_across_storage_and_compression(): void
    {
        Queue::fake();
        $configA = $this->makeStorageConfig('local');
        $configB = $this->makeStorageConfig('s3');

        $schedule = BackupSchedule::factory()->create([
            'storage_drivers' => [$configA->id, $configB->id],
            'compression_types' => ['zip', 'gzip'],
        ]);

        $service = app(BackupScheduleService::class);
        $service->executeSchedule($schedule);

        // 2 أماكن تخزين × 2 أنواع ضغط = 4 مهام
        Queue::assertPushed(CreateBackupJob::class, 4);
    }

    public function test_execute_schedule_falls_back_to_highest_priority_active_config_when_storage_drivers_empty(): void
    {
        Queue::fake();
        $config = $this->makeStorageConfig();
        $config->update(['priority' => 99]);

        $schedule = BackupSchedule::factory()->create(['storage_drivers' => [], 'compression_types' => ['zip']]);

        $service = app(BackupScheduleService::class);
        $backup = $service->executeSchedule($schedule);

        $this->assertNotNull($backup);
        Queue::assertPushed(CreateBackupJob::class, 1);
    }

    public function test_execute_schedule_throws_when_no_active_storage_available(): void
    {
        Queue::fake();
        AppStorageConfig::query()->update(['is_active' => false]);

        $schedule = BackupSchedule::factory()->create(['storage_drivers' => []]);

        $service = app(BackupScheduleService::class);

        $this->expectException(\RuntimeException::class);
        $service->executeSchedule($schedule);
    }

    public function test_execute_schedule_defaults_compression_to_zip_when_empty(): void
    {
        Queue::fake();
        $config = $this->makeStorageConfig();
        $schedule = BackupSchedule::factory()->create(['storage_drivers' => [$config->id], 'compression_types' => []]);

        $service = app(BackupScheduleService::class);
        $service->executeSchedule($schedule);

        Queue::assertPushed(CreateBackupJob::class, function ($job) {
            return $job->options['compression_type'] === 'zip';
        });
    }

    public function test_execute_schedule_advances_next_run_at_even_when_it_throws(): void
    {
        Queue::fake();
        AppStorageConfig::query()->update(['is_active' => false]);

        $schedule = BackupSchedule::factory()->create([
            'storage_drivers' => [],
            'frequency' => 'daily',
            'next_run_at' => now()->subMinute(),
        ]);
        $beforeNextRun = $schedule->next_run_at;

        $service = app(BackupScheduleService::class);

        try {
            $service->executeSchedule($schedule);
        } catch (\RuntimeException) {
            // متوقّع — لا توجد أماكن تخزين نشطة
        }

        $schedule->refresh();
        $this->assertNotNull($schedule->last_run_at);
        $this->assertTrue($schedule->next_run_at->greaterThan($beforeNextRun));
    }

    public function test_execute_schedule_prevents_concurrent_execution_via_cache_lock(): void
    {
        $config = $this->makeStorageConfig();
        $schedule = BackupSchedule::factory()->create(['storage_drivers' => [$config->id]]);

        $lock = Cache::lock('backup-schedule-execute-' . $schedule->id, 3600);
        $this->assertTrue($lock->get());

        $service = app(BackupScheduleService::class);

        try {
            $this->expectException(\RuntimeException::class);
            $service->executeSchedule($schedule);
        } finally {
            $lock->release();
        }
    }
}
