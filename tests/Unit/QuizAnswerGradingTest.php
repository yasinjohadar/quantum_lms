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

test('gradeMatching awards full points when all pairs match match_target text', function () {
    $question = new Question(['type' => 'matching']);
    $opt1 = new \App\Models\QuestionOption(['id' => 1, 'content' => 'Apple', 'match_target' => 'فاكهة']);
    $opt1->id = 1;
    $opt2 = new \App\Models\QuestionOption(['id' => 2, 'content' => 'Carrot', 'match_target' => 'خضار']);
    $opt2->id = 2;
    $question->setRelation('options', collect([$opt1, $opt2]));

    $answer = quizAnswerWithQuestion($question, [
        'max_points' => 10,
        'matching_pairs' => [1 => 'فاكهة', 2 => 'خضار'],
    ]);

    $result = invokeQuizAnswerGradingMethod($answer, 'gradeMatching');

    expect($result['is_correct'])->toBeTrue()
        ->and($result['points'])->toBe(10.0);
});

test('gradeMatching awards partial points for some correct pairs', function () {
    $question = new Question(['type' => 'matching']);
    $opt1 = new \App\Models\QuestionOption(['content' => 'Apple', 'match_target' => 'فاكهة']);
    $opt1->id = 1;
    $opt2 = new \App\Models\QuestionOption(['content' => 'Carrot', 'match_target' => 'خضار']);
    $opt2->id = 2;
    $question->setRelation('options', collect([$opt1, $opt2]));

    $answer = quizAnswerWithQuestion($question, [
        'max_points' => 10,
        'matching_pairs' => [1 => 'فاكهة', 2 => 'غلط'],
    ]);

    $result = invokeQuizAnswerGradingMethod($answer, 'gradeMatching');

    expect($result['is_correct'])->toBeFalse()
        ->and($result['points'])->toBe(5.0);
});

test('gradeMatching returns zero when all pairs are wrong', function () {
    $question = new Question(['type' => 'matching']);
    $opt1 = new \App\Models\QuestionOption(['content' => 'Apple', 'match_target' => 'فاكهة']);
    $opt1->id = 1;
    $opt2 = new \App\Models\QuestionOption(['content' => 'Carrot', 'match_target' => 'خضار']);
    $opt2->id = 2;
    $question->setRelation('options', collect([$opt1, $opt2]));

    $answer = quizAnswerWithQuestion($question, [
        'max_points' => 10,
        'matching_pairs' => [1 => 'خضار', 2 => 'فاكهة'],
    ]);

    $result = invokeQuizAnswerGradingMethod($answer, 'gradeMatching');

    expect($result['is_correct'])->toBeFalse()
        ->and($result['points'])->toBe(0.0);
});

test('gradeMatching accepts string option ids in matching_pairs', function () {
    $question = new Question(['type' => 'matching']);
    $opt1 = new \App\Models\QuestionOption(['content' => 'A', 'match_target' => '1']);
    $opt1->id = 10;
    $question->setRelation('options', collect([$opt1]));

    $answer = quizAnswerWithQuestion($question, [
        'max_points' => 4,
        'matching_pairs' => ['10' => '1'],
    ]);

    $result = invokeQuizAnswerGradingMethod($answer, 'gradeMatching');

    expect($result['is_correct'])->toBeTrue()
        ->and($result['points'])->toBe(4.0);
});

