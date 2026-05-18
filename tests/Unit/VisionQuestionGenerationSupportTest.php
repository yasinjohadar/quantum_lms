<?php

use App\Services\AI\VisionQuestionGenerationSupport;

uses(Tests\TestCase::class);

it('builds openai messages with multiple images', function () {
    $messages = VisionQuestionGenerationSupport::buildOpenAiStyleMessagesWithImages('Analyze these pages', [
        ['mime' => 'image/jpeg', 'binary' => 'fake-image-1'],
        ['mime' => 'image/png', 'binary' => 'fake-image-2'],
    ]);

    expect($messages)->toHaveCount(1);
    expect($messages[0]['role'])->toBe('user');

    $content = $messages[0]['content'];
    expect($content)->toBeArray();

    $textParts = array_filter($content, fn ($p) => ($p['type'] ?? '') === 'text');
    $imageParts = array_filter($content, fn ($p) => ($p['type'] ?? '') === 'image_url');

    expect(count($textParts))->toBe(1);
    expect(count($imageParts))->toBe(2);
});

it('single image helper delegates to multi image builder', function () {
    $single = VisionQuestionGenerationSupport::buildOpenAiStyleMessages('prompt', 'image/jpeg', 'binary');
    $multi = VisionQuestionGenerationSupport::buildOpenAiStyleMessagesWithImages('prompt', [
        ['mime' => 'image/jpeg', 'binary' => 'binary'],
    ]);

    expect($single)->toEqual($multi);
});
