<?php

namespace App\Support;

class QuestionMarkupFormatter
{
    private const MATH_SEGMENT_PATTERN = '/(\$\$[\s\S]*?\$\$|\\\\\[[\s\S]*?\\\\\]|\\\\\([\s\S]*?\\\\\))/u';

    /** أوامر LaTeX شائعة في خيارات الإجابة (بدون delimiters) */
    private const BARE_LATEX_PATTERN = '/\\\\(?:frac|int|sum|sqrt|sin|cos|tan|cot|sec|csc|ln|log|arctan|arcsin|arccos|pi|theta|alpha|beta|gamma|delta|cdot|times|left|right|text|quad|,)|\^{|_{}/u';

    public static function containsMath(?string $text): bool
    {
        if ($text === null || trim($text) === '') {
            return false;
        }

        return (bool) preg_match(self::MATH_SEGMENT_PATTERN, $text);
    }

    /**
     * عنوان مختصر للترويسة: عربي فقط بدون LaTeX خام في شريط الصفحة.
     */
    /**
     * فكّ كيانات HTML المخزّنة كنص (مثل &quot;) بما فيها التشفير المزدوج.
     */
    public static function normalizeStoredText(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        $decoded = $text;
        for ($i = 0; $i < 3; $i++) {
            $next = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($next === $decoded) {
                break;
            }
            $decoded = $next;
        }

        return $decoded;
    }

    public static function plainHeading(?string $text, int $limit = 100): string
    {
        if ($text === null || trim($text) === '') {
            return '';
        }

        $text = self::normalizeStoredText($text);
        $plain = trim(strip_tags($text));
        $plain = preg_replace(self::MATH_SEGMENT_PATTERN, ' ', $plain) ?? $plain;
        $plain = preg_replace('/`[^`\n]+`/u', ' ', $plain) ?? $plain;
        $plain = preg_replace('/\s+/u', ' ', trim($plain)) ?? '';
        $plain = preg_replace('/\s*[:،]\s*[.\s]*$/u', '', $plain) ?? $plain;
        $plain = trim($plain);

        if ($plain === '') {
            return 'سؤال';
        }

        return \Illuminate\Support\Str::limit($plain, $limit);
    }

    public static function looksLikeBareLatex(string $text): bool
    {
        if (self::containsMath($text)) {
            return false;
        }

        $trimmed = trim($text);
        if ($trimmed === '') {
            return false;
        }

        // كود برمجة بسيط: push() — ليس LaTeX
        if (! preg_match('/\\\\/', $trimmed) && preg_match('/^[a-zA-Z_$][\w$.]*\([^)]*\)$/u', $trimmed)) {
            return false;
        }

        if (! preg_match('/\\\\/', $trimmed) && preg_match('/^[a-zA-Z_$][\w$]*$/u', $trimmed) && strlen($trimmed) <= 40) {
            return false;
        }

        return (bool) preg_match(self::BARE_LATEX_PATTERN, $text);
    }

    public static function wrapBareLatex(string $text): string
    {
        $text = trim($text);
        $text = preg_replace('/\\\\-+$/u', '', $text) ?? $text;
        $text = rtrim($text, " \t\\");

        return '<span class="question-math-fragment">\('.$text.'\)</span>';
    }

