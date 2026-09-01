<?php

use App\InteractiveLearning\Services\ExperienceSourceExtractionService;
use App\Services\AI\PdfPageImageService;
use App\Services\AI\PdfTextExtractionService;
use Barryvdh\DomPDF\Facade\Pdf;

uses(Tests\TestCase::class);

beforeEach(function () {
    $this->service = new ExperienceSourceExtractionService(
        new PdfTextExtractionService,
        new PdfPageImageService,
    );
});

it('extracts a normal text pdf with a trailing blank page as text, not images', function () {
    if (! class_exists(\Smalot\PdfParser\Parser::class)) {
        $this->markTestSkipped('smalot/pdfparser غير مثبت');
    }

    config(['ai.question_generation_pdf.min_extracted_chars' => 80]);
    config(['ai.question_generation_pdf.min_chars_per_page' => 25]);

    $path = sys_get_temp_dir().'/qlms-test-'.uniqid('', true).'.pdf';

    Pdf::loadHTML(
        '<html><body>'.
        '<p>'.str_repeat('محتوى تجريبي لتوليد الأسئلة من ملف PDF نصي. ', 20).'</p>'.
        '<div style="page-break-before: always;"></div>'.
        '</body></html>'
    )->save($path);

    try {
        $result = $this->service->extractFromStoredPath($path);

        expect($result['kind'])->toBe(ExperienceSourceExtractionService::KIND_TEXT);
        expect($result['text'])->not->toBe('');
        expect($result['images'])->toBe([]);
    } finally {
        @unlink($path);
    }
});
