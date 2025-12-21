<?php

namespace App\Events;

use App\Models\User;
use App\Models\Question;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuestionAnswered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public Question $question,
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
        $isCorrect = $this->metadata['is_correct'] ?? false;
        $score = $this->metadata['score'] ?? 0;

        return [
            'type' => 'question_answered',
            'title' => $isCorrect ? 'إجابة صحيحة!' : 'إجابة خاطئة',
            'message' => $isCorrect 
                ? "إجابة صحيحة! لقد حصلت على {$score} نقطة"
                : "إجابة خاطئة. حاول مرة أخرى!",
            'data' => [
                'question_id' => $this->question->id,
                'is_correct' => $isCorrect,
                'score' => $score,
                'points' => $this->metadata['points'] ?? 0,
            ],
            'timestamp' => now()->toIso8601String(),
            'icon' => $isCorrect ? 'fe fe-check' : 'fe fe-x',
            'color' => $isCorrect ? 'success' : 'danger',
        ];
    }
}
