<?php

use App\InteractiveLearning\Services\ContentLogicChecker;

test('content logic strips emoji piles from classic icons', function () {
    $checker = new ContentLogicChecker();
    $result = $checker->checkAndFix([
        'stem' => 'اختر الرقم',
        'payload' => [
            'options' => [
                ['id' => 'a', 'label' => '5', 'icon' => '🍎🍎🍎🍎🍎'],
            ],
            'correctId' => 'a',
        ],
    ], 'classic');

    $part = $checker->partition($result);

    expect($part['question']['payload']['options'][0]['icon'])->toBe('🍎')
        ->and($part['ok'])->toBeTrue();
});

test('content logic adds scene for counting dynamic questions', function () {
    $checker = new ContentLogicChecker();
    $result = $checker->checkAndFix([
        'stem' => 'كم عدد التفاحات؟',
        'stemBlocks' => [
            ['type' => 'text', 'text' => 'كم عدد التفاحات الحمراء التي يراها؟'],
        ],
        'interaction' => [
            'type' => 'single_choice',
            'payload' => [
                'options' => [
                    ['id' => 'a', 'label' => '2', 'icon' => '2'],
                    ['id' => 'b', 'label' => '3', 'icon' => '3'],
                ],
                'correctId' => 'b',
            ],
        ],
    ], 'dynamic');

    $part = $checker->partition($result);
    $types = array_column($part['question']['stemBlocks'], 'type');

    expect($types)->toContain('scene')
        ->and($part['ok'])->toBeTrue();

    $scene = collect($part['question']['stemBlocks'])->firstWhere('type', 'scene');
    expect((int) $scene['count'])->toBe(3);
});

test('isEmojiPile detects repeated apples', function () {
    $checker = new ContentLogicChecker();
    expect($checker->isEmojiPile('🍎🍎🍎'))->toBeTrue()
        ->and($checker->isEmojiPile('🍎'))->toBeFalse()
        ->and($checker->isEmojiPile('lion'))->toBeFalse();
});
