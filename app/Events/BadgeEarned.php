<?php

namespace App\Events;

use App\Models\User;
use App\Models\Badge;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BadgeEarned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public Badge $badge,
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
            'type' => 'badge_earned',
            'title' => 'شارة جديدة!',
            'message' => "لقد حصلت على الشارة: {$this->badge->name}",
            'data' => [
                'badge_id' => $this->badge->id,
                'badge_name' => $this->badge->name,
                'badge_icon' => $this->badge->icon,
                'badge_color' => $this->badge->color,
            ],
            'timestamp' => now()->toIso8601String(),
            'icon' => $this->badge->icon ?? 'fe fe-award',
            'color' => $this->badge->color ?? 'primary',
        ];
    }
}
