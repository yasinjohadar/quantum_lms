<?php

namespace Database\Factories;

use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Enrollment>
 */
class EnrollmentFactory extends Factory
{
    protected $model = Enrollment::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subject_id' => Subject::factory(),
            'enrolled_by' => User::factory(),
            'enrolled_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'status' => 'active',
            'notes' => null,
        ];
    }
}
