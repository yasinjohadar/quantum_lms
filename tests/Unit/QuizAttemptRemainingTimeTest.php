<?php

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Tests\TestCase;

uses(TestCase::class);

test('remaining time is positive for a freshly started in-progress attempt', function () {
    $quiz = new Quiz;
    $quiz->duration_minutes = 30;

    $attempt = new QuizAttempt;
    $attempt->status = 'in_progress';
    $attempt->started_at = now();
    $attempt->setRelation('quiz', $quiz);

    $remaining = $attempt->remaining_time;

    expect($remaining)->toBeGreaterThan(1700)
        ->and($remaining)->toBeLessThanOrEqual(1800);
});

test('remaining time is zero when the quiz duration has elapsed', function () {
    $quiz = new Quiz;
    $quiz->duration_minutes = 30;

    $attempt = new QuizAttempt;
    $attempt->status = 'in_progress';
    $attempt->started_at = now()->subMinutes(31);
    $attempt->setRelation('quiz', $quiz);

    expect($attempt->remaining_time)->toBe(0);
});

test('remaining time is null when quiz has no duration', function () {
    $quiz = new Quiz;
    $quiz->duration_minutes = null;

    $attempt = new QuizAttempt;
    $attempt->status = 'in_progress';
    $attempt->started_at = now();
    $attempt->setRelation('quiz', $quiz);

    expect($attempt->remaining_time)->toBeNull();
});
