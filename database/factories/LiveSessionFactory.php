<?php

namespace Database\Factories;

use App\Models\LiveSession;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LiveSession>
 */
class LiveSessionFactory extends Factory
{
    protected $model = LiveSession::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $subject = Subject::factory()->create();

        return [
            'sessionable_type' => Subject::class,
            'sessionable_id' => $subject->id,
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'scheduled_at' => fake()->dateTimeBetween('now', '+1 month'),
            'duration_minutes' => fake()->numberBetween(30, 120),
            'timezone' => 'UTC',
            'status' => 'scheduled',
            'created_by' => User::factory(),
        ];
    }
}
