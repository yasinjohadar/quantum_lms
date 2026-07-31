<?php

namespace Database\Factories;

use App\Models\AppStorageConfig;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BackupSchedule>
 */
class BackupScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'schedule_' . fake()->unique()->numerify('test_####'),
            'backup_type' => 'database',
            'frequency' => 'daily',
            'time' => '02:00',
            'days_of_week' => null,
            'day_of_month' => null,
            'storage_drivers' => fn () => [AppStorageConfig::create([
                'name' => 'test-schedule-storage-' . fake()->unique()->numerify('####'),
                'driver' => 'local',
                'config' => ['path' => 'public'],
                'is_active' => true,
                'priority' => 0,
                'redundancy' => false,
            ])->id],
            'compression_types' => ['zip'],
            'retention_days' => 30,
            'is_active' => true,
            'next_run_at' => now()->addDay(),
            'created_by' => fn () => User::factory()->create()->id,
        ];
    }

    /** جدولة مستحقة الآن (next_run_at في الماضي). */
    public function dueNow(): static
    {
        return $this->state(fn () => [
            'next_run_at' => now()->subMinute(),
        ]);
    }

    /** جدولة غير مستحقة (next_run_at في المستقبل). */
    public function notDue(): static
    {
        return $this->state(fn () => [
            'next_run_at' => now()->addDay(),
        ]);
    }
}
