<?php

use App\Services\AI\AIQuestionGenerationService;

it('humanizes high demand api errors in arabic', function () {
    $message = 'This model is currently experiencing high demand. Spikes in demand are usually temporary.';

    $result = AIQuestionGenerationService::humanizeApiErrorMessage($message);

    expect($result)->toContain('الموديل مشغول');
});

it('humanizes rate limit errors in arabic', function () {
    $result = AIQuestionGenerationService::humanizeApiErrorMessage('Error 429: rate limit exceeded');

    expect($result)->toContain('الموديل مشغول');
});

it('passes through unknown errors unchanged', function () {
    $message = 'خطأ مخصص من النظام';

    expect(AIQuestionGenerationService::humanizeApiErrorMessage($message))->toBe($message);
});

it('humanizes timeout errors in arabic', function () {
    $result = AIQuestionGenerationService::humanizeApiErrorMessage('Request timed out after 120 seconds');

    expect($result)->toContain('مهلة');
});
