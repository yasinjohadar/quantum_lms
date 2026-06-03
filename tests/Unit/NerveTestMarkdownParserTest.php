<?php

use App\Services\NerveTestImport\MarkdownNerveTestParser;

test('markdown parser extracts five nerve test questions from docs sample', function () {
    $path = dirname(__DIR__, 2).'/docs/اختبار-الأعصاب.md';
    $content = file_get_contents($path);

    $questions = (new MarkdownNerveTestParser)->parse($content);

    expect($questions)->toHaveCount(5);
    expect($questions[0]->title)->toContain('الأحاسيس');
    expect($questions[0]->correctLetter)->toBe('B');
    expect($questions[2]->correctLetter)->toBe('B');
    expect($questions[2]->optionB)->toBe('خطأ');
});
