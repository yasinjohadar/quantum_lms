<?php

namespace Database\Factories;

use App\Models\LiveSession;
use App\Models\User;
use App\Models\ZoomJoinToken;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ZoomJoinToken>
 */
class ZoomJoinTokenFactory extends Factory
{
    protected $model = ZoomJoinToken::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $liveSession = LiveSession::factory()->create();
        $user = User::factory()->create();

        return [
            'user_id' => $user->id,
            'live_session_id' => $liveSession->id,
            'token_hash' => Hash::make(fake()->sha256()),
            'expires_at' => fake()->dateTimeBetween('now', '+1 hour'),
            'used_at' => null,
            'use_count' => 0,
            'max_uses' => 1,
            'user_agent_hash' => hash('sha256', fake()->userAgent()),
            'ip_prefix' => fake()->ipv4(),
        ];
    }
}
