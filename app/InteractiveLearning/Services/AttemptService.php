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

        $score = (int) ($payload['score'] ?? 0);
        $total = (int) ($payload['total'] ?? 0);
        $percentage = isset($payload['percentage'])
            ? (float) $payload['percentage']
            : ($total > 0 ? round(($score / $total) * 100, 2) : 0.0);

        return LearningExperienceAttempt::create([
            'learning_experience_id' => $experience->id,
            'user_id' => $user->id,
            'score' => $score,
            'total' => $total,
            'percentage' => $percentage,
            'duration' => (int) ($payload['duration'] ?? 0),
            'started_at' => isset($payload['startedAt']) ? Carbon::parse($payload['startedAt']) : null,
            'finished_at' => isset($payload['finishedAt']) ? Carbon::parse($payload['finishedAt']) : now(),
            'answers_json' => $payload['answers'] ?? [],
            'result_json' => $payload,
        ]);
    }
}
