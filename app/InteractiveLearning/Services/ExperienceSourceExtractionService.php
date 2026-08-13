<?php

namespace App\InteractiveLearning\Services;

use App\Services\AI\PdfPageImageService;
use App\Services\AI\PdfTextExtractionService;
use Illuminate\Http\UploadedFile;
use RuntimeException;

/**
 * استخراج محتوى مصدر (PDF أو صورة) لتوليد أسئلة تجربة تفاعلية منه.
 *
 * غلاف رقيق حول خدمات الذكاء الاصطناعي الموجودة أصلاً — لا يكرّر منطقها:
 * - App\Services\AI\PdfTextExtractionService : استخراج نص PDF وقياس كفايته
 * - App\Services\AI\PdfPageImageService     : تحويل صفحات PDF الممسوح إلى صور
 *
 * يتبع نفس سلوك AIQuestionGenerationService::processPdfGeneration() المُجرَّب:
 * النص أولاً، وإن كان غير كافٍ (ملف ممسوح ضوئياً) فالصور مع موديل رؤية.
 */
class ExperienceSourceExtractionService
{
    public const KIND_TEXT = 'text';

    public const KIND_IMAGES = 'images';

    /** @var list<string> */
    public const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public function __construct(
        protected PdfTextExtractionService $pdfTextExtraction,
        protected PdfPageImageService $pdfPageImage,
    ) {}

    /**
     * @return array{kind: string, text: string, images: array<int, array{mime: string, binary: string}>, pageCount: int, charCount: int, notes: string}
     */
    public function extract(UploadedFile $file): array
    {
        if ($this->isImage($file)) {
            return $this->extractFromImage($file);
        }

        if ($this->isPdf($file)) {
            return $this->extractFromPdf($file);
        }

        throw new RuntimeException('نوع الملف غير مدعوم. يُقبل ملف PDF أو صورة (JPEG, PNG, WebP, GIF) فقط.');
    }

    /**
     * نفس الاستخراج لكن من ملف محفوظ مؤقتاً على القرص (مسار الصور بين الطلبين).
     *
     * @return array{kind: string, text: string, images: array<int, array{mime: string, binary: string}>, pageCount: int, charCount: int, notes: string}
     */
    public function extractFromStoredPath(string $absolutePath): array
    {
        if (! is_readable($absolutePath)) {
            throw new RuntimeException('الملف المؤقّت غير قابل للقراءة. أعد تحليل الملف من جديد.');
        }

        $mime = strtolower((string) (@mime_content_type($absolutePath) ?: ''));

        if (str_starts_with($mime, 'image/')) {
            $binary = @file_get_contents($absolutePath);
            if ($binary === false || $binary === '') {
                throw new RuntimeException('تعذر قراءة الصورة المؤقّتة.');
            }

            return [
                'kind' => self::KIND_IMAGES,
                'text' => '',
                'images' => [['mime' => $mime, 'binary' => $binary]],
                'pageCount' => 1,
                'charCount' => 0,
                'notes' => '',
            ];
        }

        if ($mime === 'application/pdf' || strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION)) === 'pdf') {
            return $this->extractFromPdfPath($absolutePath);
        }

        throw new RuntimeException('نوع الملف المؤقّت غير مدعوم.');
    }

    public function isImage(UploadedFile $file): bool
    {
        $mime = strtolower((string) $file->getMimeType());
        $ext = strtolower((string) $file->getClientOriginalExtension());

        return str_starts_with($mime, 'image/') || in_array($ext, self::IMAGE_EXTENSIONS, true);
    }

    public function isPdf(UploadedFile $file): bool
    {
        $mime = strtolower((string) $file->getMimeType());
        $ext = strtolower((string) $file->getClientOriginalExtension());

        return $mime === 'application/pdf' || $ext === 'pdf';
    }

    /**
     * الصورة تُحلّل بصرياً — لا استخراج نص محلياً (لا توجد مكتبة OCR في المشروع).
     *
     * @return array{kind: string, text: string, images: array<int, array{mime: string, binary: string}>, pageCount: int, charCount: int, notes: string}
     */
    protected function extractFromImage(UploadedFile $file): array
    {
        $binary = @file_get_contents($file->getRealPath());
        if ($binary === false || $binary === '') {
            throw new RuntimeException('تعذر قراءة الصورة المرفوعة.');
        }

        return [
            'kind' => self::KIND_IMAGES,
            'text' => '',
            'images' => [[
                'mime' => (string) ($file->getMimeType() ?: 'image/jpeg'),
                'binary' => $binary,
            ]],
            'pageCount' => 1,
            'charCount' => 0,
            'notes' => 'سيتم تحليل الصورة بصرياً عبر موديل يدعم الرؤية.',
        ];
    }

    /**
     * @return array{kind: string, text: string, images: array<int, array{mime: string, binary: string}>, pageCount: int, charCount: int, notes: string}
     */
    protected function extractFromPdf(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        if (! $path || ! is_readable($path)) {
            throw new RuntimeException('تعذر قراءة ملف PDF المرفوع.');
        }

        return $this->extractFromPdfPath($path);
    }

    /**
     * @return array{kind: string, text: string, images: array<int, array{mime: string, binary: string}>, pageCount: int, charCount: int, notes: string}
     */
    protected function extractFromPdfPath(string $path): array
    {
        $extracted = $this->pdfTextExtraction->extractFromPath($path);
        $text = (string) $extracted['text'];
        $pageCount = (int) $extracted['pageCount'];

        // المسار الأساسي: ملف PDF فيه طبقة نص حقيقية
        if ($this->pdfTextExtraction->isTextSufficient($text, $pageCount)) {
            $truncated = $this->pdfTextExtraction->truncateForPrompt($text);
            $wasTruncated = mb_strlen($truncated) < mb_strlen($text);

            return [
                'kind' => self::KIND_TEXT,
                'text' => $truncated,
                'images' => [],
                'pageCount' => $pageCount,
                'charCount' => mb_strlen($truncated),
                'notes' => $wasTruncated ? 'تم اقتطاع النص لطول الملف.' : '',
            ];
        }

        // ملف ممسوح ضوئياً: لا طبقة نص — يُحوَّل لصور ويُحلّل بصرياً
        if (! $this->pdfPageImage->isAvailable()) {
            throw new RuntimeException(
                'ملف PDF يبدو ممسوحاً ضوئياً (لا يحتوي نصاً قابلاً للقراءة). '
                .'تحويل صفحاته إلى صور يتطلب تفعيل PHP Imagick وتثبيت Ghostscript على الخادم. '
                .'الحلول: ارفع صور الصفحات مباشرة، أو استخدم ملف PDF نصياً، أو فعّل Imagick + Ghostscript.'
            );
        }

        $maxPages = (int) config('ai.question_generation_pdf.max_pages_vision', 10);
        $images = $this->pdfPageImage->renderPages($path, $maxPages);

        $notes = 'ملف PDF ممسوح ضوئياً — سيتم تحليل صور الصفحات بصرياً.';
        if ($pageCount > count($images)) {
            $notes .= ' (سيتم تحليل أول '.count($images).' صفحة من '.$pageCount.')';
        }

        return [
            'kind' => self::KIND_IMAGES,
            'text' => '',
            'images' => $images,
            'pageCount' => $pageCount,
            'charCount' => 0,
            'notes' => $notes,
        ];
    }
}
