<?php

use App\Services\NerveTestImport\CsvNerveTestParser;

test('csv parser extracts five questions and correct answer for swapped options row', function () {
    $path = dirname(__DIR__, 2).'/docs/اختبار-الأعصاب.csv';
    $content = file_get_contents($path);

    $questions = (new CsvNerveTestParser)->parse($content);

    expect($questions)->toHaveCount(5);
    expect($questions[0]->correctLetter)->toBe('A');
    expect($questions[2]->correctLetter)->toBe('B');
    expect($questions[2]->optionB)->toBe('خطأ');
});
