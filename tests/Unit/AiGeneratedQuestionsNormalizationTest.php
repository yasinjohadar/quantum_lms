<?php

use App\Models\AIQuestionGeneration;
use App\Services\AI\AIQuestionGenerationService;

test('normalizeParsedQuestionList accepts a direct question array', function () {
    $decoded = [
        [
            'type' => 'single_choice',
            'question' => 'ما هي عاصمة مصر؟',
            'options' => ['القاهرة', 'الإسكندرية'],
            'correct_answer' => 'القاهرة',
        ],
    ];

    $result = AIQuestionGenerationService::normalizeParsedQuestionList($decoded);

    expect($result)->toHaveCount(1)
        ->and($result[0]['question'])->toBe('ما هي عاصمة مصر؟');
});

test('normalizeParsedQuestionList unwraps questions wrapper key', function () {
    $decoded = [
        'questions' => [
            [
                'type' => 'true_false',
                'question' => 'الشمس نجم.',
                'correct_answer' => 'true',
            ],
        ],
    ];

    $result = AIQuestionGenerationService::normalizeParsedQuestionList($decoded);

    expect($result)->toHaveCount(1)
        ->and($result[0]['question'])->toBe('الشمس نجم.');
});

test('normalizeParsedQuestionList maps title to question field', function () {
    $decoded = [
        [
            'type' => 'short_answer',
            'title' => 'اكتب ناتج 2 + 2',
            'correct_answer' => '4',
        ],
    ];

    $result = AIQuestionGenerationService::normalizeParsedQuestionList($decoded);

    expect($result)->toHaveCount(1)
        ->and($result[0]['question'])->toBe('اكتب ناتج 2 + 2');
});

test('getResolvedGeneratedQuestions resolves wrapped json from model', function () {
    $generation = new AIQuestionGeneration([
        'status' => 'completed',
        'generated_questions' => [
            'questions' => [
                [
                    'type' => 'single_choice',
                    'stem' => 'اختر الإجابة الصحيحة',
                    'options' => ['أ', 'ب'],
                    'correct_answer' => 'أ',
                ],
            ],
        ],
    ]);

    $resolved = $generation->getResolvedGeneratedQuestions();

    expect($resolved)->toHaveCount(1)
        ->and($resolved[0]['question'])->toBe('اختر الإجابة الصحيحة');
});
