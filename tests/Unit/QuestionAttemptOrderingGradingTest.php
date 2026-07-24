<?php

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Services\QuestionAttemptService;
use Tests\TestCase;

uses(TestCase::class);

function invokePracticeOrderingGrade(Question $question, QuestionAnswer $answer): array
{
    $service = new QuestionAttemptService;
    $reflection = new ReflectionMethod(QuestionAttemptService::class, 'gradeOrdering');
    $reflection->setAccessible(true);

    return $reflection->invoke($service, $question, $answer);
}

function orderingQuestionWithItems(array $items): Question
{
    $question = new Question(['type' => 'ordering']);
    $options = collect();

    foreach ($items as $id => [$content, $correctOrder]) {
        $option = new QuestionOption([
            'content' => $content,
            'correct_order' => $correctOrder,
        ]);
        $option->id = $id;
        $options->push($option);
    }

    $question->setRelation('options', $options);

    return $question;
}

test('practice gradeOrdering awards full points when sequence matches correct_order', function () {
    $question = orderingQuestionWithItems([
        1 => ['أولاً', 1],
        2 => ['ثانياً', 2],
        3 => ['ثالثاً', 3],
    ]);

    $answer = new QuestionAnswer([
        'max_points' => 9,
        'ordering' => [1, 2, 3],
    ]);

    $result = invokePracticeOrderingGrade($question, $answer);

    expect($result['is_correct'])->toBeTrue()
        ->and($result['points'])->toBe(9.0);
});

test('practice gradeOrdering awards partial credit for correct positions', function () {
    $question = orderingQuestionWithItems([
        1 => ['أولاً', 1],
        2 => ['ثانياً', 2],
    ]);

    $answer = new QuestionAnswer([
        'max_points' => 10,
        'ordering' => [1, 99],
    ]);

    $result = invokePracticeOrderingGrade($question, $answer);

    expect($result['is_correct'])->toBeFalse()
        ->and($result['points'])->toBe(5.0);
});

test('practice gradeOrdering accepts string ids from JSON payload', function () {
    $question = orderingQuestionWithItems([
        7 => ['A', 1],
        8 => ['B', 2],
    ]);

    $answer = new QuestionAnswer([
        'max_points' => 10,
        'ordering' => ['7', '8'],
    ]);

    $result = invokePracticeOrderingGrade($question, $answer);

    expect($result['is_correct'])->toBeTrue()
        ->and($result['points'])->toBe(10.0);
});

test('practice gradeOrdering uses correct_order not order field', function () {
    $question = new Question(['type' => 'ordering']);
    $optA = new QuestionOption(['content' => 'A', 'correct_order' => 2, 'order' => 1]);
    $optA->id = 1;
    $optB = new QuestionOption(['content' => 'B', 'correct_order' => 1, 'order' => 2]);
    $optB->id = 2;
    $question->setRelation('options', collect([$optA, $optB]));

    $answer = new QuestionAnswer([
        'max_points' => 10,
        'ordering' => [2, 1],
    ]);

    $result = invokePracticeOrderingGrade($question, $answer);

    expect($result['is_correct'])->toBeTrue()
        ->and($result['points'])->toBe(10.0);
});
