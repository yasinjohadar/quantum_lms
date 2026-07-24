<?php

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Services\QuestionAttemptService;
use Tests\TestCase;

uses(TestCase::class);

function invokePracticeNumericalGrade(Question $question, QuestionAnswer $answer): bool
{
    $service = new QuestionAttemptService;
    $reflection = new ReflectionMethod(QuestionAttemptService::class, 'gradeNumerical');
    $reflection->setAccessible(true);

    return $reflection->invoke($service, $question, $answer);
}

function numericalQuestion(float|string $correct, float $tolerance = 0): Question
{
    $question = new Question([
        'type' => 'numerical',
        'tolerance' => $tolerance,
    ]);
    $option = new QuestionOption([
        'content' => (string) $correct,
        'is_correct' => true,
    ]);
    $option->id = 1;
    $question->setRelation('correctOptions', collect([$option]));

    return $question;
}

test('practice gradeNumerical accepts exact answer', function () {
    $question = numericalQuestion(25);
    $answer = new QuestionAnswer(['numeric_answer' => 25, 'max_points' => 1]);

    expect(invokePracticeNumericalGrade($question, $answer))->toBeTrue();
});

test('practice gradeNumerical accepts answer within tolerance', function () {
    $question = numericalQuestion(10, 0.5);
    $answer = new QuestionAnswer(['numeric_answer' => 10.5, 'max_points' => 1]);

    expect(invokePracticeNumericalGrade($question, $answer))->toBeTrue();
});

test('practice gradeNumerical rejects answer outside tolerance', function () {
    $question = numericalQuestion(10, 0.5);
    $answer = new QuestionAnswer(['numeric_answer' => 11, 'max_points' => 1]);

    expect(invokePracticeNumericalGrade($question, $answer))->toBeFalse();
});

test('practice gradeNumerical accepts zero answer when correct is zero', function () {
    $question = numericalQuestion(0);
    $answer = new QuestionAnswer(['numeric_answer' => 0, 'max_points' => 1]);

    expect(invokePracticeNumericalGrade($question, $answer))->toBeTrue();
});

test('practice gradeNumerical returns false when answer is null', function () {
    $question = numericalQuestion(5);
    $answer = new QuestionAnswer(['numeric_answer' => null, 'max_points' => 1]);

    expect(invokePracticeNumericalGrade($question, $answer))->toBeFalse();
});
