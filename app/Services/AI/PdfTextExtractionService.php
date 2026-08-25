<?php

namespace App\Services\AI;

use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser;

class PdfTextExtractionService
{
    public function extractFromPath(string $absolutePath): array
    {
        if (! is_readable($absolutePath)) {
            throw new \InvalidArgumentException('ملف PDF غير قابل للقراءة.');
        }

        // تعطيل الاحتفاظ بمحتوى الصور: نحتاج النص فقط، وفكّ الصور المضمّنة
        // هو سبب التعلّق/البطء الشديد مع ملفات PDF الغنية بالرسومات (كالشرائح).
        $config = new Config;
        $config->setRetainImageContent(false);

        $parser = new Parser([], $config);
        $pdf = $parser->parseFile($absolutePath);
        $pages = $pdf->getPages();
        $pageCount = max(1, count($pages));

        $chunks = [];
        foreach ($pages as $page) {
            $chunks[] = (string) $page->getText();
        }

        $text = $this->normalizeText(implode("\n\n", $chunks));

        // نص متطابق على كل الصفحات (كعلامة مائية) لا يمثل محتوى حقيقياً حتى لو
        // كان عدد حروفه كافياً — نحسب عدد الصفحات ذات نص مختلف فعلياً.
        $normalizedChunks = array_filter(array_map(fn ($c) => $this->normalizeText($c), $chunks), fn ($c) => $c !== '');
        $uniquePageTexts = count(array_unique($normalizedChunks));

        return [
            'text' => $text,
            'pageCount' => $pageCount,
            'uniquePageTexts' => $uniquePageTexts,
        ];
    }

    public function isTextSufficient(string $text, int $pageCount): bool
    {
        $minTotal = (int) config('ai.question_generation_pdf.min_extracted_chars', 80);
        $minPerPage = (int) config('ai.question_generation_pdf.min_chars_per_page', 25);

        $length = mb_strlen($text);
        if ($length >= $minTotal) {
            return true;
        }

        $pageCount = max(1, $pageCount);

        return ($length / $pageCount) >= $minPerPage;
    }

    public function truncateForPrompt(string $text): string
    {
        $max = (int) config('ai.question_generation_pdf.max_text_chars_for_prompt', 100000);
        if (mb_strlen($text) <= $max) {
            return $text;
        }

        return mb_substr($text, 0, $max)."\n\n[... تم اقتطاع النص لطول الملف ...]";
    }

    public function normalizeText(string $text): string
    {
        $text = str_replace(["\xC2\xAD", "\u{200B}", "\u{FEFF}"], '', $text);
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }
}
