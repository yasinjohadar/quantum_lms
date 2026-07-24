<?php

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Services\QuestionAttemptService;
use Tests\TestCase;

uses(TestCase::class);

function invokePracticeDragDropGrade(Question $question, QuestionAnswer $answer): array
{
    $service = new QuestionAttemptService;
    $reflection = new ReflectionMethod(QuestionAttemptService::class, 'gradeDragDrop');
    $reflection->setAccessible(true);

    return $reflection->invoke($service, $question, $answer);
}

function dragDropQuestion(array $items): Question
{
    $question = new Question(['type' => 'drag_drop']);
    $options = collect();

    foreach ($items as $id => [$content, $zone]) {
        $option = new QuestionOption([
            'content' => $content,
            'match_target' => $zone,
            'is_correct' => true,
        ]);
        $option->id = $id;
        $options->push($option);
    }

    $question->setRelation('options', $options);

    return $question;
}

test('practice gradeDragDrop awards full points for correct zone map', function () {
    $question = dragDropQuestion([
        1 => ['تفاحة', 'فواكه'],
        2 => ['جزر', 'خضار'],
    ]);

    $answer = new QuestionAnswer([
        'max_points' => 10,
        'answer' => [1 => 'فواكه', 2 => 'خضار'],
    ]);

    $result = invokePracticeDragDropGrade($question, $answer);

    expect($result['is_correct'])->toBeTrue()
        ->and($result['points'])->toBe(10.0);
});

test('practice gradeDragDrop awards partial credit', function () {
    $question = dragDropQuestion([
        1 => ['تفاحة', 'فواكه'],
        2 => ['جزر', 'خضار'],
    ]);

    $answer = new QuestionAnswer([
        'max_points' => 10,
        'answer' => [1 => 'فواكه', 2 => 'فواكه'],
    ]);

    $result = invokePracticeDragDropGrade($question, $answer);

    expect($result['is_correct'])->toBeFalse()
        ->and($result['points'])->toBe(5.0);
});

test('practice gradeDragDrop accepts string option ids', function () {
    $question = dragDropQuestion([
        9 => ['Item', 'Zone A'],
    ]);

    $answer = new QuestionAnswer([
        'max_points' => 5,
        'answer' => ['9' => 'Zone A'],
    ]);

    $result = invokePracticeDragDropGrade($question, $answer);

    expect($result['is_correct'])->toBeTrue()
        ->and($result['points'])->toBe(5.0);
});
