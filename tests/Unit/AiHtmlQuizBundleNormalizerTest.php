<?php

use App\AiHtmlQuiz\Services\AiHtmlQuizBundleAssembler;
use App\AiHtmlQuiz\Services\AiHtmlQuizBundleNormalizer;

test('normalize injects result bridge when postMessage is missing', function () {
    $normalizer = new AiHtmlQuizBundleNormalizer;

    $result = $normalizer->normalize([
        'title' => 'اختبار تجريبي',
        'html' => '<div id="app">مرحبا</div>',
        'css' => 'body{background:#fff}',
        'js' => 'console.log("hi");',
        'summary' => 'ملخص',
    ]);

    expect($result['title'])->toBe('اختبار تجريبي')
        ->and($result['js'])->toContain('ile-html-quiz-result')
        ->and($result['js'])->toContain('postMessage')
        ->and($result['html'])->toContain('مرحبا');
});

test('normalize rejects truncated javascript', function () {
    $normalizer = new AiHtmlQuizBundleNormalizer;

    $normalizer->normalize([
        'html' => '<button onclick="startQuiz()">ابدأ</button>',
        'css' => '',
        'js' => "function updateProgress() {\n  const pct = (currentIndex / QUESTIONS.length) *",
    ]);
})->throws(InvalidArgumentException::class);

test('normalize rejects missing onclick handlers', function () {
    $normalizer = new AiHtmlQuizBundleNormalizer;

    $normalizer->normalize([
        'html' => '<button onclick="startQuiz()">ابدأ</button>',
        'css' => '',
        'js' => 'console.log(1); window.parent.postMessage({type:"ile-html-quiz-result",payload:{}},"*");',
    ]);
})->throws(InvalidArgumentException::class);

test('normalize keeps existing postMessage and does not duplicate bridge needlessly', function () {
    $normalizer = new AiHtmlQuizBundleNormalizer;
    $js = 'window.parent.postMessage({type:"ile-html-quiz-result",payload:{score:1,total:1,percentage:100,answers:[],durationSeconds:10}},"*");';

    $result = $normalizer->normalize([
        'html' => '<button>ابدأ</button>',
        'css' => '',
        'js' => $js,
    ]);

    expect(substr_count($result['js'], 'ile-html-quiz-result'))->toBe(1)
        ->and(trim($result['js']))->toBe(trim($js));
});

test('sanitizeHtml strips external script and link tags', function () {
    $normalizer = new AiHtmlQuizBundleNormalizer;
    $html = '<div>ok</div><script src="https://cdn.example.com/x.js"></script><link href="//evil.com/a.css" rel="stylesheet"><script src="/sounds/ok.js"></script>';

    $clean = $normalizer->sanitizeHtml($html);

    expect($clean)->toContain('<div>ok</div>')
        ->and($clean)->not->toContain('cdn.example.com')
        ->and($clean)->not->toContain('evil.com')
        ->and($clean)->toContain('/sounds/ok.js');
});

test('sanitizeCss strips remote @import', function () {
    $normalizer = new AiHtmlQuizBundleNormalizer;
    $css = "@import url('https://fonts.example.com/x.css');\nbody{color:red}";

    expect($normalizer->sanitizeCss($css))->toBe('body{color:red}');
});

test('hasDisallowedExternalScripts detects https script src', function () {
    $normalizer = new AiHtmlQuizBundleNormalizer;

    expect($normalizer->hasDisallowedExternalScripts('<script src="https://x.com/a.js"></script>'))->toBeTrue()
        ->and($normalizer->hasDisallowedExternalScripts('<div></div>', 'import("https://x.com/m.js")'))->toBeTrue()
        ->and($normalizer->hasDisallowedExternalScripts('<script src="/local.js"></script>'))->toBeFalse();
});

test('normalize rejects empty bundle', function () {
    $normalizer = new AiHtmlQuizBundleNormalizer;

    $normalizer->normalize(['html' => '  ', 'css' => '', 'js' => '']);
})->throws(InvalidArgumentException::class);

test('assembler builds document with CSP and embedded parts', function () {
    $assembler = new AiHtmlQuizBundleAssembler(new AiHtmlQuizBundleNormalizer);

    $doc = $assembler->assembleFromParts(
        '<h1>اختبر</h1>',
        'h1{color:teal}',
        'window.parent.postMessage({type:"ile-html-quiz-result",payload:{score:0,total:1,percentage:0,answers:[],durationSeconds:1}},"*");',
        'عنوان'
    );

    expect($doc)->toContain('Content-Security-Policy')
        ->and($doc)->toContain("default-src 'none'")
        ->and($doc)->toContain('fonts.googleapis.com')
        ->and($doc)->toContain('family=Alexandria')
        ->and($doc)->toContain('<h1>اختبر</h1>')
        ->and($doc)->toContain('h1{color:teal}')
        ->and($doc)->toContain('ile-html-quiz-result')
        ->and($doc)->toContain('lang="ar"')
        ->and($doc)->toContain('dir="rtl"');
});
