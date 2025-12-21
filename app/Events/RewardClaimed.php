<?php

namespace App\Events;

use App\Models\User;
use App\Models\Reward;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RewardClaimed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public Reward $reward,
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
            'type' => 'reward_claimed',
            'title' => 'مكافأة مستبدلة!',
            'message' => "لقد استبدلت النقاط بالمكافأة: {$this->reward->name}",
            'data' => [
                'reward_id' => $this->reward->id,
                'reward_name' => $this->reward->name,
                'reward_type' => $this->reward->type,
                'points_cost' => $this->reward->points_cost,
            ],
            'timestamp' => now()->toIso8601String(),
            'icon' => 'fe fe-gift',
            'color' => 'info',
        ];
    }
}
