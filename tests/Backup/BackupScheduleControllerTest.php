<?php

namespace Tests\Backup;

use App\Jobs\CreateBackupJob;
use App\Models\AppStorageConfig;
use App\Models\BackupSchedule;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

class BackupScheduleControllerTest extends BackupTestCase
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

    public function test_store_validates_weekly_requires_days_of_week(): void
    {
        $this->actingAsAdmin();
        $config = $this->makeStorageConfig();

        $response = $this->post(route('admin.backup-schedules.store'), [
            'name' => 'qa_weekly_invalid',
            'backup_type' => 'database',
            'frequency' => 'weekly',
            'time' => '02:00',
            'storage_drivers' => [$config->id],
            'compression_types' => ['zip'],
            'retention_days' => 30,
        ]);

        $response->assertSessionHasErrors(['days_of_week']);
    }

    public function test_store_validates_monthly_requires_day_of_month(): void
    {
        $this->actingAsAdmin();
        $config = $this->makeStorageConfig();

        $response = $this->post(route('admin.backup-schedules.store'), [
            'name' => 'qa_monthly_invalid',
            'backup_type' => 'database',
            'frequency' => 'monthly',
            'time' => '02:00',
            'storage_drivers' => [$config->id],
            'compression_types' => ['zip'],
            'retention_days' => 30,
        ]);

        $response->assertSessionHasErrors(['day_of_month']);
    }

    public function test_store_rejects_storage_driver_id_that_does_not_exist(): void
    {
        $this->actingAsAdmin();

        $response = $this->post(route('admin.backup-schedules.store'), [
            'name' => 'qa_bad_storage',
            'backup_type' => 'database',
            'frequency' => 'daily',
            'time' => '02:00',
            'storage_drivers' => [999999999],
            'compression_types' => ['zip'],
            'retention_days' => 30,
        ]);

        $response->assertSessionHasErrors(['storage_drivers.0']);
    }

    public function test_store_happy_path_creates_schedule(): void
    {
        $this->actingAsAdmin();
        $config = $this->makeStorageConfig();

        $response = $this->post(route('admin.backup-schedules.store'), [
            'name' => 'qa_schedule_happy',
            'backup_type' => 'database',
            'frequency' => 'daily',
            'time' => '02:00',
            'storage_drivers' => [$config->id],
            'compression_types' => ['zip'],
            'retention_days' => 30,
        ]);

        $response->assertRedirect(route('admin.backup-schedules.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('backup_schedules', ['name' => 'qa_schedule_happy']);
    }

    public function test_index_returns_stats(): void
    {
        $this->actingAsAdmin();
        $config = $this->makeStorageConfig();
        BackupSchedule::factory()->create(['is_active' => true, 'storage_drivers' => [$config->id]]);

        $response = $this->get(route('admin.backup-schedules.index'));

        $response->assertOk();
        $response->assertViewHas('stats');
    }

    public function test_destroy_removes_schedule(): void
    {
        $this->actingAsAdmin();
        $config = $this->makeStorageConfig();
        $schedule = BackupSchedule::factory()->create(['storage_drivers' => [$config->id]]);

        $response = $this->delete(route('admin.backup-schedules.destroy', $schedule));

        $response->assertRedirect(route('admin.backup-schedules.index'));
        $this->assertDatabaseMissing('backup_schedules', ['id' => $schedule->id]);
    }

    public function test_execute_queues_backup_and_redirects_to_show(): void
    {
        Queue::fake();
        $this->actingAsAdmin();
        $config = $this->makeStorageConfig();
        $schedule = BackupSchedule::factory()->create(['storage_drivers' => [$config->id]]);

        $response = $this->post(route('admin.backup-schedules.execute', $schedule));

        $response->assertSessionHas('success');
        Queue::assertPushed(CreateBackupJob::class);
    }

    public function test_execute_redirects_with_error_when_no_active_storage(): void
    {
        $this->actingAsAdmin();
        AppStorageConfig::query()->update(['is_active' => false]);
        $schedule = BackupSchedule::factory()->create(['storage_drivers' => []]);

        $response = $this->post(route('admin.backup-schedules.execute', $schedule));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_toggle_active_flips_the_flag(): void
    {
        $this->actingAsAdmin();
        $config = $this->makeStorageConfig();
        $schedule = BackupSchedule::factory()->create(['is_active' => true, 'storage_drivers' => [$config->id]]);

        $response = $this->post(route('admin.backup-schedules.toggle-active', $schedule));

        $response->assertSessionHas('success');
        $this->assertFalse($schedule->fresh()->is_active);
    }
}
