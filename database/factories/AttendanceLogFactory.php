<?php

namespace Database\Factories;

use App\Models\AttendanceLog;
use App\Models\LiveSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttendanceLog>
 */
class AttendanceLogFactory extends Factory
{
    protected $model = AttendanceLog::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $liveSession = LiveSession::factory()->create();
        $user = User::factory()->create();
        $joinedAt = fake()->dateTimeBetween('-1 hour', 'now');
        $leftAt = fake()->optional()->dateTimeBetween($joinedAt, 'now');

        return [
            'user_id' => $user->id,
            'live_session_id' => $liveSession->id,
            'zoom_meeting_id' => (string) fake()->numerify('##########'),
            'joined_at' => $joinedAt,
            'left_at' => $leftAt,
            'join_ip' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'duration_seconds' => $leftAt ? $joinedAt->diffInSeconds($leftAt) : null,
            'meta_json' => [],
        ];
    }
}
