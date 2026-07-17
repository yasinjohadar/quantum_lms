<?php

namespace App\Support;

use App\Models\Quiz;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * كائن محاولة معاينة يحاكي واجهة QuizAttempt للقوالب دون حفظ في DB.
 */
class QuizPreviewAttempt
{
    public int|string $id = 'preview';

    public int $quiz_id;

    public int $user_id;

    public int $attempt_number = 1;

    public Carbon $started_at;

    public ?Carbon $finished_at = null;

    public string $status = 'in_progress';

    public float|int $score = 0;

    public float|int $max_score = 0;

    public float|int $percentage = 0;

    public bool $passed = false;

    public ?array $question_order = null;

    public ?int $time_spent = null;

    public Quiz $quiz;

    /** @var Collection<int, PreviewQuizAnswer> */
    public Collection $answerModels;

    public function __construct(array $state, Quiz $quiz, Collection $answerModels)
    {
        $this->quiz_id = (int) $state['quiz_id'];
        $this->user_id = (int) ($state['user_id'] ?? 0);
        $this->attempt_number = (int) ($state['attempt_number'] ?? 1);
        $this->started_at = Carbon::parse($state['started_at']);
        $this->finished_at = ! empty($state['finished_at']) ? Carbon::parse($state['finished_at']) : null;
        $this->status = $state['status'] ?? 'in_progress';
        $this->score = $state['score'] ?? 0;
        $this->max_score = $state['max_score'] ?? ($quiz->total_points ?? 0);
        $this->percentage = $state['percentage'] ?? 0;
        $this->passed = (bool) ($state['passed'] ?? false);
        $this->question_order = $state['question_order'] ?? null;
        $this->time_spent = $state['time_spent'] ?? null;
        $this->quiz = $quiz;
        $this->answerModels = $answerModels;
    }

    public function getRemainingTimeAttribute(): ?int
    {
        if ($this->status !== 'in_progress' || ! $this->quiz->hasTimeLimit()) {
            return null;
        }

        $endTime = $this->started_at->copy()->addMinutes((int) $this->quiz->duration_minutes);

        return max(0, (int) now()->diffInSeconds($endTime, false));
    }

    public function getElapsedSecondsAttribute(): int
    {
        $end = $this->finished_at ?? now();

        return max(0, (int) $this->started_at->diffInSeconds($end));
    }

    public function getFormattedElapsedTimeAttribute(): string
    {
        return \App\Models\QuizAttempt::formatDurationSeconds($this->elapsed_seconds);
    }

    public function getFormattedRemainingTimeAttribute(): ?string
    {
        $remaining = $this->remaining_time;
        if ($remaining === null) {
            return null;
        }

        return sprintf('%d:%02d', intdiv($remaining, 60), $remaining % 60);
    }

    public function __get(string $key)
    {
        $method = 'get'.str_replace('_', '', ucwords($key, '_')).'Attribute';
        // Laravel-style snake accessors
        $studly = str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
        $accessor = 'get'.$studly.'Attribute';

        if (method_exists($this, $accessor)) {
            return $this->{$accessor}();
        }

        return null;
    }

    public function __isset(string $key): bool
    {
        $studly = str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
        $accessor = 'get'.$studly.'Attribute';

        return method_exists($this, $accessor) || property_exists($this, $key);
    }
}
