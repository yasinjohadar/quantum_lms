<?php

use App\Services\ExtensionImport\NotebookLmQuestionMapper;

test('notebooklm mapper normalizes alternate field names', function () {
    $mapper = new NotebookLmQuestionMapper;

    $result = $mapper->normalizeMany([
        [
            'question' => 'سؤال تجريبي',
            'type' => 'multiple_choice',
            'options' => ['أ', 'ب', 'ج'],
        ],
    ]);

    expect($result['questions'][0]['title'])->toBe('سؤال تجريبي')
        ->and($result['questions'][0]['type'])->toBe('multiple_choice')
        ->and(count($result['questions'][0]['options']))->toBe(3);
});

test('notebooklm mapper accepts answerOptions field', function () {
    $mapper = new NotebookLmQuestionMapper;

    $result = $mapper->normalizeMany([
        [
            'question' => 'سؤال من NotebookLM',
            'answerOptions' => [
                ['text' => 'أ', 'isCorrect' => true],
                ['text' => 'ب', 'isCorrect' => false],
            ],
        ],
    ]);

    expect($result['questions'])->toHaveCount(1)
        ->and($result['questions'][0]['options'][0]['is_correct'])->toBeTrue();
});

test('notebooklm mapper skips invalid questions without failing batch', function () {
    $mapper = new NotebookLmQuestionMapper;

    $result = $mapper->normalizeMany([
        ['title' => 'بدون خيارات', 'type' => 'single_choice', 'options' => []],
        [
            'title' => 'سؤال صالح',
            'options' => [
                ['text' => '1', 'is_correct' => true],
                ['text' => '2', 'is_correct' => false],
            ],
        ],
    ]);

    expect($result['questions'])->toHaveCount(1)
        ->and($result['errors'])->toHaveCount(1);
});
