<?php

namespace App\Events;

use App\Models\User;
use App\Models\Quiz;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuizStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public Quiz $quiz,
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
            'type' => 'quiz_started',
            'title' => 'بدء الاختبار',
            'message' => "لقد بدأت الاختبار: {$this->quiz->name}",
            'data' => [
                'quiz_id' => $this->quiz->id,
                'quiz_name' => $this->quiz->name,
                'time_limit' => $this->metadata['time_limit'] ?? null,
            ],
            'timestamp' => now()->toIso8601String(),
            'icon' => 'fe fe-play-circle',
            'color' => 'info',
        ];
    }
}
