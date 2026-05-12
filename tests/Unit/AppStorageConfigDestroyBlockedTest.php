<?php

namespace Tests\Unit;

use App\Models\AppStorageConfig;
use App\Models\Role;
use App\Models\StorageDiskMapping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\TestCase;

#[RequiresPhpExtension('pdo_sqlite')]
class AppStorageConfigDestroyBlockedTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_delete_storage_config_when_used_as_primary_in_disk_mapping(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['dashboard_type' => 'admin', 'staff_profile' => 'none']
        );

        $user = User::factory()->create();
        $user->assignRole($role);

        $config = AppStorageConfig::create([
            'name' => 'keep-me',
            'driver' => 'local',
            'config' => ['path' => 'public'],
            'is_active' => true,
            'priority' => 0,
            'redundancy' => false,
            'created_by' => $user->id,
        ]);

        StorageDiskMapping::create([
            'disk_name' => 'images_test_block',
            'label' => 'Test',
            'primary_storage_id' => $config->id,
            'fallback_storage_ids' => null,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('admin.app-storage.configs.destroy', $config));

        $response->assertRedirect(route('admin.app-storage.configs.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('app_storage_configs', ['id' => $config->id]);
    }

    public function test_cannot_delete_storage_config_when_listed_in_fallback_storage_ids(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['dashboard_type' => 'admin', 'staff_profile' => 'none']
        );

        $user = User::factory()->create();
        $user->assignRole($role);

        $primary = AppStorageConfig::create([
            'name' => 'primary-local',
            'driver' => 'local',
            'config' => ['path' => 'public'],
            'is_active' => true,
            'priority' => 0,
            'redundancy' => false,
            'created_by' => $user->id,
        ]);

        $fallback = AppStorageConfig::create([
            'name' => 'fallback-local',
            'driver' => 'local',
            'config' => ['path' => 'public'],
            'is_active' => true,
            'priority' => 0,
            'redundancy' => false,
            'created_by' => $user->id,
        ]);

        StorageDiskMapping::create([
            'disk_name' => 'images_test_fallback_block',
            'label' => 'Test FB',
            'primary_storage_id' => $primary->id,
            'fallback_storage_ids' => [$fallback->id],
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('admin.app-storage.configs.destroy', $fallback));

        $response->assertRedirect(route('admin.app-storage.configs.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('app_storage_configs', ['id' => $fallback->id]);
    }

    public function test_can_delete_storage_config_when_not_referenced_by_mapping(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['dashboard_type' => 'admin', 'staff_profile' => 'none']
        );

        $user = User::factory()->create();
        $user->assignRole($role);

        $config = AppStorageConfig::create([
            'name' => 'orphan',
            'driver' => 'local',
            'config' => ['path' => 'public'],
            'is_active' => true,
            'priority' => 0,
            'redundancy' => false,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('admin.app-storage.configs.destroy', $config));

        $response->assertRedirect(route('admin.app-storage.configs.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('app_storage_configs', ['id' => $config->id]);
    }
}
