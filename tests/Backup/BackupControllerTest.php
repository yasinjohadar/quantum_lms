<?php

namespace Tests\Backup;

use App\Jobs\CreateBackupJob;
use App\Models\Backup;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class BackupControllerTest extends BackupTestCase
{
    private function actingAsAdmin(): User
    {
        $role = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['dashboard_type' => 'admin', 'staff_profile' => 'none']
        );
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);
        $this->actingAs($user);

        return $user;
    }

    public function test_index_filters_by_status_backup_type_and_storage_driver(): void
    {
        $admin = $this->actingAsAdmin();
        $s3Config = $this->makeStorageConfig('s3');
        $localConfig = $this->makeStorageConfig('local');

        $target = Backup::factory()->create([
            'status' => 'completed',
            'backup_type' => 'database',
            'storage_config_id' => $s3Config->id,
            'name' => 'qa_index_target_' . uniqid(),
        ]);
        Backup::factory()->create([
            'status' => 'failed',
            'backup_type' => 'full',
            'storage_config_id' => $localConfig->id,
            'name' => 'qa_index_other_' . uniqid(),
        ]);

        $response = $this->get(route('admin.backups.index', [
            'status' => 'completed',
            'backup_type' => 'database',
            'storage_driver' => 's3',
        ]));

        $response->assertOk();
        $response->assertViewHas('backups', function ($backups) use ($target) {
            return $backups->pluck('id')->contains($target->id);
        });
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAsAdmin();

        $response = $this->post(route('admin.backups.store'), []);

        $response->assertSessionHasErrors(['name', 'backup_type', 'storage_config_id', 'compression_type', 'retention_days']);
    }

    public function test_store_rejects_inactive_storage_config(): void
    {
        $this->actingAsAdmin();
        $config = $this->makeStorageConfig();
        $config->update(['is_active' => false]);

        $response = $this->post(route('admin.backups.store'), [
            'name' => 'qa_store_inactive',
            'backup_type' => 'database',
            'storage_config_id' => $config->id,
            'compression_type' => 'zip',
            'retention_days' => 30,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('backups', ['name' => 'qa_store_inactive']);
    }

    public function test_store_happy_path_queues_backup(): void
    {
        Queue::fake();
        $this->actingAsAdmin();
        $config = $this->makeStorageConfig();

        $response = $this->post(route('admin.backups.store'), [
            'name' => 'qa_store_success',
            'backup_type' => 'database',
            'storage_config_id' => $config->id,
            'compression_type' => 'zip',
            'retention_days' => 30,
        ]);

        $response->assertSessionHas('success');
        Queue::assertPushed(CreateBackupJob::class);
        $this->assertDatabaseHas('backups', ['name' => 'qa_store_success', 'storage_config_id' => $config->id]);
    }

    public function test_download_redirects_with_error_when_not_completed(): void
    {
        $this->actingAsAdmin();
        $config = $this->makeStorageConfig();
        $backup = Backup::factory()->pending()->create(['storage_config_id' => $config->id]);

        $response = $this->get(route('admin.backups.download', $backup));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_restore_requires_exact_restore_confirmation_phrase(): void
    {
        $this->actingAsAdmin();
        $config = $this->makeStorageConfig();
        $backup = Backup::factory()->create(['storage_config_id' => $config->id, 'status' => 'completed']);

        $response = $this->post(route('admin.backups.restore', $backup), [
            'confirm' => true,
            'confirm_phrase' => 'restore', // أحرف صغيرة — يجب أن يُرفض
        ]);

        $response->assertSessionHasErrors(['confirm_phrase']);
    }

    public function test_restore_rejects_non_completed_backup_before_calling_service(): void
    {
        $this->actingAsAdmin();
        $config = $this->makeStorageConfig();
        $backup = Backup::factory()->pending()->create(['storage_config_id' => $config->id]);

        $response = $this->post(route('admin.backups.restore', $backup), [
            'confirm' => true,
            'confirm_phrase' => 'RESTORE',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_destroy_deletes_backup_and_redirects_with_success(): void
    {
        Storage::fake('local');
        $this->actingAsAdmin();
        $config = $this->makeStorageConfig();
        $backup = Backup::factory()->create(['storage_config_id' => $config->id]);

        $response = $this->delete(route('admin.backups.destroy', $backup));

        $response->assertRedirect(route('admin.backups.index'));
        $response->assertSessionHas('success');
        $this->assertSoftDeleted('backups', ['id' => $backup->id]);
    }

    public function test_stats_endpoint_returns_json_summary(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.backups.stats'));

        $response->assertOk();
        $response->assertJsonStructure(['total', 'completed', 'failed', 'pending', 'running', 'total_size', 'expired', 'stuck']);
    }
}