test('gradeMatching does not score option ids as match targets', function () {
    $question = new Question(['type' => 'matching']);
    $opt1 = new \App\Models\QuestionOption(['content' => 'Left', 'match_target' => 'Right']);
    $opt1->id = 1;
    $opt2 = new \App\Models\QuestionOption(['content' => 'Other', 'match_target' => 'Target']);
    $opt2->id = 2;
    $question->setRelation('options', collect([$opt1, $opt2]));

    // Legacy quiz UI bug: values were option IDs instead of match_target text
    $answer = quizAnswerWithQuestion($question, [
        'max_points' => 10,
        'matching_pairs' => [1 => 2],
    ]);

    $result = invokeQuizAnswerGradingMethod($answer, 'gradeMatching');

    expect($result['is_correct'])->toBeFalse()
        ->and($result['points'])->toBe(0.0);
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

test('gradeOrdering awards full points when all positions match correct_order', function () {
    $question = new Question(['type' => 'ordering']);
    $opt1 = new \App\Models\QuestionOption(['content' => 'First', 'correct_order' => 1]);
    $opt1->id = 10;
    $opt2 = new \App\Models\QuestionOption(['content' => 'Second', 'correct_order' => 2]);
    $opt2->id = 20;
    $opt3 = new \App\Models\QuestionOption(['content' => 'Third', 'correct_order' => 3]);
    $opt3->id = 30;
    $question->setRelation('options', collect([$opt2, $opt1, $opt3]));

    $answer = quizAnswerWithQuestion($question, [
        'max_points' => 9,
        'ordering' => [10, 20, 30],
    ]);

    $result = invokeQuizAnswerGradingMethod($answer, 'gradeOrdering');

    expect($result['is_correct'])->toBeTrue()
        ->and($result['points'])->toBe(9.0);
});

test('gradeOrdering awards partial points for correct positions only', function () {
    $question = new Question(['type' => 'ordering']);
    $opt1 = new \App\Models\QuestionOption(['content' => 'First', 'correct_order' => 1]);
    $opt1->id = 1;
    $opt2 = new \App\Models\QuestionOption(['content' => 'Second', 'correct_order' => 2]);
    $opt2->id = 2;
    $question->setRelation('options', collect([$opt1, $opt2]));

    $answer = quizAnswerWithQuestion($question, [
        'max_points' => 10,
        'ordering' => [1, 99],
    ]);

    $result = invokeQuizAnswerGradingMethod($answer, 'gradeOrdering');

    expect($result['is_correct'])->toBeFalse()
        ->and($result['points'])->toBe(5.0);
});

test('gradeOrdering returns zero when all positions are wrong', function () {
    $question = new Question(['type' => 'ordering']);
    $opt1 = new \App\Models\QuestionOption(['content' => 'First', 'correct_order' => 1]);
    $opt1->id = 1;
    $opt2 = new \App\Models\QuestionOption(['content' => 'Second', 'correct_order' => 2]);
    $opt2->id = 2;
    $question->setRelation('options', collect([$opt1, $opt2]));

    $answer = quizAnswerWithQuestion($question, [
        'max_points' => 10,
        'ordering' => [2, 1],
    ]);

    $result = invokeQuizAnswerGradingMethod($answer, 'gradeOrdering');

    expect($result['is_correct'])->toBeFalse()
        ->and($result['points'])->toBe(0.0);
});

test('gradeOrdering accepts string option ids', function () {
    $question = new Question(['type' => 'ordering']);
    $opt1 = new \App\Models\QuestionOption(['content' => 'A', 'correct_order' => 1]);
    $opt1->id = 5;
    $question->setRelation('options', collect([$opt1]));

    $answer = quizAnswerWithQuestion($question, [
        'max_points' => 4,
        'ordering' => ['5'],
    ]);

    $result = invokeQuizAnswerGradingMethod($answer, 'gradeOrdering');

    expect($result['is_correct'])->toBeTrue()
        ->and($result['points'])->toBe(4.0);
});

test('gradeOrdering uses correct_order not display order field', function () {
    $question = new Question(['type' => 'ordering']);
    // Display order (order) differs from correct_order
    $optA = new \App\Models\QuestionOption(['content' => 'A', 'correct_order' => 2, 'order' => 1]);
    $optA->id = 1;
    $optB = new \App\Models\QuestionOption(['content' => 'B', 'correct_order' => 1, 'order' => 2]);
    $optB->id = 2;
    $question->setRelation('options', collect([$optA, $optB]));

    // Correct sequence by correct_order: B then A => [2, 1]
    $answer = quizAnswerWithQuestion($question, [
        'max_points' => 10,
        'ordering' => [2, 1],
    ]);

    $result = invokeQuizAnswerGradingMethod($answer, 'gradeOrdering');

    expect($result['is_correct'])->toBeTrue()
        ->and($result['points'])->toBe(10.0);
});

test('gradeNumerical marks exact answer as correct', function () {
    $question = new Question(['type' => 'numerical', 'tolerance' => 0]);
    $opt = new \App\Models\QuestionOption(['content' => '42', 'is_correct' => true]);
    $opt->id = 1;
    $question->setRelation('correctOptions', collect([$opt]));

    $answer = quizAnswerWithQuestion($question, [
        'max_points' => 5,
        'numeric_answer' => 42,
    ]);

    expect(invokeQuizAnswerGradingMethod($answer, 'gradeNumerical'))->toBeTrue();
});

test('gradeNumerical accepts answer within absolute tolerance', function () {
    $question = new Question(['type' => 'numerical', 'tolerance' => 0.5]);
    $opt = new \App\Models\QuestionOption(['content' => '10', 'is_correct' => true]);
    $opt->id = 1;
    $question->setRelation('correctOptions', collect([$opt]));

    $answer = quizAnswerWithQuestion($question, [
        'numeric_answer' => 10.4,
    ]);

    expect(invokeQuizAnswerGradingMethod($answer, 'gradeNumerical'))->toBeTrue();
});

test('gradeNumerical rejects answer outside tolerance', function () {
    $question = new Question(['type' => 'numerical', 'tolerance' => 0.5]);
    $opt = new \App\Models\QuestionOption(['content' => '10', 'is_correct' => true]);
    $opt->id = 1;
    $question->setRelation('correctOptions', collect([$opt]));

    $answer = quizAnswerWithQuestion($question, [
        'numeric_answer' => 11,
    ]);

    expect(invokeQuizAnswerGradingMethod($answer, 'gradeNumerical'))->toBeFalse();
});

test('gradeNumerical accepts zero as a valid student answer', function () {
    $question = new Question(['type' => 'numerical', 'tolerance' => 0]);
    $opt = new \App\Models\QuestionOption(['content' => '0', 'is_correct' => true]);
    $opt->id = 1;
    $question->setRelation('correctOptions', collect([$opt]));

    $answer = quizAnswerWithQuestion($question, [
        'numeric_answer' => 0,
    ]);

    expect(invokeQuizAnswerGradingMethod($answer, 'gradeNumerical'))->toBeTrue();
});

test('gradeNumerical returns false when numeric_answer is null', function () {
    $question = new Question(['type' => 'numerical', 'tolerance' => 0]);
    $opt = new \App\Models\QuestionOption(['content' => '5', 'is_correct' => true]);
    $opt->id = 1;
    $question->setRelation('correctOptions', collect([$opt]));

    $answer = quizAnswerWithQuestion($question, [
        'numeric_answer' => null,
    ]);

    expect(invokeQuizAnswerGradingMethod($answer, 'gradeNumerical'))->toBeFalse();
});

test('gradeDragDrop awards full points when all items match zone labels', function () {
    $question = new Question(['type' => 'drag_drop']);
    $opt1 = new \App\Models\QuestionOption(['content' => 'تفاحة', 'match_target' => 'فواكه']);
    $opt1->id = 1;
    $opt2 = new \App\Models\QuestionOption(['content' => 'جزر', 'match_target' => 'خضار']);
    $opt2->id = 2;
    $question->setRelation('options', collect([$opt1, $opt2]));

    $answer = quizAnswerWithQuestion($question, [
        'max_points' => 10,
        'drag_drop_assignments' => [1 => 'فواكه', 2 => 'خضار'],
    ]);

    $result = invokeQuizAnswerGradingMethod($answer, 'gradeDragDrop');

    expect($result['is_correct'])->toBeTrue()
        ->and($result['points'])->toBe(10.0);
});

test('gradeDragDrop awards partial points for correct zone assignments', function () {
    $question = new Question(['type' => 'drag_drop']);
    $opt1 = new \App\Models\QuestionOption(['content' => 'تفاحة', 'match_target' => 'فواكه']);
    $opt1->id = 1;
    $opt2 = new \App\Models\QuestionOption(['content' => 'جزر', 'match_target' => 'خضار']);
    $opt2->id = 2;
    $question->setRelation('options', collect([$opt1, $opt2]));

    $answer = quizAnswerWithQuestion($question, [
        'max_points' => 10,
        'drag_drop_assignments' => [1 => 'فواكه', 2 => 'فواكه'],
    ]);

    $result = invokeQuizAnswerGradingMethod($answer, 'gradeDragDrop');

    expect($result['is_correct'])->toBeFalse()
        ->and($result['points'])->toBe(5.0);
});

test('gradeDragDrop returns zero when assignments empty', function () {
    $question = new Question(['type' => 'drag_drop']);
    $opt1 = new \App\Models\QuestionOption(['content' => 'A', 'match_target' => 'Z1']);
    $opt1->id = 1;
    $question->setRelation('options', collect([$opt1]));

    $answer = quizAnswerWithQuestion($question, [
        'max_points' => 4,
        'drag_drop_assignments' => [],
    ]);

    $result = invokeQuizAnswerGradingMethod($answer, 'gradeDragDrop');

    expect($result)->toBe(['is_correct' => false, 'points' => 0.0]);
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
