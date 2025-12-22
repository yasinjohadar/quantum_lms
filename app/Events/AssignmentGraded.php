<?php

namespace App\Events;

use App\Models\User;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssignmentGraded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public Assignment $assignment,
        public AssignmentSubmission $submission,
        public array $metadata = []
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user.{$this->user->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => 'assignment_graded',
            'title' => 'تم تصحيح الواجب',
            'message' => "تم تصحيح الواجب: {$this->assignment->title} - الدرجة: {$this->submission->total_score} / {$this->assignment->max_score}",
            'data' => [
                'assignment_id' => $this->assignment->id,
                'submission_id' => $this->submission->id,
                'score' => $this->submission->total_score,
                'max_score' => $this->assignment->max_score,
            ],
            'timestamp' => now()->toIso8601String(),
            'icon' => 'fe fe-check-circle',
            'color' => 'success',
        ];
    }
}
