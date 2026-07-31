<?php

namespace Database\Factories;

use App\Models\AppStorageConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Backup>
 */
class BackupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'backup_' . fake()->unique()->numerify('test_####'),
            'type' => 'manual',
            'backup_type' => 'database',
            'storage_config_id' => fn () => AppStorageConfig::create([
                'name' => 'test-storage-' . fake()->unique()->numerify('####'),
                'driver' => 'local',
                'config' => ['path' => 'public'],
                'is_active' => true,
                'priority' => 0,
                'redundancy' => false,
            ])->id,
            'storage_path' => 'backups/test/backup.zip',
            'file_path' => '',
            'file_size' => fake()->numberBetween(1000, 500000),
            'compression_type' => 'zip',
            'status' => 'completed',
            'started_at' => now()->subMinutes(5),
            'completed_at' => now(),
            'duration' => 30,
            'retention_days' => 30,
            'expires_at' => now()->addDays(30),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => 'pending',
            'started_at' => null,
            'completed_at' => null,
        ]);
    }

    public function running(): static
    {
        return $this->state(fn () => [
            'status' => 'running',
            'started_at' => now()->subMinutes(5),
            'completed_at' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => 'failed',
            'completed_at' => now(),
            'error_message' => 'قصد الاختبار',
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'expires_at' => now()->subDays(1),
        ]);
    }
}
