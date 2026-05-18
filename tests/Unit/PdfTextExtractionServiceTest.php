<?php

use App\Services\AI\PdfTextExtractionService;
use Barryvdh\DomPDF\Facade\Pdf;

uses(Tests\TestCase::class);

beforeEach(function () {
    $this->service = new PdfTextExtractionService;
});

it('normalizes whitespace in extracted text', function () {
    $text = $this->service->normalizeText("  hello   \n\n\n  world  ");

    expect($text)->toBe('hello world');
});

it('detects sufficient text by total length', function () {
    config(['ai.question_generation_pdf.min_extracted_chars' => 80]);
    config(['ai.question_generation_pdf.min_chars_per_page' => 25]);

    $text = str_repeat('أ', 100);

    expect($this->service->isTextSufficient($text, 1))->toBeTrue();
});

it('detects insufficient scanned-like text', function () {
    config(['ai.question_generation_pdf.min_extracted_chars' => 80]);
    config(['ai.question_generation_pdf.min_chars_per_page' => 25]);

    expect($this->service->isTextSufficient('abc', 5))->toBeFalse();
});

it('extracts text from a generated pdf file', function () {
    if (! class_exists(\Smalot\PdfParser\Parser::class)) {
        $this->markTestSkipped('smalot/pdfparser غير مثبت');
    }

    $path = sys_get_temp_dir().'/qlms-test-'.uniqid('', true).'.pdf';

    Pdf::loadHTML(
        '<html><body><p>'.
        str_repeat('محتوى تجريبي لتوليد الأسئلة من ملف PDF نصي. ', 20).
        '</p></body></html>'
    )->save($path);

    try {
        $result = $this->service->extractFromPath($path);

        expect($result['pageCount'])->toBeGreaterThan(0);
        expect($result['text'])->not->toBe('');
        expect($this->service->isTextSufficient($result['text'], $result['pageCount']))->toBeTrue();
    } finally {
        @unlink($path);
    }
});

it('truncates long text for prompts', function () {
    config(['ai.question_generation_pdf.max_text_chars_for_prompt' => 100]);

    $long = str_repeat('x', 200);
    $truncated = $this->service->truncateForPrompt($long);

    expect(mb_strlen($truncated))->toBeLessThan(200);
    expect($truncated)->toContain('اقتطاع');
});
