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

class QuizCompleted implements ShouldBroadcast
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
        $score = $this->metadata['score'] ?? 0;
        $percentage = $this->metadata['percentage'] ?? 0;
        $passed = $this->metadata['passed'] ?? false;

        return [
            'type' => 'quiz_completed',
            'title' => 'تم إكمال الاختبار',
            'message' => "لقد أكملت الاختبار: {$this->quiz->name} - النتيجة: {$percentage}%",
            'data' => [
                'quiz_id' => $this->quiz->id,
                'quiz_name' => $this->quiz->name,
                'score' => $score,
                'percentage' => $percentage,
                'passed' => $passed,
                'points' => $this->metadata['points'] ?? 0,
            ],
            'timestamp' => now()->toIso8601String(),
            'icon' => $passed ? 'fe fe-check-circle' : 'fe fe-x-circle',
            'color' => $passed ? 'success' : 'warning',
        ];
    }
}
