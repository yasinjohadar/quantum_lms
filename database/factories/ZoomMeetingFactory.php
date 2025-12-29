<?php

namespace Database\Factories;

use App\Models\LiveSession;
use App\Models\User;
use App\Models\ZoomMeeting;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ZoomMeeting>
 */
class ZoomMeetingFactory extends Factory
{
    protected $model = ZoomMeeting::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $liveSession = LiveSession::factory()->create();

        return [
            'live_session_id' => $liveSession->id,
            'zoom_meeting_id' => (string) fake()->numerify('##########'),
            'zoom_uuid' => fake()->uuid(),
            'host_email' => fake()->email(),
            'host_id' => fake()->uuid(),
            'topic' => fake()->sentence(),
            'start_time' => fake()->dateTimeBetween('now', '+1 month'),
            'duration' => fake()->numberBetween(30, 120),
            'timezone' => 'UTC',
            'encrypted_passcode' => Crypt::encryptString(fake()->numerify('######')),
            'settings_json' => [],
            'status' => 'created',
            'created_by' => User::factory(),
        ];
    }
}
