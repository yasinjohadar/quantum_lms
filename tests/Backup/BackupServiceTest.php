<?php

namespace Tests\Backup;

use App\Models\Backup;
use App\Services\Backup\BackupService;
use App\Services\Backup\StorageManager;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class BackupServiceTest extends BackupTestCase
{
    public function test_create_pending_backup_requires_storage_config_id(): void
    {
        $service = app(BackupService::class);

        $this->expectException(\InvalidArgumentException::class);
        $service->createPendingBackup(['name' => 'no-storage']);
    }

    public function test_create_pending_backup_sets_defaults_and_expiry(): void
    {
        $config = $this->makeStorageConfig();
        $service = app(BackupService::class);

        $backup = $service->createPendingBackup([
            'name' => 'qa_backup_defaults',
            'storage_config_id' => $config->id,
            'retention_days' => 10,
        ]);

        $this->assertSame('manual', $backup->type);
        $this->assertSame('full', $backup->backup_type);
        $this->assertSame('pending', $backup->status);
        $this->assertSame('zip', $backup->compression_type);
        $this->assertNotNull($backup->expires_at);
        $this->assertTrue($backup->expires_at->isSameDay(now()->addDays(10)));
    }

    public function test_queue_backup_dispatches_create_backup_job(): void
    {
        Queue::fake();
        $config = $this->makeStorageConfig();
        $service = app(BackupService::class);

        $backup = $service->queueBackup([
            'name' => 'qa_backup_queue',
            'storage_config_id' => $config->id,
        ]);

        Queue::assertPushed(\App\Jobs\CreateBackupJob::class, function ($job) use ($backup) {
            return $job->backup->id === $backup->id;
        });
        $this->assertSame(1, $backup->logs()->count());
    }

    public function test_get_backup_stats_counts_by_status(): void
    {
        $config = $this->makeStorageConfig();
        $completed = Backup::factory()->create(['status' => 'completed', 'storage_config_id' => $config->id]);
        $failed = Backup::factory()->create(['status' => 'failed', 'storage_config_id' => $config->id]);

        $service = app(BackupService::class);
        $stats = $service->getBackupStats();

        $this->assertGreaterThanOrEqual(1, $stats['completed']);
        $this->assertGreaterThanOrEqual(1, $stats['failed']);
        $this->assertArrayHasKey('stuck', $stats);
        $this->assertArrayHasKey('total_size', $stats);
    }

    public function test_count_stuck_backups_detects_old_running_and_pending(): void
    {
        $config = $this->makeStorageConfig();
        $stuckRunning = Backup::factory()->running()->create([
            'storage_config_id' => $config->id,
            'started_at' => now()->subMinutes(200),
        ]);
        $healthyRunning = Backup::factory()->running()->create([
            'storage_config_id' => $config->id,
            'started_at' => now()->subMinutes(5),
        ]);

        $service = app(BackupService::class);
        $before = $service->countStuckBackups();

        // لا نتحقق من رقم مطلق (بيانات مشتركة) بل من تغيّر النتيجة بوجود سجل عالق حقيقي
        $healthyRunning->delete();
        $this->assertGreaterThanOrEqual(1, $before);

        $stuckRunning->forceFill(['status' => 'completed'])->save();
        $after = $service->countStuckBackups();
        $this->assertLessThan($before, $after);
    }

    public function test_cleanup_expired_backups_dry_run_does_not_delete(): void
    {
        $config = $this->makeStorageConfig();
        Storage::fake('local');

        $backup = Backup::factory()->expired()->create([
            'storage_config_id' => $config->id,
            'file_size' => 1000,
        ]);

        $service = app(BackupService::class);
        $summary = $service->cleanupExpiredBackups(dryRun: true);

        $this->assertGreaterThanOrEqual(1, $summary['deleted']);
        $this->assertDatabaseHas('backups', ['id' => $backup->id, 'deleted_at' => null]);
    }

    public function test_cleanup_expired_backups_real_run_deletes_expired_only(): void
    {
        $config = $this->makeStorageConfig();
        Storage::fake('local');

        $expired = Backup::factory()->expired()->create(['storage_config_id' => $config->id]);
        $notExpired = Backup::factory()->create(['storage_config_id' => $config->id, 'expires_at' => now()->addDay()]);

        $service = app(BackupService::class);
        $summary = $service->cleanupExpiredBackups();

        $this->assertGreaterThanOrEqual(1, $summary['deleted']);
        $this->assertSoftDeleted('backups', ['id' => $expired->id]);
        $this->assertDatabaseHas('backups', ['id' => $notExpired->id, 'deleted_at' => null]);
    }

    public function test_download_backup_requires_completed_status(): void
    {
        $config = $this->makeStorageConfig();
        $backup = Backup::factory()->pending()->create(['storage_config_id' => $config->id]);

        $service = app(BackupService::class);

        $this->expectException(\RuntimeException::class);
        $service->downloadBackup($backup);
    }

    public function test_download_and_delete_round_trip_via_local_storage(): void
    {
        Storage::fake('local');
        $config = $this->makeStorageConfig();
        $backup = Backup::factory()->pending()->create(['storage_config_id' => $config->id]);

        $tempFile = tempnam(sys_get_temp_dir(), 'qa_backup_');
        file_put_contents($tempFile, 'قصد الاختبار');

        $manager = app(StorageManager::class);
        $manager->storeWithFailover($backup, $tempFile);
        @unlink($tempFile);

        $backup->refresh();
        $backup->update(['status' => 'completed']);

        $service = app(BackupService::class);
        $response = $service->downloadBackup($backup);
        $this->assertNotNull($response);

        $deleted = $service->deleteBackup($backup->fresh());
        $this->assertTrue($deleted);
        $this->assertSoftDeleted('backups', ['id' => $backup->id]);
    }
}
