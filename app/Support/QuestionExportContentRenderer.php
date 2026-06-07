<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class QuestionExportContentRenderer
{
    public static function toPlainText(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $text = QuestionMarkupFormatter::normalizePseudoMath(
            QuestionMarkupFormatter::normalizeStoredText($html)
        );

        $text = preg_replace('/<span class="question-math-fragment">(.*?)<\/span>/us', '$1', $text) ?? $text;
        $text = preg_replace('/\\\\\(([^)]+)\\\\\)/u', '$1', $text) ?? $text;
        $text = preg_replace('/\$(.+?)\$/u', '$1', $text) ?? $text;
        $text = preg_replace('/`([^`]+)`/u', '$1', $text) ?? $text;
        $text = str_replace(
            ['\\infty', '\\to', '\\frac', '\\lim', '\\sin', '\\cos', '\\tan'],
            ['∞', '→', '', 'lim', 'sin', 'cos', 'tan'],
            $text
        );
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim(str_replace("\xc2\xa0", ' ', $text));
    }

    public static function questionStemText(\App\Models\Question $question): string
    {
        $title = self::toPlainText($question->title);
        if (! question_content_differs_from_title($question->title, $question->content)) {
            return $title;
        }

        $content = self::toPlainText($question->content);

        return trim($title."\n".$content);
    }

    public static function localImagePath(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $path = trim($path);
        if (preg_match('#^https?://[^/]+/storage/(.+)$#i', $path, $matches)) {
            $path = $matches[1];
        }

        $normalized = ltrim(str_replace('\\', '/', $path), '/');

        if (str_starts_with($normalized, 'storage/')) {
            $normalized = substr($normalized, strlen('storage/'));
        }

        $disk = config('storage.fallback_disk', 'public');

        try {
            if (Storage::disk($disk)->exists($normalized)) {
                return Storage::disk($disk)->path($normalized);
            }
        } catch (\Throwable) {
            // fallback below
        }

        $publicPath = public_path('storage/'.$normalized);
        if (is_file($publicPath)) {
            return $publicPath;
        }

        return null;
    }
}
