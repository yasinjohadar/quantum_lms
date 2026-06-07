<?php

namespace App\Support;

class QuestionMarkupFormatter
{
    private const MATH_SEGMENT_PATTERN = '/(\$\$[\s\S]*?\$\$|(?<!\$)\$(?!\$)[^\$\n]+?\$(?!\$)|\\\\\[[\s\S]*?\\\\\]|\\\\\([\s\S]*?\\\\\))/u';

    /** أوامر LaTeX شائعة في خيارات الإجابة (بدون delimiters) */
    private const BARE_LATEX_PATTERN = '/\\\\(?:frac|int|sum|sqrt|sin|cos|tan|cot|sec|csc|ln|log|arctan|arcsin|arccos|lim|infty|to|pm|pi|theta|alpha|beta|gamma|delta|cdot|times|left|right|text|quad|,)|\^{|_{}/u';

    /** متباينات القيمة المطلقة: |x| ≤ 3 (بدون محددات regex) */
    private const ABS_INEQUALITY_PATTERN = '\|[^|\n]+\|\s*(?:≤|≥|<|>|<=|>=|≠|!=)\s*[-−+]?[\d]+(?:[.,][\d]+)?';

    /** فترة عددية: [-3, 3] */
    private const INTERVAL_PATTERN = '\[-?[\d]+(?:[.,][\d]+)?\s*,\s*-?[\d]+(?:[.,][\d]+)?\]';

    public static function containsMath(?string $text): bool
    {
        if ($text === null || trim($text) === '') {
            return false;
        }

        return (bool) preg_match(self::MATH_SEGMENT_PATTERN, $text);
    }

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

        $plain = self::normalizedPlainText($text);
        $plain = preg_replace('/\s*[:،]\s*[.\s]*$/u', '', $plain) ?? $plain;
        $plain = trim($plain);

        if ($plain === '') {
            return 'سؤال';
        }

