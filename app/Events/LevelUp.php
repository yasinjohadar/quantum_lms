<?php

namespace App\Events;

use App\Models\User;
use App\Models\Level;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LevelUp implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public Level $level,
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
            'type' => 'level_up',
            'title' => 'ترقية مستوى!',
            'message' => "تهانينا! لقد وصلت للمستوى {$this->level->level_number}: {$this->level->name}",
            'data' => [
                'level_id' => $this->level->id,
                'level_number' => $this->level->level_number,
                'level_name' => $this->level->name,
                'total_points' => $this->metadata['total_points'] ?? 0,
            ],
            'timestamp' => now()->toIso8601String(),
            'icon' => 'fe fe-trending-up',
            'color' => 'primary',
        ];
    }
}
