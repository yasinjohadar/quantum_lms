<?php

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Services\QuestionAttemptService;
use Tests\TestCase;

uses(TestCase::class);

function invokePracticeMatchingGrade(Question $question, QuestionAnswer $answer): array
{
    $service = new QuestionAttemptService;
    $reflection = new ReflectionMethod(QuestionAttemptService::class, 'gradeMatching');
    $reflection->setAccessible(true);

    return $reflection->invoke($service, $question, $answer);
}

function matchingQuestionWithPairs(array $pairs): Question
{
    $question = new Question(['type' => 'matching']);
    $options = collect();

    foreach ($pairs as $id => [$content, $matchTarget]) {
        $option = new QuestionOption([
            'content' => $content,
            'match_target' => $matchTarget,
        ]);
        $option->id = $id;
        $options->push($option);
    }

    $question->setRelation('options', $options);

    return $question;
}

test('practice gradeMatching awards full points when all pairs are correct', function () {
    $question = matchingQuestionWithPairs([
        1 => ['تفاحة', 'فاكهة'],
        2 => ['جزر', 'خضار'],
    ]);

    $answer = new QuestionAnswer([
        'max_points' => 10,
        'matching_pairs' => [1 => 'فاكهة', 2 => 'خضار'],
    ]);

    $result = invokePracticeMatchingGrade($question, $answer);

    expect($result['is_correct'])->toBeTrue()
        ->and($result['points'])->toBe(10.0);
});

test('practice gradeMatching awards partial credit for correct pairs only', function () {
    $question = matchingQuestionWithPairs([
        1 => ['تفاحة', 'فاكهة'],
        2 => ['جزر', 'خضار'],
    ]);

    $answer = new QuestionAnswer([
        'max_points' => 10,
        'matching_pairs' => [1 => 'فاكهة', 2 => 'خطأ'],
    ]);

    $result = invokePracticeMatchingGrade($question, $answer);

    expect($result['is_correct'])->toBeFalse()
        ->and($result['points'])->toBe(5.0);
});

test('practice gradeMatching returns zero when pairs are empty', function () {
    $question = matchingQuestionWithPairs([
        1 => ['تفاحة', 'فاكهة'],
    ]);

    $answer = new QuestionAnswer([
        'max_points' => 5,
        'matching_pairs' => [],
    ]);

    $result = invokePracticeMatchingGrade($question, $answer);

    expect($result)->toBe(['is_correct' => false, 'points' => 0.0]);
});

test('practice gradeMatching uses match_target not matching_content schema', function () {
    $question = matchingQuestionWithPairs([
        5 => ['Left', 'RightText'],
    ]);

    $answer = new QuestionAnswer([
        'max_points' => 8,
        'matching_pairs' => ['5' => 'RightText'],
    ]);

    $result = invokePracticeMatchingGrade($question, $answer);

    expect($result['is_correct'])->toBeTrue()
        ->and($result['points'])->toBe(8.0);
});
