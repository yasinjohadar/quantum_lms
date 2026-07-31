<?php

namespace Tests\Backup;

use App\Models\Backup;

class BackupModelTest extends BackupTestCase
{
    public function test_scope_completed_failed_expired(): void
    {
        $config = $this->makeStorageConfig();

        $completed = Backup::factory()->create(['status' => 'completed', 'storage_config_id' => $config->id]);
        $failed = Backup::factory()->create(['status' => 'failed', 'storage_config_id' => $config->id]);
        $expired = Backup::factory()->expired()->create(['status' => 'pending', 'storage_config_id' => $config->id]);
        $ids = [$completed->id, $failed->id, $expired->id];

        $this->assertSame(1, Backup::whereIn('id', $ids)->completed()->count());
        $this->assertSame(1, Backup::whereIn('id', $ids)->failed()->count());
        $this->assertSame(1, Backup::whereIn('id', $ids)->expired()->count());
    }

    public function test_scope_by_type_and_backup_type(): void
    {
        $config = $this->makeStorageConfig();

        $manual = Backup::factory()->create(['type' => 'manual', 'backup_type' => 'database', 'storage_config_id' => $config->id]);
        $scheduled = Backup::factory()->create(['type' => 'scheduled', 'backup_type' => 'full', 'storage_config_id' => $config->id]);
        $ids = [$manual->id, $scheduled->id];

        $this->assertSame(1, Backup::whereIn('id', $ids)->byType('manual')->count());
        $this->assertSame(1, Backup::whereIn('id', $ids)->byType('scheduled')->count());
        $this->assertSame(1, Backup::whereIn('id', $ids)->byBackupType('full')->count());
    }

    public function test_scope_by_storage_driver_uses_storage_config_relation(): void
    {
        $s3Config = $this->makeStorageConfig('s3');
        $localConfig = $this->makeStorageConfig('local');

        $s3Backup = Backup::factory()->create(['storage_config_id' => $s3Config->id]);
        $localBackup = Backup::factory()->create(['storage_config_id' => $localConfig->id]);
        $ids = [$s3Backup->id, $localBackup->id];

        $this->assertSame(1, Backup::whereIn('id', $ids)->byStorageDriver('s3')->count());
        $this->assertSame(1, Backup::whereIn('id', $ids)->byStorageDriver('local')->count());
        $this->assertSame(0, Backup::whereIn('id', $ids)->byStorageDriver('azure')->count());
    }

    public function test_storage_driver_accessor_is_derived_from_storage_config(): void
    {
        $config = $this->makeStorageConfig('wasabi');
        $backup = Backup::factory()->create(['storage_config_id' => $config->id]);

        $this->assertSame('wasabi', $backup->fresh()->storage_driver);
    }

    public function test_get_file_size_formats_bytes_into_readable_units(): void
    {
        $config = $this->makeStorageConfig();

        $backup = Backup::factory()->create(['storage_config_id' => $config->id, 'file_size' => 500]);
        $this->assertSame('500 B', $backup->getFileSize());

        $backup->file_size = 2048;
        $this->assertSame('2 KB', $backup->getFileSize());

        $backup->file_size = 5 * 1024 * 1024;
        $this->assertSame('5 MB', $backup->getFileSize());
    }

    public function test_is_expired(): void
    {
        $config = $this->makeStorageConfig();

        $expired = Backup::factory()->create(['storage_config_id' => $config->id, 'expires_at' => now()->subDay()]);
        $notExpired = Backup::factory()->create(['storage_config_id' => $config->id, 'expires_at' => now()->addDay()]);

        $this->assertTrue($expired->isExpired());
        $this->assertFalse($notExpired->isExpired());
    }

    public function test_calculate_expires_at_adds_retention_days_to_now(): void
    {
        $config = $this->makeStorageConfig();
        $backup = Backup::factory()->create(['storage_config_id' => $config->id, 'retention_days' => 7]);

        $this->assertTrue($backup->calculateExpiresAt()->isSameDay(now()->addDays(7)));
    }
}
