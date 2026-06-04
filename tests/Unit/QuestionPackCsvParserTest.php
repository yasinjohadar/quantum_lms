<?php

use App\Services\QuestionPackImport\CsvQuestionPackParser;

test('biology csv parses five questions with correct letter from answer column', function () {
    $path = dirname(__DIR__, 2).'/docs/اختبار-أحياء.csv';
    $content = file_get_contents($path);

    $questions = (new CsvQuestionPackParser)->parse($content, 'single_choice');

    expect($questions)->toHaveCount(5);
    expect($questions[2]->correctLetter)->toBe('C');
    expect($questions[2]->options['C'])->toBe('cAMP');
});

test('biology csv fill blanks extracts blank answer from correct letter', function () {
    $path = dirname(__DIR__, 2).'/docs/اختبار-أحياء.csv';
    $content = file_get_contents($path);

    $questions = (new CsvQuestionPackParser)->parse($content, 'fill_blanks');

    expect($questions[2]->blankAnswers())->toBe(['cAMP']);
});

test('senses csv parses seven questions', function () {
    $path = dirname(__DIR__, 2).'/docs/اختبار-الحواس (1).csv';
    $content = file_get_contents($path);

    $questions = (new CsvQuestionPackParser)->parse($content, 'single_choice');

    expect($questions)->toHaveCount(7);
    expect($questions[4]->correctLetter)->toBe('A');
});
