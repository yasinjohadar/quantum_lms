<?php

namespace App\Events;

use App\Models\User;
use App\Models\Challenge;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChallengeCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public Challenge $challenge,
        public array $metadata = []
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user.{$this->user->id}"),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'notification';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'type' => 'challenge_completed',
            'title' => 'تحدٍ مكتمل!',
            'message' => "لقد أكملت التحدي: {$this->challenge->name}",
            'data' => [
                'challenge_id' => $this->challenge->id,
                'challenge_name' => $this->challenge->name,
                'points' => $this->metadata['points'] ?? 0,
            ],
            'timestamp' => now()->toIso8601String(),
            'icon' => 'fe fe-flag',
            'color' => 'success',
        ];
    }
}
