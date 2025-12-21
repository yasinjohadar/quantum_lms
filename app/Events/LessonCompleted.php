<?php

namespace App\Events;

use App\Models\User;
use App\Models\Lesson;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LessonCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public Lesson $lesson,
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
            'type' => 'lesson_completed',
            'title' => 'تم إكمال الدرس',
            'message' => "لقد أكملت الدرس: {$this->lesson->name}",
            'data' => [
                'lesson_id' => $this->lesson->id,
                'lesson_name' => $this->lesson->name,
                'points' => $this->metadata['points'] ?? 0,
            ],
            'timestamp' => now()->toIso8601String(),
            'icon' => 'fe fe-check-circle',
            'color' => 'success',
        ];
    }
}
