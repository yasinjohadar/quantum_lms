<?php

use App\AiHtmlQuiz\Services\AiHtmlQuizBundleAssembler;
use App\AiHtmlQuiz\Services\AiHtmlQuizBundleNormalizer;
use App\AiHtmlQuiz\Services\AiHtmlQuizGenerationService;

test('generation prompt wires default sound paths', function () {
    $service = app(AiHtmlQuizGenerationService::class);
    $ref = new ReflectionClass($service);
    $method = $ref->getMethod('buildPrompt');
    $method->setAccessible(true);
    $prompt = $method->invoke($service, 'جمع الأعداد حتى 20', 'أهداف', 5, 'medium', 'سحب', ['single_choice', 'true_false']);

    expect($prompt)->toContain('/sounds/ai-html-quiz/success-01.mp3')
        ->and($prompt)->toContain('/sounds/ai-html-quiz/wrong-01.mp3')
        ->and($prompt)->toContain('ile-html-quiz-result')
        ->and($prompt)->toContain('بدون مكتبات CDN')
        ->and($prompt)->toContain('جمع الأعداد حتى 20')
        ->and($prompt)->toContain('اختيار من متعدد')
        ->and($prompt)->toContain('صح أو خطأ')
        ->and($prompt)->toContain('التزم به حرفياً');
});

test('question types filter keeps only known keys', function () {
    $filtered = \App\AiHtmlQuiz\Support\AiHtmlQuizQuestionTypes::filterValid([
        'single_choice',
        'bogus',
        'matching',
        'single_choice',
    ]);

    expect($filtered)->toBe(['single_choice', 'matching']);
});

test('default sound assets exist on disk', function () {
    $dir = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'sounds'.DIRECTORY_SEPARATOR.'ai-html-quiz';
    $required = [
        'success-01.mp3',
        'wrong-01.mp3',
        'pass-01.mp3',
        'retry-01.mp3',
        'continue-01.mp3',
    ];

    foreach ($required as $file) {
        expect(file_exists($dir.DIRECTORY_SEPARATOR.$file))->toBeTrue();
    }
});

test('parseBundle accepts delimiter format with KEEP markers', function () {
    $service = app(AiHtmlQuizGenerationService::class);
    $raw = <<<'RAW'
<<<AHQ_TITLE>>>
عنوان جديد
<<<AHQ_HTML>>>
__KEEP__
<<<AHQ_CSS>>>
body{color:red}
<<<AHQ_JS>>>
__KEEP__
<<<AHQ_SUMMARY>>>
لون أحمر
<<<AHQ_END>>>
RAW;

    $parsed = $service->parseBundle($raw);

    expect($parsed['title'])->toBe('عنوان جديد')
        ->and($parsed['html'])->toBe('__KEEP__')
        ->and($parsed['css'])->toContain('color:red')
        ->and($parsed['js'])->toBe('__KEEP__')
        ->and($parsed['summary'])->toContain('أحمر');
});

test('parseBundle still accepts valid JSON', function () {
    $service = app(AiHtmlQuizGenerationService::class);
    $raw = json_encode([
        'title' => 'ت',
        'html' => '<div>x</div>',
        'css' => 'x{}',
        'js' => '1',
        'summary' => 'ok',
    ], JSON_UNESCAPED_UNICODE);

    $parsed = $service->parseBundle($raw);

    expect($parsed['html'])->toBe('<div>x</div>')
        ->and($parsed['css'])->toBe('x{}');
});

test('refine applies KEEP by preserving previous html/js', function () {
    $service = app(AiHtmlQuizGenerationService::class);
    $ref = new ReflectionClass($service);
    $method = $ref->getMethod('applyKeepMarkers');
    $method->setAccessible(true);

    $out = $method->invoke($service, [
        'title' => '__KEEP__',
        'html' => '__KEEP__',
        'css' => 'body{color:red}',
        'js' => '__KEEP__',
        'summary' => 'red',
    ], 'عنوان قديم', '<div>old</div>', 'old{}', 'oldjs');

    expect($out['title'])->toBe('عنوان قديم')
        ->and($out['html'])->toBe('<div>old</div>')
        ->and($out['css'])->toBe('body{color:red}')
        ->and($out['js'])->toBe('oldjs');
});


test('normalize keeps audio self paths for quiz sounds', function () {
    $normalizer = new AiHtmlQuizBundleNormalizer;
    $result = $normalizer->normalize([
        'html' => '<audio src="/sounds/ai-html-quiz/success-01.mp3"></audio><button>تم</button>',
        'css' => '',
        'js' => '',
    ]);

    $doc = (new AiHtmlQuizBundleAssembler($normalizer))->assembleFromParts(
        $result['html'],
        $result['css'],
        $result['js'],
        'أصوات'
    );

    expect($doc)->toContain('/sounds/ai-html-quiz/success-01.mp3')
        ->and($doc)->toContain('media-src');
});