        return \Illuminate\Support\Str::limit($plain, $limit);
    }

    /**
     * نص عادي موحّد للمقارنة بين title و content (كيانات HTML، مسافات، backticks).
     */
    public static function normalizedPlainText(?string $text): string
    {
        if ($text === null || trim($text) === '') {
            return '';
        }

        $text = self::normalizePseudoMath(self::normalizeStoredText($text));
        $plain = trim(strip_tags($text));
        $plain = preg_replace(self::MATH_SEGMENT_PATTERN, ' ', $plain) ?? $plain;
        $plain = preg_replace('/`[^`\n]+`/u', ' ', $plain) ?? $plain;
        $plain = preg_replace('/\s+/u', ' ', $plain) ?? '';

        return trim(str_replace("\xc2\xa0", ' ', $plain));
    }

    public static function samePlainText(?string $a, ?string $b): bool
    {
        return self::normalizedPlainText($a) === self::normalizedPlainText($b);
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

        // نص عربي مختلط — لا نلفّ الجملة كاملة كـ LaTeX
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            return false;
        }

        return (bool) preg_match(self::BARE_LATEX_PATTERN, $text);
    }

    public static function wrapBareLatex(string $text): string
    {
        $text = trim($text);
        $text = preg_replace('/\\\\-+$/u', '', $text) ?? $text;
        $text = rtrim($text, " \t\\");

        return self::wrapMathSegment('\\('.$text.'\\)');
    }

    private static function wrapMathSegment(string $segment): string
    {
        $segment = trim($segment);

        if (preg_match('/^\$(.+)\$$/us', $segment, $matches)) {
            $segment = '\\('.$matches[1].'\\)';
        }

        return '<span class="question-math-fragment">'.$segment.'</span>';
    }

    public static function format(?string $text): string
    {
        if ($text === null || trim($text) === '') {
            return '';
        }

        $text = self::normalizePseudoMath(trim(self::normalizeStoredText($text)));

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
                $output .= self::wrapMathSegment($segment);
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

        if ($trimmed !== '' && self::isSimpleMathValue($trimmed)) {
            return self::wrapInlineCode($trimmed);
        }

        if ($trimmed !== '' && preg_match('/^'.self::ABS_INEQUALITY_PATTERN.'$/u', $trimmed)) {
            return self::wrapInlineCode($trimmed);
        }

        if ($trimmed !== '' && preg_match('/^[a-zA-Z_$][\w$.]*\([^)]*\)$/u', $trimmed)) {
            return self::wrapInlineCode($trimmed);
        }

        if ($trimmed !== '' && preg_match('/^'.self::INTERVAL_PATTERN.'$/u', $trimmed)) {
            return self::wrapInlineCode($trimmed);
        }

        if ($trimmed !== '' && preg_match('/^\[[^\]]+\]$/u', $trimmed)) {
            return self::wrapInlineCode($trimmed);
        }

        if ($trimmed !== '' && preg_match('/^[a-zA-Z_$][\w$]*$/u', $trimmed) && strlen($trimmed) <= 40) {
            return self::wrapInlineCode($trimmed);
        }

        $codePattern = '/('.self::ABS_INEQUALITY_PATTERN.')|('.self::INTERVAL_PATTERN.')|(\[[^\]]+\])|([a-zA-Z_$][\w$]*(?:\.[a-zA-Z_$][\w$]*)*\([^)]*\))/u';
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
                $output .= self::wrapInlineCode($part);
            } else {
                $output .= self::escapePlainSegment($part);
            }
        }

        return $output;
    }

    private static function isSimpleMathValue(string $text): bool
    {
        return (bool) preg_match('/^[-−+]?[\d]+(?:[.,][\d]+)?$/u', trim($text));
    }

    private static function wrapInlineCode(string $text): string
    {
        return '<code class="question-inline-code">'.e(trim($text)).'</code>';
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
                static fn (array $matches): string => self::wrapInlineCode($matches[1]),
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
                $output .= self::wrapInlineCode($matches[1]);
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

    /**
     * تحويل صيغ pseudo-LaTeX الشائعة في ملفات الاستيراد إلى LaTeX بمحددات $...$.
     */
    public static function normalizePseudoMath(string $text): string
    {
        if ($text === '') {
            return '';
        }

        $trimmed = trim($text);

        $infinityOption = self::normalizeInfinityOptionValue($trimmed);
        if ($infinityOption !== null) {
            return $infinityOption;
        }

        $text = preg_replace_callback(
            '/lim_(?:\{([^}]+)\}|([a-zA-Z]))\s*\\\\to\s*(\+\\\\infty|\+∞|-\\\\infty|-∞|\\\\infty|∞)\s*\\\\frac\{([^}]+)\}\{([^}]+)\}/u',
            static function (array $matches): string {
                $variable = $matches[1] !== '' ? $matches[1] : $matches[2];
                $infinity = self::normalizeInfinityTarget($matches[3]);

                return '$\lim_{'.$variable.' \to '.$infinity.'} \frac{'.$matches[4].'}{'.$matches[5].'}$';
            },
            $text
        ) ?? $text;

        $text = preg_replace_callback(
            '/\(([^()]+)\)\/\(([^()]+)\)\s*lim_(?:\{([^}]+)\}|([a-zA-Z]))\s*\\\\to\s*(\+\\\\infty|\+∞|-\\\\infty|-∞|\\\\infty|∞)/u',
            static function (array $matches): string {
                $numerator = $matches[1];
                $denominator = $matches[2];
                $variable = $matches[3] !== '' ? $matches[3] : $matches[4];
                $infinity = self::normalizeInfinityTarget($matches[5]);

                return '$\lim_{'.$variable.' \to '.$infinity.'} \frac{'.$numerator.'}{'.$denominator.'}$';
            },
            $text
        ) ?? $text;

        $text = preg_replace_callback(
            '/lim_(?:\{([^}]+)\}|([a-zA-Z]))\s*\\\\to\s*(\+\\\\infty|\+∞|-\\\\infty|-∞|\\\\infty|∞)/u',
            static function (array $matches): string {
                $variable = $matches[1] !== '' ? $matches[1] : $matches[2];
                $infinity = self::normalizeInfinityTarget($matches[3]);

                return '$\lim_{'.$variable.' \to '.$infinity.'}$';
            },
            $text
        ) ?? $text;

        $text = preg_replace_callback(
            '/\(([^()]+)\)\/\(([^()]+)\)/u',
            static function (array $matches): string {
                if (
                    preg_match('/[a-zA-Z0-9^\\\\]/u', $matches[1])
                    && preg_match('/[a-zA-Z0-9^\\\\]/u', $matches[2])
                ) {
                    return '$\frac{'.$matches[1].'}{'.$matches[2].'}$';
                }

                return $matches[0];
            },
            $text
        ) ?? $text;

        $text = self::replaceBareFracOutsideMath($text);

        $text = preg_replace_callback(
            '/(?<!\$)\$(?!\$)([^\$\n]+?)(?<!\$)\$(?!\$)/u',
            static function (array $matches): string {
                $content = str_replace('∞', '\\infty', $matches[1]);

                return '$'.$content.'$';
            },
            $text
        ) ?? $text;

        $text = preg_replace_callback(
            '/(?<=حيث\s)([a-zA-Z])(?=\s+عدد)/u',
            static fn (array $matches): string => '$'.$matches[1].'$',
            $text
        ) ?? $text;

        return $text;
    }

    private static function replaceBareFracOutsideMath(string $text): string
    {
        $parts = preg_split(
            self::MATH_SEGMENT_PATTERN,
            $text,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );

        if ($parts === false) {
            return $text;
        }

        $output = '';
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            if (preg_match(self::MATH_SEGMENT_PATTERN, $part)) {
                $output .= $part;
            } else {
                $output .= preg_replace(
                    '/\\\\frac\{([^}]+)\}\{([^}]+)\}/u',
                    '$\\frac{$1}{$2}$',
                    $part
                ) ?? $part;
            }
        }

        return $output;
    }

    private static function normalizeInfinityOptionValue(string $trimmed): ?string
    {
        $compact = preg_replace('/\s+/u', '', $trimmed) ?? $trimmed;

        if (preg_match('/^(?:\+∞|\+\\\\infty|\+?\$\+?(?:∞|\\\\infty)\$|\$\+(?:∞|\\\\infty)\$)$/u', $compact)) {
            return '$+\infty$';
        }

        if (preg_match('/^(?:-∞|-\\\\infty|-?\$-?(?:∞|\\\\infty)\$|\$-?(?:∞|\\\\infty)\$)$/u', $compact)) {
            return '$-\infty$';
        }

        if (preg_match('/^(?:∞|\\\\infty|\$(?:∞|\\\\infty)\$)$/u', $compact)) {
            return '$\infty$';
        }

        return null;
    }

    private static function normalizeInfinityTarget(string $target): string
    {
        $target = trim($target);

        if (preg_match('/^(?:\+\\\\infty|\+∞)$/u', $target)) {
            return '+\infty';
        }

        if (preg_match('/^(?:-\\\\infty|-∞)$/u', $target)) {
            return '-\infty';
        }

        if (preg_match('/^(?:\\\\infty|∞)$/u', $target)) {
            return '\infty';
        }

        return $target;
    }
}
