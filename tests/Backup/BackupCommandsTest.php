<?php

namespace Tests\Backup;

use App\Models\AppStorageConfig;
use App\Models\Backup;
use App\Models\BackupSchedule;
use Illuminate\Support\Facades\Queue;

class BackupCommandsTest extends BackupTestCase
{
    public function test_run_scheduled_backups_command_reports_zero_when_nothing_due(): void
    {
        BackupSchedule::query()->update(['is_active' => false]);

        $this->artisan('backup:run-scheduled')
            ->expectsOutputToContain('لا توجد نسخ')
            ->assertExitCode(0);
    }

    public function test_run_scheduled_backups_command_reports_count_when_schedules_run(): void
    {
        Queue::fake();
        BackupSchedule::query()->update(['is_active' => false]);
        $config = $this->makeStorageConfig();
        BackupSchedule::factory()->dueNow()->create(['storage_drivers' => [$config->id]]);

        $this->artisan('backup:run-scheduled')
            ->expectsOutputToContain('تم تشغيل')
            ->assertExitCode(0);
    }

    public function test_cleanup_expired_backups_command_dry_run_option(): void
    {
        $config = $this->makeStorageConfig();
        $backup = Backup::factory()->expired()->create(['storage_config_id' => $config->id]);

        $this->artisan('backup:cleanup-expired --dry-run')
            ->assertExitCode(0);

        $this->assertDatabaseHas('backups', ['id' => $backup->id, 'deleted_at' => null]);
    }

    public function test_cleanup_expired_backups_command_real_run(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        $config = $this->makeStorageConfig();
        $backup = Backup::factory()->expired()->create(['storage_config_id' => $config->id]);

        $this->artisan('backup:cleanup-expired')
            ->assertExitCode(0);

        $this->assertSoftDeleted('backups', ['id' => $backup->id]);
    }

    public function test_backup_test_storage_command_targets_app_storage_config(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        $config = $this->makeStorageConfig('local');

        $this->artisan("backup:test-storage {$config->id}")
            ->expectsOutputToContain($config->name)
            ->assertExitCode(0);
    }

    public function test_backup_test_storage_command_reports_missing_config(): void
    {
        $this->artisan('backup:test-storage 999999999')
            ->assertExitCode(1);
    }

    public function test_reconcile_stuck_backups_command_marks_stuck_backups_as_failed(): void
    {
        $config = $this->makeStorageConfig();
        $stuck = Backup::factory()->running()->create([
            'storage_config_id' => $config->id,
            'started_at' => now()->subMinutes(200),
        ]);

        $this->artisan('backup:reconcile-stuck')
            ->assertExitCode(0);

        $this->assertSame('failed', $stuck->fresh()->status);
    }

    public function test_reconcile_stuck_backups_command_reports_clean_state(): void
    {
        Backup::query()->where('status', 'running')->update(['status' => 'completed']);
        Backup::query()->where('status', 'pending')->update(['status' => 'completed']);

        $this->artisan('backup:reconcile-stuck')
            ->expectsOutputToContain('لا توجد نسخ عالقة')
            ->assertExitCode(0);
    }
}
