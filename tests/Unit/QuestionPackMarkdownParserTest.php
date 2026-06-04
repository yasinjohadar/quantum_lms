<?php

use App\Services\QuestionPackImport\MarkdownQuestionPackParser;

test('biology markdown parses five single choice questions', function () {
    $path = dirname(__DIR__, 2).'/docs/اختبار-أحياء.md';
    $content = file_get_contents($path);

    $questions = (new MarkdownQuestionPackParser)->parse($content, 'single_choice');

    expect($questions)->toHaveCount(5);
    expect($questions[0]->correctLetter)->toBe('A');
    expect($questions[2]->correctLetter)->toBe('A');
    expect($questions[2]->options['A'])->toContain('cAMP');
    expect($questions[0]->options)->toHaveKeys(['A', 'B', 'C', 'D']);
});

test('biology markdown fill blanks uses correct option text', function () {
    $path = dirname(__DIR__, 2).'/docs/اختبار-أحياء.md';
    $content = file_get_contents($path);

    $questions = (new MarkdownQuestionPackParser)->parse($content, 'fill_blanks');

    expect($questions[0]->blankAnswers())->toBe(['الوطاء']);
    expect($questions[1]->hasBlankPlaceholder())->toBeTrue();
});

test('senses markdown parses seven questions', function () {
    $path = dirname(__DIR__, 2).'/docs/اختبار-الحواس (3).md';
    $content = file_get_contents($path);

    $questions = (new MarkdownQuestionPackParser)->parse($content, 'single_choice');

    expect($questions)->toHaveCount(7);
    expect($questions[0]->correctLetter)->toBe('D');
});