    public static function format(?string $text): string
    {
        if ($text === null || trim($text) === '') {
            return '';
        }

        $text = trim(self::normalizeStoredText($text));

        if (str_contains($text, 'question-inline-code')) {
            return $text;
        }

        if (self::looksLikeBareLatex($text)) {
            return self::wrapBareLatex($text);
        }

        if (! self::containsMath($text)) {
            return self::formatWithoutMath($text);
        }

        // لا نلفّ النص العربي بالكامل — فقط مقاطع LaTeX تُترك خاماً لـ KaTeX

        $segments = preg_split(self::MATH_SEGMENT_PATTERN, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($segments === false) {
            return self::formatWithoutMath($text);
        }

        $output = '';
        foreach ($segments as $segment) {
            if ($segment === '') {
                continue;
            }
            if (preg_match(self::MATH_SEGMENT_PATTERN, $segment)) {
                $output .= '<span class="question-math-fragment">'.$segment.'</span>';
            } else {
                $output .= self::formatWithoutMath($segment);
            }
        }

        return $output;
    }

    private static function formatWithoutMath(string $text): string
    {
        $text = self::normalizeStoredText($text);

        if (self::looksLikeBareLatex($text)) {
            return self::wrapBareLatex($text);
        }

        $text = self::replaceCodeBlocks($text);
        $text = self::replaceInlineCode($text);
        $text = self::autoWrapCodePatterns($text);

        if ($text === strip_tags($text)) {
            return nl2br($text, false);
        }

        return $text;
    }

    /**
     * تمييز أكواد البرمجة (push(), [1,2,3]) حتى بدون backticks — مفيد في خيارات الإجابة.
     */
    private static function autoWrapCodePatterns(string $text): string
    {
        if (trim($text) === '') {
            return '';
        }

        $protectedPattern = '/(<pre class="question-code-block">[\s\S]*?<\/pre>)|(<code class="question-inline-code">[\s\S]*?<\/code>)/u';
        $parts = preg_split($protectedPattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        if ($parts === false) {
            return self::wrapPlainCodeSegments($text);
        }

        $output = '';
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            if (preg_match($protectedPattern, $part)) {
                $output .= $part;
            } elseif ($part !== strip_tags($part)) {
                $output .= $part;
            } else {
                $output .= self::wrapPlainCodeSegments($part);
            }
        }

        return $output;
    }

    private static function wrapPlainCodeSegments(string $text): string
    {
        $trimmed = trim($text);

        if ($trimmed !== '' && preg_match('/^[a-zA-Z_$][\w$.]*\([^)]*\)$/u', $trimmed)) {
            return '<code class="question-inline-code">'.e($trimmed).'</code>';
        }

        if ($trimmed !== '' && preg_match('/^\[[^\]]+\]$/u', $trimmed)) {
            return '<code class="question-inline-code">'.e($trimmed).'</code>';
        }

        if ($trimmed !== '' && preg_match('/^[a-zA-Z_$][\w$]*$/u', $trimmed) && strlen($trimmed) <= 40) {
            return '<code class="question-inline-code">'.e($trimmed).'</code>';
        }

        $codePattern = '/(\[[^\]]+\])|([a-zA-Z_$][\w$]*(?:\.[a-zA-Z_$][\w$]*)*\([^)]*\))/u';
        $parts = preg_split($codePattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        if ($parts === false) {
            return self::escapePlainSegment($text);
        }

        $output = '';
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            if (preg_match($codePattern, $part)) {
                $output .= '<code class="question-inline-code">'.e($part).'</code>';
            } else {
                $output .= self::escapePlainSegment($part);
            }
        }

        return $output;
    }

    private static function replaceCodeBlocks(string $text): string
    {
        return preg_replace_callback(
            '/```([\s\S]*?)```/u',
            static fn (array $matches): string => '<pre class="question-code-block"><code>'
                .e(trim($matches[1]))
                .'</code></pre>',
            $text
        ) ?? $text;
    }

    private static function replaceInlineCode(string $text): string
    {
        if ($text !== strip_tags($text)) {
            return preg_replace_callback(
                '/`([^`\n]+)`/u',
                static fn (array $matches): string => '<code class="question-inline-code">'
                    .e($matches[1])
                    .'</code>',
                $text
            ) ?? $text;
        }

        $parts = preg_split('/(`[^`\n]+`)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return $text;
        }

        $output = '';
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            if (preg_match('/^`([^`\n]+)`$/u', $part, $matches)) {
                $output .= '<code class="question-inline-code">'.e($matches[1]).'</code>';
            } else {
                $output .= $part;
            }
        }

        return $output;
    }

    private static function escapePlainSegment(string $text): string
    {
        return e(self::normalizeStoredText($text));
    }
}
