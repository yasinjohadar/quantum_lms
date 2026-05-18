<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\PdfToImage\Enums\OutputFormat;
use Spatie\PdfToImage\Pdf;

class PdfPageImageService
{
    public function isAvailable(): bool
    {
        if (! extension_loaded('imagick')) {
            return false;
        }

        return $this->ghostscriptIsAvailable();
    }

    public function ghostscriptIsAvailable(): bool
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $candidates = ['gswin64c', 'gswin32c', 'gs'];
            foreach ($candidates as $cmd) {
                $output = [];
                $code = 1;
                @exec($cmd.' --version 2>&1', $output, $code);
                if ($code === 0) {
                    return true;
                }
            }

            return false;
        }

        $output = [];
        $code = 1;
        @exec('gs --version 2>&1', $output, $code);

        return $code === 0;
    }

    /**
     * @return array<int, array{mime: string, binary: string}>
     */
    public function renderPages(string $pdfPath, ?int $maxPages = null): array
    {
        if (! $this->isAvailable()) {
            throw new \RuntimeException(
                'تحويل PDF الممسوح إلى صور يتطلب تفعيل PHP Imagick وتثبيت Ghostscript على الخادم. '
                .'يمكنك رفع صور الصفحات بدلاً من ذلك، أو استخدام ملف PDF نصي.'
            );
        }

        $maxPages = $maxPages ?? (int) config('ai.question_generation_pdf.max_pages_vision', 10);
        $resolution = (int) config('ai.question_generation_pdf.page_render_resolution', 150);

        $pdf = new Pdf($pdfPath);
        $totalPages = max(1, $pdf->pageCount());
        $pagesToRender = min($maxPages, $totalPages);
        $pageNumbers = range(1, $pagesToRender);

        $tempDir = storage_path('app/temp/pdf_pages/'.Str::uuid());
        File::ensureDirectoryExists($tempDir);

        $images = [];
        try {
            $savedPaths = $pdf
                ->resolution($resolution)
                ->format(OutputFormat::Jpg)
                ->selectPages(...$pageNumbers)
                ->save($tempDir);

            foreach ($savedPaths as $savedPath) {
                if (! is_readable($savedPath)) {
                    continue;
                }
                $binary = file_get_contents($savedPath);
                if ($binary !== false && $binary !== '') {
                    $images[] = [
                        'mime' => 'image/jpeg',
                        'binary' => $binary,
                    ];
                }
            }
        } finally {
            File::deleteDirectory($tempDir);
        }

        if ($images === []) {
            throw new \RuntimeException('تعذر تحويل صفحات PDF إلى صور. تحقق من تثبيت Ghostscript.');
        }

        return $images;
    }
}
