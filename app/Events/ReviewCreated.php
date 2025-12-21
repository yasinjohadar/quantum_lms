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

class ReviewCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public Review $review,
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
            'type' => 'review_created',
            'title' => 'تم إنشاء التقييم',
            'message' => $this->review->status === 'approved' 
                ? "تم نشر تقييمك لـ {$reviewableName} بنجاح"
                : "تم إرسال تقييمك لـ {$reviewableName}. سيتم مراجعته قبل النشر",
            'data' => [
                'review_id' => $this->review->id,
                'reviewable_type' => $this->review->reviewable_type,
                'reviewable_id' => $this->review->reviewable_id,
                'rating' => $this->review->rating,
                'status' => $this->review->status,
            ],
            'timestamp' => now()->toIso8601String(),
            'icon' => 'fe fe-star',
            'color' => $this->review->status === 'approved' ? 'success' : 'info',
        ];
    }
}
