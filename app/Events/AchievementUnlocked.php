<?php

namespace App\Events;

use App\Models\User;
use App\Models\Achievement;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AchievementUnlocked implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public Achievement $achievement,
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
            'type' => 'achievement_unlocked',
            'title' => 'إنجاز جديد!',
            'message' => "لقد فتحت الإنجاز: {$this->achievement->name}",
            'data' => [
                'achievement_id' => $this->achievement->id,
                'achievement_name' => $this->achievement->name,
                'points_reward' => $this->achievement->points_reward,
            ],
            'timestamp' => now()->toIso8601String(),
            'icon' => 'fe fe-trophy',
            'color' => 'warning',
        ];
    }
}
