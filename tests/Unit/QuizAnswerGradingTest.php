<?php

use App\Models\Question;
use App\Models\QuizAnswer;
use Tests\TestCase;

uses(TestCase::class);

function invokeQuizAnswerGradingMethod(QuizAnswer $answer, string $method): array|bool
{
    $reflection = new ReflectionMethod(QuizAnswer::class, $method);
    $reflection->setAccessible(true);

    return $reflection->invoke($answer);
}

function quizAnswerWithQuestion(Question $question, array $attrs = []): QuizAnswer
{
    $answer = new QuizAnswer(array_merge(['max_points' => 10], $attrs));
    $answer->setRelation('question', $question);

    return $answer;
}

test('gradeMultipleChoice returns zero points when no correct options configured', function () {
    $question = new Question(['type' => 'multiple_choice', 'id' => 1]);
    $question->setRelation('correctOptions', collect());

    $answer = quizAnswerWithQuestion($question, ['selected_options' => [1, 2]]);

    $result = invokeQuizAnswerGradingMethod($answer, 'gradeMultipleChoice');

    expect($result)->toBe(['is_correct' => false, 'points' => 0]);
});

test('gradeFillBlanks returns zero points when blank_answers is empty', function () {
    $question = new Question([
        'type' => 'fill_blanks',
        'blank_answers' => [],
        'case_sensitive' => false,
    ]);

    $answer = quizAnswerWithQuestion($question, [
        'fill_blanks_answers' => ['answer'],
    ]);

    $result = invokeQuizAnswerGradingMethod($answer, 'gradeFillBlanks');

    expect($result)->toBe(['is_correct' => false, 'points' => 0]);
});

test('gradeMatching returns zero points when question has no options', function () {
    $question = new Question(['type' => 'matching']);
    $question->setRelation('options', collect());

    $answer = quizAnswerWithQuestion($question, [
        'matching_pairs' => [1 => 'x'],
    ]);

    $result = invokeQuizAnswerGradingMethod($answer, 'gradeMatching');

    expect($result)->toBe(['is_correct' => false, 'points' => 0]);
});

test('gradeOrdering returns zero points when question has no options', function () {
    $question = new Question(['type' => 'ordering', 'id' => 1]);
    $question->setRelation('options', collect());

    $answer = quizAnswerWithQuestion($question, [
        'ordering' => [1],
    ]);

    $result = invokeQuizAnswerGradingMethod($answer, 'gradeOrdering');

    expect($result)->toBe(['is_correct' => false, 'points' => 0]);
});

test('autoGrade does not throw DivisionByZeroError for multiple choice without correct options', function () {
    $question = new Question(['type' => 'multiple_choice', 'id' => 999999]);
    $question->setRelation('correctOptions', collect());

    $answer = quizAnswerWithQuestion($question, [
        'selected_options' => [1],
        'attempt_id' => 1,
        'question_id' => 999999,
    ]);

    expect(fn () => invokeQuizAnswerGradingMethod($answer, 'gradeMultipleChoice'))
        ->not->toThrow(DivisionByZeroError::class);
});
