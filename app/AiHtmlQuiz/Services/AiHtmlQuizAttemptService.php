<?php

namespace App\AiHtmlQuiz\Services;

use App\AiHtmlQuiz\Models\AiHtmlQuiz;
use App\AiHtmlQuiz\Models\AiHtmlQuizAttempt;
use App\Models\User;
use Illuminate\Support\Carbon;

class AiHtmlQuizAttemptService
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function store(AiHtmlQuiz $quiz, User $user, array $payload): AiHtmlQuizAttempt
    {
        $score = max(0, (int) ($payload['score'] ?? 0));
        $total = max(0, (int) ($payload['total'] ?? 0));
        $percentage = isset($payload['percentage'])
            ? (float) $payload['percentage']
            : ($total > 0 ? round(($score / $total) * 100, 2) : 0.0);

        $duration = (int) ($payload['durationSeconds'] ?? $payload['duration'] ?? 0);
        $answers = $payload['answers'] ?? [];
        if (! is_array($answers)) {
            $answers = [];
        }

        return AiHtmlQuizAttempt::create([
            'ai_html_quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'score' => $score,
            'total' => $total,
            'percentage' => max(0, min(100, $percentage)),
            'duration' => max(0, $duration),
            'started_at' => isset($payload['startedAt']) ? Carbon::parse($payload['startedAt']) : now()->subSeconds(max(0, $duration)),
            'finished_at' => isset($payload['finishedAt']) ? Carbon::parse($payload['finishedAt']) : now(),
            'answers_json' => $answers,
            'result_json' => $payload,
        ]);
    }
}
