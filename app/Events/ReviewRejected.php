<?php

namespace App\Events;

use App\Models\User;
use App\Models\Review;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReviewRejected implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public Review $review,
        public string $reason,
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
        $reviewableName = $this->review->reviewable->name ?? 'عنصر غير معروف';
        
        return [
            'type' => 'review_rejected',
            'title' => 'تم رفض التقييم',
            'message' => "تم رفض تقييمك لـ {$reviewableName}. السبب: {$this->reason}",
            'data' => [
                'review_id' => $this->review->id,
                'reviewable_type' => $this->review->reviewable_type,
                'reviewable_id' => $this->review->reviewable_id,
                'reason' => $this->reason,
            ],
            'timestamp' => now()->toIso8601String(),
            'icon' => 'fe fe-x-circle',
            'color' => 'danger',
        ];
    }
}
