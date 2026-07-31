<?php

namespace Tests\Backup;

use App\Jobs\CreateBackupJob;
use App\Models\Backup;

class CreateBackupJobTest extends BackupTestCase
{
    public function test_unique_id_is_scoped_to_the_backup(): void
    {
        $config = $this->makeStorageConfig();
        $backup = Backup::factory()->create(['storage_config_id' => $config->id]);

        $job = new CreateBackupJob($backup, []);

        $this->assertSame('create-backup-' . $backup->id, $job->uniqueId());
    }

    public function test_timeout_and_unique_for_resolve_from_config(): void
    {
        config(['backup.job_timeout' => 1234]);
        $config = $this->makeStorageConfig();
        $backup = Backup::factory()->create(['storage_config_id' => $config->id]);

        $job = new CreateBackupJob($backup, []);

        $this->assertSame(1234, $job->timeout);
        $this->assertSame(1234, $job->uniqueFor);
    }

    public function test_failed_callback_marks_backup_as_failed_with_message(): void
    {
        $config = $this->makeStorageConfig();
        $backup = Backup::factory()->pending()->create(['storage_config_id' => $config->id]);

        $job = new CreateBackupJob($backup, []);
        $job->failed(new \Exception('قصد الاختبار: فشل الطابور'));

        $backup->refresh();
        $this->assertSame('failed', $backup->status);
        $this->assertSame('قصد الاختبار: فشل الطابور', $backup->error_message);
        $this->assertNotNull($backup->completed_at);
    }

    public function test_failed_callback_does_not_overwrite_an_already_completed_backup(): void
    {
        $config = $this->makeStorageConfig();
        $backup = Backup::factory()->create(['storage_config_id' => $config->id, 'status' => 'completed']);

        $job = new CreateBackupJob($backup, []);
        $job->failed(new \Exception('لن يُطبَّق'));

        $backup->refresh();
        $this->assertSame('completed', $backup->status);
        $this->assertNull($backup->error_message);
    }
}
