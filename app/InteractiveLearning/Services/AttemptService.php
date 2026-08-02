<?php

namespace App\InteractiveLearning\Services;

use App\InteractiveLearning\Models\LearningExperience;
use App\InteractiveLearning\Models\LearningExperienceAttempt;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class AttemptService
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function store(LearningExperience $experience, User $user, array $payload): LearningExperienceAttempt
    {
        if (! $experience->isPublished()) {
            throw ValidationException::withMessages([
                'experience' => 'التجربة غير منشورة.',
            ]);
        }

        $score = round((float) ($payload['score'] ?? 0), 2);
        $total = round((float) ($payload['total'] ?? 0), 2);
        $percentage = isset($payload['percentage'])
            ? round((float) $payload['percentage'], 2)
            : ($total > 0 ? round(($score / $total) * 100, 2) : 0.0);

        $answers = $payload['answers'] ?? [];
        if (! is_array($answers)) {
            $answers = [];
        }

        $resultPayload = $payload;
        $resultPayload['score'] = $score;
        $resultPayload['total'] = $total;
        $resultPayload['percentage'] = $percentage;
        $resultPayload['answers'] = $answers;
        $resultPayload['saved_at'] = now()->toIso8601String();

        return LearningExperienceAttempt::create([
            'learning_experience_id' => $experience->id,
            'user_id' => $user->id,
            'score' => $score,
            'total' => $total,
            'percentage' => $percentage,
            'duration' => max(0, (int) ($payload['duration'] ?? 0)),
            'started_at' => isset($payload['startedAt']) ? Carbon::parse($payload['startedAt']) : null,
            'finished_at' => isset($payload['finishedAt']) ? Carbon::parse($payload['finishedAt']) : now(),
            'answers_json' => $answers,
            'result_json' => $resultPayload,
        ]);
    }
}
