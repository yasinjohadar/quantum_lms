<?php

namespace App\Support;

class QuestionMarkupFormatter
{
    private const MATH_SEGMENT_PATTERN = '/(\$\$[\s\S]*?\$\$|(?<!\$)\$(?!\$)[^\$\n]+?\$(?!\$)|\\\\\[[\s\S]*?\\\\\]|\\\\\([\s\S]*?\\\\\))/u';

    /** أوامر LaTeX شائعة في خيارات الإجابة (بدون delimiters) */
    private const BARE_LATEX_PATTERN = '/\\\\(?:frac|int|sum|prod|sqrt|sin|cos|tan|cot|sec|csc|ln|log|arctan|arcsin|arccos|lim|infty|to|pm|pi|theta|alpha|beta|gamma|delta|cdot|times|left|right|text|quad|,)|_\{|\^\{/u';

    /** أسطر خطوات تبدأ بعلامة يساوي */
    private const EQUATION_STEP_PATTERN = '/^=\s/u';

    /** كسور بصيغة (a)/(b) أو [a] / [b] */
    private const SLASH_FRACTION_PATTERN = '/(?:\([^)]+\)|\[[^\]]+\])\s*\/\s*(?:\([^)]+\)|\[[^\]]+\])/u';

    /** pseudo-LaTeX بدون backslash */
    private const PSEUDO_MATH_PATTERN = '/\b(?:sum|prod|int|lim)_\{|(?:\([^)]+\)|[a-zA-Z0-9]+)!|[a-zA-Z]\s*[*×]\s*[a-zA-Z]/u';

    /** متباينات القيمة المطلقة: |x| ≤ 3 أو |x| \leq 3 */
    private const ABS_INEQUALITY_PATTERN = '\|[^|\n]+\|\s*(?:≤|≥|<|>|<=|>=|≠|!=|\\\\leq|\\\\geq|\\\\neq)\s*[-−+]?[\d]+(?:[.,][\d]+)?';

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
     * تطبيع نص السؤال قبل التخزين (pseudo-LaTeX → LaTeX، backticks رياضية → $...$).
     */
    public static function normalizeForStorage(?string $text): string
    {
        if ($text === null || trim($text) === '') {
            return '';
        }

        $text = trim(self::normalizeStoredText($text));
        $text = self::softenImportedHtml($text);
        $text = self::convertMathBackticks($text);
        $text = self::convertBraceWrappedMathOutsideDelimiters($text);
        $text = self::convertUnicodeMathOutsideDelimiters($text);
        $text = self::normalizePseudoMath($text);
        $text = self::extractInlinePseudoMathInArabicText($text);
        $text = self::wrapCommonFunctionDefinitionsInArabicText($text);
        $text = self::repairBrokenMathDelimiters($text);

        return $text;
    }

    /**
     * إصلاح فواصل $ المكسورة الناتجة عن لفّ f(x)= و \frac بشكل منفصل:
     * "$f(x) =$$\frac{a}{b}$" → "$f(x) = \frac{a}{b}$"
     * "$$f(x) = \frac{a}{b}$" → "$f(x) = \frac{a}{b}$"
     */
    private static function repairBrokenMathDelimiters(string $text): string
    {
        if ($text === '' || ! str_contains($text, '$')) {
            return $text;
        }

        // $f(x) =$$\frac...$ أو $f(x) = $$\frac...$
        $text = preg_replace_callback(
            '/\$([a-zA-Z]\s*\(\s*[a-zA-Z0-9]+\s*\)\s*=)\s*\$\$/u',
            static fn (array $m): string => '$'.$m[1].' ',
            $text
        ) ?? $text;

        // $$f(x) = \frac{...}$ (فُتح بـ $$ وأُغلق بـ $)
        $text = preg_replace_callback(
            '/\$\$([a-zA-Z]\s*\(\s*[a-zA-Z0-9]+\s*\)\s*=\s*\\\\frac\{[^}]+\}\{[^}]+\})\$/u',
            static fn (array $m): string => '$'.$m[1].'$',
            $text
        ) ?? $text;

        // أي $$...$ غير متوازن يحتوي أوامر LaTeX
        $text = preg_replace_callback(
            '/\$\$([^\$\n]+)\$/u',
            static function (array $m): string {
                $inner = trim($m[1]);
                if ($inner !== '' && preg_match('/\\\\(?:frac|sqrt|lim|int|sum|infty)|[a-zA-Z]\s*\(/u', $inner)) {
                    return '$'.$inner.'$';
                }

                return $m[0];
            },
            $text
        ) ?? $text;

        // $f(x)$$\frac → $f(x) \frac
        $text = preg_replace_callback(
            '/\$([a-zA-Z]\s*\(\s*[a-zA-Z0-9]+\s*\))\s*\$\$/u',
            static fn (array $m): string => '$'.$m[1].' ',
            $text
        ) ?? $text;

        return $text;
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

    /**
     * تبسيط HTML المستورد (NotebookLM/Excel) إلى نص مع الحفاظ على فواصل الأسطر،
     * دون لمس محتوى TinyMCE الغني إن وُجدت وسوم هيكلية مفيدة مع صور.
     */
    private static function softenImportedHtml(string $text): string
    {
        if (! preg_match('/<(?:p|div|br|span|em|strong|b|i)\b/i', $text)) {
            return $text;
        }

        // إن وُجدت صور أو جداول أو روابط ملفات نُبقي HTML كما هو
        if (preg_match('/<(?:img|table|video|audio|iframe|a)\b/i', $text)) {
            return $text;
        }

        $text = preg_replace('/<\s*br\s*\/?\s*>/iu', "\n", $text) ?? $text;
        $text = preg_replace('/<\/\s*p\s*>/iu', "\n", $text) ?? $text;
        $text = preg_replace('/<\/\s*div\s*>/iu', "\n", $text) ?? $text;
        $text = strip_tags($text);
        $text = preg_replace("/[ \t]+\n/u", "\n", $text) ?? $text;
        $text = preg_replace("/\n{3,}/u", "\n\n", $text) ?? $text;

        return trim($text);
    }

    /**
     * تحويل رموز يونيكود الرياضية الشائعة (من NotebookLM وغيرها) إلى LaTeX خارج المحددات.
     */
    public static function convertUnicodeMathOutsideDelimiters(string $text): string
    {
        if ($text === '') {
            return '';
        }

        $parts = preg_split(self::MATH_SEGMENT_PATTERN, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return self::convertUnicodeMathInPlainSegment($text);
        }

        $output = '';
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            if (preg_match(self::MATH_SEGMENT_PATTERN, $part)) {
                $inner = preg_replace('/^\$\$?|\$\$?$/u', '', $part) ?? $part;
                $inner = preg_replace('/^\\\\[\[\(]|\\\\[\]\)]$/u', '', $inner) ?? $inner;
                $convertedInner = self::convertUnicodeMathInPlainSegment($inner);
                if (str_starts_with($part, '$$')) {
                    $output .= '$$'.$convertedInner.'$$';
                } elseif (str_starts_with($part, '$')) {
                    $output .= '$'.$convertedInner.'$';
                } elseif (str_starts_with($part, '\\[')) {
                    $output .= '\\['.$convertedInner.'\\]';
                } else {
                    $output .= '\\('.$convertedInner.'\\)';
                }
            } else {
                $output .= self::convertUnicodeMathInPlainSegment($part);
            }
        }

        return $output;
    }

    private static function convertUnicodeMathInPlainSegment(string $text): string
    {
        static $symbolMap = [
            '√' => '\\sqrt',
            '∛' => '\\sqrt[3]',
            '∜' => '\\sqrt[4]',
            '∞' => '\\infty',
            '±' => '\\pm',
            '∓' => '\\mp',
            '×' => '\\times',
            '·' => '\\cdot',
            '⋅' => '\\cdot',
            '÷' => '\\div',
            '≤' => '\\leq',
            '≥' => '\\geq',
            '≠' => '\\neq',
            '≈' => '\\approx',
            '∈' => '\\in',
            '∉' => '\\notin',
            '⊂' => '\\subset',
            '⊆' => '\\subseteq',
            '∪' => '\\cup',
            '∩' => '\\cap',
            '→' => '\\to',
            '⇒' => '\\Rightarrow',
            '⇔' => '\\Leftrightarrow',
            '∂' => '\\partial',
            '∇' => '\\nabla',
            '∫' => '\\int',
            '∑' => '\\sum',
            '∏' => '\\prod',
            'π' => '\\pi',
            'θ' => '\\theta',
            'α' => '\\alpha',
            'β' => '\\beta',
            'γ' => '\\gamma',
            'δ' => '\\delta',
            'λ' => '\\lambda',
            'μ' => '\\mu',
            'σ' => '\\sigma',
            'φ' => '\\varphi',
            'ω' => '\\omega',
            'ℝ' => '\\mathbb{R}',
            'ℕ' => '\\mathbb{N}',
            'ℤ' => '\\mathbb{Z}',
            'ℚ' => '\\mathbb{Q}',
            'ℂ' => '\\mathbb{C}',
            'ℓ' => '\\ell',
            '′' => "'",
            '″' => "''",
            '−' => '-',
        ];

        static $superscripts = [
            '⁰' => '0', '¹' => '1', '²' => '2', '³' => '3', '⁴' => '4',
            '⁵' => '5', '⁶' => '6', '⁷' => '7', '⁸' => '8', '⁹' => '9',
            '⁺' => '+', '⁻' => '-', 'ⁿ' => 'n', 'ⁱ' => 'i',
        ];

        static $subscripts = [
            '₀' => '0', '₁' => '1', '₂' => '2', '₃' => '3', '₄' => '4',
            '₅' => '5', '₆' => '6', '₇' => '7', '₈' => '8', '₉' => '9',
            '₊' => '+', '₋' => '-', 'ₙ' => 'n', 'ᵢ' => 'i', 'ₓ' => 'x',
            'ₚ' => 'p', 'ₖ' => 'k', 'ₘ' => 'm', 'ₜ' => 't', 'ₛ' => 's',
            'ᵣ' => 'r', 'ᵤ' => 'u', 'ᵥ' => 'v', 'ₐ' => 'a', 'ₑ' => 'e',
        ];

        // إصلاحات شائعة من تصدير NotebookLM CSV (يونيكود مشوّه)
        $text = preg_replace(
            '/\(([A-Za-z])ₙ\)ₙ\s*gₑ\s*₀/u',
            '($1_n)_{n \\ge 0}',
            $text
        ) ?? $text;
        $text = preg_replace(
            '/([A-Za-z])ₙ\+₁/u',
            '$1_{n+1}',
            $text
        ) ?? $text;
        $text = preg_replace(
            '/([A-Za-z])ₙ/u',
            '$1_n',
            $text
        ) ?? $text;
        $text = preg_replace(
            '/([A-Za-z])ₚ\+₁/u',
            '$1_{p+1}',
            $text
        ) ?? $text;
        $text = preg_replace(
            '/([A-Za-z])ₚ/u',
            '$1_p',
            $text
        ) ?? $text;
        $text = preg_replace(
            '/limₓ\s*\\\\ₜₒ\s*₊\\\\ᵢₙfₜy/u',
            '\\lim_{x \\to +\\infty}',
            $text
        ) ?? $text;
        $text = preg_replace(
            '/limₓ\s*\\\\ₜₒ\s*₋\\\\ᵢₙfₜy/u',
            '\\lim_{x \\to -\\infty}',
            $text
        ) ?? $text;
        $text = preg_replace(
            '/limₓ\s*\\\\ₜₒ\s*\\\\ᵢₙfₜy/u',
            '\\lim_{x \\to \\infty}',
            $text
        ) ?? $text;
        $text = preg_replace(
            '/limₓ\s*\\\\ₜₒ\s*₀/u',
            '\\lim_{x \\to 0}',
            $text
        ) ?? $text;
        $text = str_replace(['⅝', '⅜', '⅞', '½', '¼', '¾'], ['\\frac{5}{8}', '\\frac{3}{8}', '\\frac{7}{8}', '\\frac{1}{2}', '\\frac{1}{4}', '\\frac{3}{4}'], $text);

        // √(expr) أو √expr → \sqrt{expr}
        $text = preg_replace_callback(
            '/√\s*(?:\(([^()]+)\)|([a-zA-Z0-9]+))/u',
            static function (array $m): string {
                $inner = $m[1] !== '' ? $m[1] : $m[2];

                return '\\sqrt{'.$inner.'}';
            },
            $text
        ) ?? $text;

        $text = strtr($text, $symbolMap);

        $text = preg_replace_callback(
            '/([A-Za-z0-9\)])([⁰¹²³⁴⁵⁶⁷⁸⁹⁺⁻ⁿⁱ]+)/u',
            static function (array $m) use ($superscripts): string {
                $chars = preg_split('//u', $m[2], -1, PREG_SPLIT_NO_EMPTY) ?: [];
                $mapped = '';
                foreach ($chars as $ch) {
                    $mapped .= $superscripts[$ch] ?? $ch;
                }

                return $m[1].'^{'.$mapped.'}';
            },
            $text
        ) ?? $text;

        $text = preg_replace_callback(
            '/([A-Za-z0-9\)])([₀₁₂₃₄₅₆₇₈₉₊₋ₙᵢₓₚₖₘₜₛᵣᵤᵥₐₑ]+)/u',
            static function (array $m) use ($subscripts): string {
                $chars = preg_split('//u', $m[2], -1, PREG_SPLIT_NO_EMPTY) ?: [];
                $mapped = '';
                foreach ($chars as $ch) {
                    $mapped .= $subscripts[$ch] ?? $ch;
                }

                return $m[1].'_{'.$mapped.'}';
            },
            $text
        ) ?? $text;

        return $text;
    }

    /**
     * لفّ تعريفات مثل f(x)=\sqrt{...} ومجموعات \mathbb{R} داخل النص العربي.
     */
    private static function wrapCommonFunctionDefinitionsInArabicText(string $text): string
    {
        if (! preg_match('/[\x{0600}-\x{06FF}]/u', $text) && ! preg_match('/\\\\(?:sqrt|mathbb|frac|int|sum)/u', $text)) {
            // حتى بدون عربي: لفّ تعبيرات LaTeX العارية الشائعة إن لم تكن داخل $
            return self::wrapBareLatexCommandsOutsideDelimiters($text);
        }

        $parts = preg_split(self::MATH_SEGMENT_PATTERN, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return $text;
        }

        $output = '';
        $count = count($parts);

        for ($i = 0; $i < $count; $i++) {
            $part = $parts[$i];
            if ($part === '') {
                continue;
            }

            if (preg_match(self::MATH_SEGMENT_PATTERN, $part)) {
                $output .= $part;
                continue;
            }

            // دمج "f(x) =" مع مقطع رياضي تالٍ لتجنب "$f(x) =$$\frac...$"
            if (
                preg_match('/^(.*?)(?<!\$)\b([a-zA-Z]\s*\(\s*[a-zA-Z0-9]+\s*\)\s*=)\s*$/u', $part, $matches)
                && isset($parts[$i + 1])
                && preg_match(self::MATH_SEGMENT_PATTERN, $parts[$i + 1])
            ) {
                $mathInner = $parts[$i + 1];
                $mathInner = preg_replace('/^\$\$([\s\S]*)\$\$$/u', '$1', $mathInner) ?? $mathInner;
                $mathInner = preg_replace('/^\$(.*)\$$/us', '$1', $mathInner) ?? $mathInner;
                $mathInner = preg_replace('/^\\\\[\[\(]([\s\S]*)\\\\[\]\)]$/u', '$1', $mathInner) ?? $mathInner;
                $expr = self::normalizeMathExpression(trim($matches[2]).' '.trim($mathInner));
                $output .= $matches[1].'$'.$expr.'$';
                $i++;

                continue;
            }

            $output .= self::wrapBareLatexCommandsOutsideDelimiters($part);
        }

        return $output;
    }

    private static function wrapBareLatexCommandsOutsideDelimiters(string $text): string
    {
        // f(x)=\frac{...}{...} أو f(x)=\sqrt{...} كوحدة واحدة
        $text = preg_replace_callback(
            '/(?<!\$)\b([a-zA-Z]\s*\(\s*[a-zA-Z0-9]+\s*\)\s*=\s*\\\\(?:frac\{[^}]+\}\{[^}]+\}|sqrt\{[^}]+\}))(?!\$)/u',
            static function (array $m): string {
                $expr = self::normalizeMathExpression(trim($m[1]));

                return '$'.$expr.'$';
            },
            $text
        ) ?? $text;

        // f(x)=تعبير حقيقي (ليس مسافات فقط وليس يبدأ بـ $)
        $text = preg_replace_callback(
            '/(?<!\$)\b([a-zA-Z]\s*\(\s*[a-zA-Z0-9]+\s*\)\s*=\s*(?!\$)(?!\s*$)(?:\\\\sqrt\{[^}]+\}|[^\s$\n\x{0600}-\x{06FF}?؟][^$\n\x{0600}-\x{06FF}?؟]*))/u',
            static function (array $m): string {
                $expr = self::normalizeMathExpression(trim($m[1]));

                return '$'.$expr.'$';
            },
            $text
        ) ?? $text;

        $text = preg_replace_callback(
            '/(?<!\$)(\\\\mathbb\{[RNZQC]\})(?!\$)/u',
            static fn (array $m): string => '$'.$m[1].'$',
            $text
        ) ?? $text;

        $text = preg_replace_callback(
            '/(?<!\$)(\\\\sqrt\{[^}]+\})(?!\$)/u',
            static function (array $m): string {
                return '$'.$m[1].'$';
            },
            $text
        ) ?? $text;

        // +\infty / -\infty / \infty عارية داخل نص عربي
        $text = preg_replace_callback(
            '/(?<!\$)((?:\+|-)\\\\infty|\\\\infty)(?!\$)/u',
            static fn (array $m): string => '$'.$m[1].'$',
            $text
        ) ?? $text;

        return $text;
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

    public static function looksLikeMathExpression(string $text): bool
    {
        $trimmed = trim($text);
        if ($trimmed === '') {
            return false;
        }

        if (self::looksLikeCodeExpression($trimmed)) {
            return false;
        }

        if (self::containsMath($trimmed)) {
            return true;
        }

        if (preg_match(self::BARE_LATEX_PATTERN, $trimmed)) {
            return true;
        }

        if (preg_match(self::PSEUDO_MATH_PATTERN, $trimmed)) {
            return true;
        }

        if (preg_match('/(?:>=|<=|≥|≤|≠|!=)/u', $trimmed) && preg_match('/[a-zA-Z]/u', $trimmed)) {
            return true;
        }

        if (preg_match('/\)!|[a-zA-Z0-9]\!/u', $trimmed)) {
            return true;
        }

        if (preg_match('/[a-zA-Z](?:_\{[^}]+\}|\^\{[^}]+\}|_[a-zA-Z0-9]|\^[a-zA-Z0-9])+/u', $trimmed)) {
            return true;
        }

        if (preg_match(self::EQUATION_STEP_PATTERN, $trimmed)) {
            return true;
        }

        if (preg_match(self::SLASH_FRACTION_PATTERN, $trimmed)) {
            return true;
        }

        if (preg_match('/^\([a-zA-Z][a-zA-Z0-9_]*\)$/u', $trimmed)) {
            return true;
        }

        if (preg_match('/^\([a-zA-Z0-9+\-]+\)$/u', $trimmed) && preg_match('/[a-zA-Z]/u', $trimmed)) {
            return true;
        }

        if (preg_match('/^(?:\([^)]+\)){2,}$/u', $trimmed)) {
            return true;
        }

        if (preg_match('/^[-−+]?[\d]+$/u', $trimmed) && strlen($trimmed) <= 4) {
            return true;
        }

        if (preg_match('/[<>]=?|\\\\geq|\\\\leq/u', $trimmed) && preg_match('/[a-zA-Z_]/u', $trimmed)) {
            return true;
        }

        return false;
    }

    private static function looksLikeCodeExpression(string $text): bool
    {
        if (preg_match('/^[a-zA-Z_$][\w$.]*\([^)]*\)$/u', $text)) {
            return true;
        }

        if (preg_match('/^\[[\d,\s]+\]$/u', $text)) {
            return true;
        }

        if (preg_match('/^[a-zA-Z_$][\w$]*$/u', $text) && strlen($text) <= 40 && ! preg_match('/[!^_{}=<>≤≥]/u', $text)) {
            return true;
        }

        return false;
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

        if (self::looksLikeCodeExpression($trimmed)) {
            return false;
        }

        // نص عربي مختلط — لا نلفّ الجملة كاملة كـ LaTeX
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            return false;
        }

        if (preg_match(self::BARE_LATEX_PATTERN, $text)) {
            return true;
        }

        if (preg_match(self::PSEUDO_MATH_PATTERN, $text)) {
            return true;
        }

        if (preg_match('/\)!|[a-zA-Z0-9]\!/u', $trimmed)) {
            return true;
        }

        return false;
    }

    public static function wrapBareLatex(string $text): string
    {
        $text = trim($text);
        $text = preg_replace('/\\\\-+$/u', '', $text) ?? $text;
        $text = rtrim($text, " \t\\");
        $text = self::normalizeMathExpression(self::normalizePseudoMath($text));

        return self::wrapMathLatex($text, false);
    }

    private static function wrapMathSegment(string $segment): string
    {
        $segment = trim($segment);
        $display = false;
        $inner = $segment;

        if (preg_match('/^\$\$([\s\S]+?)\$\$$/u', $segment, $matches)) {
            $display = true;
            $inner = trim($matches[1]);
        } elseif (preg_match('/^\\\\\[([\s\S]+?)\\\\\]$/u', $segment, $matches)) {
            $display = true;
            $inner = trim($matches[1]);
        } elseif (preg_match('/^\\\\\(([\s\S]+?)\\\\\)$/u', $segment, $matches)) {
            $inner = trim($matches[1]);
        } elseif (preg_match('/^\$(.+)\$$/us', $segment, $matches)) {
            $inner = trim($matches[1]);
        }

        return self::wrapMathLatex($inner, $display);
    }

    /**
     * مخرجات آمنة 100%: LaTeX كنص مُهرَّب داخل span — KaTeX يقرأه من textContent.
     * لا نضع \( أو $ أو < خام في HTML.
     */
    private static function wrapMathLatex(string $latex, bool $display = false): string
    {
        $latex = self::htmlSafeMathInner($latex);
        if ($latex === '') {
            return '';
        }

        $displayAttr = $display ? '1' : '0';

        return '<span class="katex-src question-math-fragment" data-display="'.$displayAttr.'">'
            .e($latex)
            .'</span>';
    }

    /**
     * تحويل < و > داخل LaTeX حتى لا يكسر المتصفح innerHTML (مهم لـ u_p < 2).
     */
    private static function htmlSafeMathInner(string $inner): string
    {
        $inner = self::stripRedundantMathBraces($inner);
        // لا تلمس أوامر LaTeX مثل \langle
        $inner = preg_replace('/(?<!\\\\)</u', '\\lt ', $inner) ?? $inner;
        $inner = preg_replace('/(?<!\\\\)>/u', '\\gt ', $inner) ?? $inner;
        $inner = preg_replace('/\s+/u', ' ', $inner) ?? $inner;

        return trim($inner);
    }

    /**
     * {u_{n+1}} → u_{n+1} عندما يغلف التعبير بالكامل — دون لمس \frac{a}{b} أو \mathbb{R}.
     */
    private static function stripRedundantMathBraces(string $inner): string
    {
        $inner = trim($inner);

        if (! preg_match('/^\{([^{}]*(?:\{[^}]*\}[^{}]*)*)\}$/u', $inner, $matches)) {
            return $inner;
        }

        $candidate = trim($matches[1]);
        if ($candidate === '') {
            return $inner;
        }

        // لا تزل غلافاً هو عملياً وسيط أمر LaTeX وحيد مثل {R} بعد أن يُمرَّر خطأً
        if (preg_match('/^\\\\[a-zA-Z]+$/u', $candidate)) {
            return $inner;
        }

        // غلاف NotebookLM: يبدأ بحرف لاتيني (u_{n+1} أو u_0 = 1)
        if (preg_match('/^[a-zA-Z]/u', $candidate) && self::looksLikeMathExpression($candidate)) {
            return $candidate;
        }

        return $inner;
    }

    public static function format(?string $text): string
    {
        if ($text === null || trim($text) === '') {
            return '';
        }

        $text = trim(self::normalizeStoredText($text));

        if (self::isMultilinePlainExplanation($text)) {
            return self::formatMultilinePlainText($text);
        }

        return self::formatSingleBlock($text);
    }

    private static function isMultilinePlainExplanation(string $text): bool
    {
        if (! preg_match('/\r\n|\r|\n/u', $text)) {
            return false;
        }

        if (str_contains($text, '```')) {
            return false;
        }

        if (preg_match('/<(?:p|div|br|ul|ol|table|pre|span)\b/i', $text)) {
            return false;
        }

        return true;
    }

    private static function formatMultilinePlainText(string $text): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $output = '';

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $formatted = self::formatSingleBlock($line);
            $lineClass = self::isExplanationMathLine($line)
                ? 'question-explanation-math-line'
                : 'question-explanation-text-line';

            $output .= '<div class="'.$lineClass.'">'.$formatted.'</div>';
        }

        return $output;
    }

    private static function isExplanationMathLine(string $line): bool
    {
        $inner = trim($line, " \t`");

        if (preg_match(self::EQUATION_STEP_PATTERN, $inner)) {
            return true;
        }

        if (preg_match('/^`[^`]+`$/u', trim($line)) && self::looksLikeMathExpression(trim($line, '`'))) {
            return true;
        }

        if (self::looksLikeMathExpression($inner) && ! preg_match('/[\x{0600}-\x{06FF}]/u', $inner)) {
            return true;
        }

        return false;
    }

    private static function formatSingleBlock(string $text): string
    {
        $text = self::repairBrokenMathDelimiters($text);
        $text = self::convertMathBackticks($text);
        $text = self::convertBraceWrappedMathOutsideDelimiters($text);
        $text = self::convertUnicodeMathOutsideDelimiters($text);
        $text = self::normalizePseudoMath($text);
        $text = self::extractInlinePseudoMathInArabicText($text);
        $text = self::wrapCommonFunctionDefinitionsInArabicText($text);
        $text = self::repairBrokenMathDelimiters($text);

        if (str_contains($text, 'question-inline-code')) {
            return $text;
        }

        if (self::looksLikeBareLatex($text)) {
            return self::wrapBareLatex($text);
        }

        if (! self::containsMath($text)) {
            return self::formatWithoutMath($text);
        }

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
     * تحويل backticks التي تحتوي رياضيات إلى $...$ قبل معالجة الكود.
     */
    private static function convertMathBackticks(string $text): string
    {
        return preg_replace_callback(
            '/`([^`\n]+)`/u',
            static function (array $matches): string {
                $inner = $matches[1];
                if (! self::looksLikeMathExpression($inner)) {
                    return $matches[0];
                }

                return '$'.self::normalizeMathExpression($inner).'$';
            },
            $text
        ) ?? $text;
    }

    /**
     * تحويل أقواس NotebookLM مثل {u_{n+1}} و {u_0 = 1} إلى $...$.
     * لا يلمس وسيطات أوامر LaTeX مثل \sqrt{...} أو \frac{...}{...}.
     */
    private static function convertBraceWrappedMathOutsideDelimiters(string $text): string
    {
        if ($text === '' || ! str_contains($text, '{')) {
            return $text;
        }

        $parts = preg_split(self::MATH_SEGMENT_PATTERN, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
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
                continue;
            }

            // فقط {رمز_بمؤشر} أو {رمز = قيمة} وليس بعد حرف من أمر LaTeX
            $output .= preg_replace_callback(
                '/(?<![a-zA-Z])\{([a-zA-Z](?:_\{[^}]+\}|_[a-zA-Z0-9]+)(?:\s*=\s*[^{}]*)?|[a-zA-Z]\s*=\s*[^{}]+)\}/u',
                static function (array $matches): string {
                    $inner = trim($matches[1]);
                    if ($inner === '' || ! self::looksLikeMathExpression($inner)) {
                        return $matches[0];
                    }

                    return '$'.self::normalizeMathExpression($inner).'$';
                },
                $part
            ) ?? $part;
        }

        return $output;
    }

    /**
     * استخراج مقاطع pseudo-LaTeX داخل نص عربي (خارج delimiters).
     */
    private static function extractInlinePseudoMathInArabicText(string $text): string
    {
        if (! preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            return $text;
        }

        if (preg_match(self::MATH_SEGMENT_PATTERN, $text)) {
            $parts = preg_split(self::MATH_SEGMENT_PATTERN, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
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
                    $output .= self::wrapPseudoMathSegmentsInPlainText($part);
                }
            }

            return $output;
        }

        return self::wrapPseudoMathSegmentsInPlainText($text);
    }

    private static function wrapPseudoMathSegmentsInPlainText(string $text): string
    {
        $patterns = [
            '/sum_\{[^}]+\}(?:\s*[=+\-]\s*[^$\n\x{0600}-\x{06FF}]+)?/u',
            '/prod_\{[^}]+\}(?:\s*[=+\-]\s*[^$\n\x{0600}-\x{06FF}]+)?/u',
            '/lim_(?:\{[^}]+\}|[a-zA-Z])\s*\\\\to\s*(?:\+|-)?\\\\infty(?:\s*\\\\frac\{[^}]+\}\{[^}]+\})?/u',
            '/lim_(?:\{[^}]+\}|[a-zA-Z])\s*\\\\to\s*(?:\+|-)?\\\\infty\s*\\\\frac\{[^}]+\}\{[^}]+\}/u',
            '/\([^)]+\)\/\([^)]+\)\s*lim_(?:\{[^}]+\}|[a-zA-Z])\s*\\\\to\s*(?:\+|-)?(?:\\\\infty|∞)/u',
            // (u_n)_{n \ge 0} — قبل لفّ n \ge 0 وحدها
            '/(?<!\$)\([a-zA-Z](?:_\{[^}]+\}|_[a-zA-Z0-9]+)?\)_\{[^}]+\}/u',
            '/(?<!\$)\b([a-zA-Z](?:_\{[^}]+\}|_[a-zA-Z0-9]+)?\s*=\s*\\\\sqrt\{[^}]+\})/u',
            '/(?<!\$)\b([a-zA-Z](?:_\{[^}]+\}|_[a-zA-Z0-9]+)\s*=\s*[-+]?\d+)/u',
            '/(?<!\$)\b([a-zA-Z](?:_\{[^}]+\}|_[a-zA-Z0-9]+)?\s*(?:<|>|<=|>=|\\\\lt|\\\\gt|\\\\leq|\\\\geq|\\\\ge|\\\\le|≤|≥)\s*[-+]?\d+)/u',
            '/(?<!\$)\b([a-zA-Z](?:_\{[^}]+\}|_[a-zA-Z0-9]+)?\s*\\\\(?:geq|leq|neq|ge|le)\s*[-+]?\d+)/u',
            '/(?<!\$)\b([a-zA-Z](?:_\{[^}]+\}|_[a-zA-Z0-9]+))(?=[\s.؟!،,;:]|$)/u',
            '/(?<!\$)\b([a-zA-Z]\s*=\s*[-+]?\d+)(?=[\s.؟!،,;:]|$)/u',
        ];

        foreach ($patterns as $pattern) {
            $text = self::replacePatternOutsideMath($text, $pattern, static function (array $matches): string {
                $segment = self::normalizeMathExpression(self::normalizePseudoMath($matches[0]));

                return '$'.$segment.'$';
            });
        }

        return $text;
    }

    /**
     * تطبيق استبدال فقط خارج مقاطع $...$ / $$...$$ / \(...\) لتجنّب كسر الصيغ الملفوفة.
     */
    private static function replacePatternOutsideMath(string $text, string $pattern, callable $callback): string
    {
        $parts = preg_split(self::MATH_SEGMENT_PATTERN, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return preg_replace_callback($pattern, $callback, $text) ?? $text;
        }

        $output = '';
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            if (preg_match(self::MATH_SEGMENT_PATTERN, $part)) {
                $output .= $part;
            } else {
                $output .= preg_replace_callback($pattern, $callback, $part) ?? $part;
            }
        }

        return $output;
    }

    /**
     * تطبيع تعبير رياضي واحد (داخل $...$) دون إضافة محددات.
     */
    private static function normalizeMathExpression(string $expr): string
    {
        $expr = trim(self::convertPseudoCommands($expr));
        $expr = self::normalizeFractionSlashNotation($expr);
        $expr = self::stripRedundantMathBraces($expr);

        $expr = str_replace(['>=', '<=', '≥', '≤', '≠', '!=', '×'], ['\\geq', '\\leq', '\\geq', '\\leq', '\\neq', '\\neq', '\\cdot'], $expr);
        $expr = preg_replace('/\\\\ge(?![a-zA-Z])/u', '\\geq', $expr) ?? $expr;
        $expr = preg_replace('/\\\\le(?![a-zA-Z])/u', '\\leq', $expr) ?? $expr;
        $expr = preg_replace('/(?<!\\\\)</u', '\\lt ', $expr) ?? $expr;
        $expr = preg_replace('/(?<!\\\\)>/u', '\\gt ', $expr) ?? $expr;
        // {u_{n+1}} داخل تعبير أوسع → u_{n+1} (لا يلمس \sqrt{...})
        $expr = preg_replace(
            '/(?<![a-zA-Z\\\\])\{([a-zA-Z](?:_\{[^}]+\}|_[a-zA-Z0-9]+))\}/u',
            '$1',
            $expr
        ) ?? $expr;
        $expr = preg_replace('/(?<=[a-zA-Z0-9\)])\*(?=[a-zA-Z0-9\(])/u', ' \\cdot ', $expr) ?? $expr;
        $expr = preg_replace('/\^(\d+)/u', '^{$1}', $expr) ?? $expr;
        $expr = preg_replace('/\s+/u', ' ', $expr) ?? $expr;

        return trim($expr);
    }

    /**
     * تحويل (a)/(b) و [a] / [b] إلى \frac{a}{b}.
     */
    private static function normalizeFractionSlashNotation(string $expr): string
    {
        $expr = preg_replace_callback(
            '/\[([^\]]+)\]\s*\/\s*\[([^\]]+)\]/u',
            static fn (array $matches): string => '\\frac{'.$matches[1].'}{'.$matches[2].'}',
            $expr
        ) ?? $expr;

        return preg_replace_callback(
            '/\(([^()]+)\)\s*\/\s*\(([^()]+)\)/u',
            static fn (array $matches): string => '\\frac{'.$matches[1].'}{'.$matches[2].'}',
            $expr
        ) ?? $expr;
    }

    /**
     * تحويل أوامر pseudo (sum_, lim_, …) إلى LaTeX بدون محددات $.
     */
    private static function convertPseudoCommands(string $text): string
    {
        $text = preg_replace_callback(
            '/\b(sum|prod|int)_\{([^}]+)\}/u',
            static function (array $matches): string {
                $cmd = $matches[1];
                $inner = trim($matches[2]);
                if (preg_match('/^(.+?)\s+to\s+(.+)$/u', $inner, $bounds)) {
                    return '\\'.$cmd.'_{'.$bounds[1].'}^{'.$bounds[2].'}';
                }

                return '\\'.$cmd.'_{'.$inner.'}';
            },
            $text
        ) ?? $text;

        $text = preg_replace_callback(
            '/lim_([a-zA-Z])\s*\\\\to\s*(\+\\\\infty|\+∞|-\\\\infty|-∞|\\\\infty|∞)(?:\s*\\\\frac\{([^}]+)\}\{([^}]+)\})?/u',
            static function (array $matches): string {
                $infinity = self::normalizeInfinityTarget($matches[2]);
                $base = '\\lim_{'.$matches[1].' \to '.$infinity.'}';
                if (isset($matches[3], $matches[4]) && $matches[3] !== '') {
                    $base .= ' \\frac{'.$matches[3].'}{'.$matches[4].'}';
                }

                return $base;
            },
            $text
        ) ?? $text;

        $text = preg_replace_callback(
            '/lim_(?:\{([^}]+)\}|([a-zA-Z]))\s*\\\\to\s*(\+\\\\infty|\+∞|-\\\\infty|-∞|\\\\infty|∞)\s*\\\\frac\{([^}]+)\}\{([^}]+)\}/u',
            static function (array $matches): string {
                $variable = $matches[1] !== '' ? $matches[1] : $matches[2];
                $infinity = self::normalizeInfinityTarget($matches[3]);

                return '\\lim_{'.$variable.' \to '.$infinity.'} \\frac{'.$matches[4].'}{'.$matches[5].'}';
            },
            $text
        ) ?? $text;

        $text = preg_replace_callback(
            '/\(([^()]+)\)\/\(([^()]+)\)\s*lim_(?:\{([^}]+)\}|([a-zA-Z]))\s*\\\\to\s*(\+\\\\infty|\+∞|-\\\\infty|-∞|\\\\infty|∞)/u',
            static function (array $matches): string {
                $variable = $matches[3] !== '' ? $matches[3] : $matches[4];
                $infinity = self::normalizeInfinityTarget($matches[5]);

                return '\\lim_{'.$variable.' \to '.$infinity.'} \\frac{'.$matches[1].'}{'.$matches[2].'}';
            },
            $text
        ) ?? $text;

        $text = preg_replace_callback(
            '/lim_(?:\{([^}]+)\}|([a-zA-Z]))\s*\\\\to\s*(\+\\\\infty|\+∞|-\\\\infty|-∞|\\\\infty|∞)/u',
            static function (array $matches): string {
                $variable = $matches[1] !== '' ? $matches[1] : $matches[2];
                $infinity = self::normalizeInfinityTarget($matches[3]);

                return '\\lim_{'.$variable.' \to '.$infinity.'}';
            },
            $text
        ) ?? $text;

        return preg_replace(
            '/\\\\frac\{([^}]+)\}\{([^}]+)\}/u',
            '\\frac{$1}{$2}',
            $text
        ) ?? $text;
    }

    /**
     * تمييز أكواد البرمجة (push(), [1,2,3]) حتى بدون backticks — مفيد في خيارات الإجابة.
     */
    private static function autoWrapCodePatterns(string $text): string
    {
        if (trim($text) === '') {
            return '';
        }

        $protectedPattern = '/(<pre class="question-code-block">[\s\S]*?<\/pre>)|(<code class="question-inline-code">[\s\S]*?<\/code>)|(<span class="(?:katex-src\s+)?question-math-fragment"[^>]*>[\s\S]*?<\/span>)|(<span class="katex-src"[^>]*>[\s\S]*?<\/span>)/u';
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
            return self::wrapBareLatex($trimmed);
        }

        // لا نعتبر "$f(x)" كوداً — علامة $ تعني رياضيات مكسورة أو صحيحة
        if ($trimmed !== '' && preg_match('/^\$/', $trimmed)) {
            return self::escapePlainSegment($text);
        }

        if ($trimmed !== '' && preg_match('/^[a-zA-Z_][\w.]*(?:\.[a-zA-Z_][\w.]*)*\([^)]*\)$/u', $trimmed)) {
            return self::wrapInlineCode($trimmed);
        }

        if ($trimmed !== '' && preg_match('/^'.self::INTERVAL_PATTERN.'$/u', $trimmed)) {
            return self::wrapInlineCode($trimmed);
        }

        if ($trimmed !== '' && preg_match('/^\[[^\]]+\]$/u', $trimmed)) {
            return self::wrapInlineCode($trimmed);
        }

        if ($trimmed !== '' && preg_match('/^[a-zA-Z_][\w]*$/u', $trimmed) && strlen($trimmed) <= 40) {
            return self::wrapInlineCode($trimmed);
        }

        $codePattern = '/('.self::ABS_INEQUALITY_PATTERN.')|('.self::INTERVAL_PATTERN.')|(\[[^\]]+\])|([a-zA-Z_][\w]*(?:\.[a-zA-Z_][\w]*)*\([^)]*\))/u';
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
                if (preg_match('/^'.self::ABS_INEQUALITY_PATTERN.'$/u', trim($part))) {
                    $output .= self::wrapBareLatex($part);
                } else {
                    $output .= self::wrapInlineCode($part);
                }
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

    private static function wrapInlineMathFromBacktick(string $inner): string
    {
        $normalized = self::normalizeMathExpression($inner);

        return self::wrapMathSegment('$'.$normalized.'$');
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
        $backtickHandler = static function (array $matches): string {
            $inner = $matches[1];
            if (self::looksLikeMathExpression($inner)) {
                return self::wrapInlineMathFromBacktick($inner);
            }

            return self::wrapInlineCode($inner);
        };

        if ($text !== strip_tags($text)) {
            return preg_replace_callback('/`([^`\n]+)`/u', $backtickHandler, $text) ?? $text;
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
                $output .= self::looksLikeMathExpression($matches[1])
                    ? self::wrapInlineMathFromBacktick($matches[1])
                    : self::wrapInlineCode($matches[1]);
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

        $text = self::applyPseudoCommandsOutsideMath($text);
        // لفّ f(x)=\frac قبل لفّ \frac وحدها لتفادي "$f(x) =$$\frac$"
        $text = self::wrapFunctionEqualsFracOutsideMath($text);
        $text = self::replaceBareFracOutsideMath($text);

        $text = preg_replace_callback(
            '/(?<!\$)\$(?!\$)([^\$\n]+?)(?<!\$)\$(?!\$)/u',
            static function (array $matches): string {
                $content = self::normalizeMathExpression(str_replace('∞', '\\infty', $matches[1]));

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

    private static function applyPseudoCommandsOutsideMath(string $text): string
    {
        $parts = preg_split(self::MATH_SEGMENT_PATTERN, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return self::convertPseudoCommands($text);
        }

        $output = '';
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            if (preg_match(self::MATH_SEGMENT_PATTERN, $part)) {
                $output .= $part;
            } else {
                $converted = self::convertPseudoCommands($part);
                $converted = preg_replace_callback(
                    '/\(([^()]+)\)\/\(([^()]+)\)/u',
                    static function (array $matches): string {
                        if (
                            preg_match('/[a-zA-Z0-9^\\\\]/u', $matches[1])
                            && preg_match('/[a-zA-Z0-9^\\\\]/u', $matches[2])
                        ) {
                            return '$\\frac{'.$matches[1].'}{'.$matches[2].'}$';
                        }

                        return $matches[0];
                    },
                    $converted
                ) ?? $converted;
                $output .= self::wrapDetectedLatexExpressionsInDollars($converted);
            }
        }

        return $output;
    }

    private static function wrapDetectedLatexExpressionsInDollars(string $text): string
    {
        return preg_replace_callback(
            '/\\\\(?:lim|sum|prod|int)_(?:\{[^}]+\}|[a-zA-Z])(?:\s*\\\\to\s*[^\s$]+)?(?:\s*\\\\frac\{[^}]+\}\{[^}]+\})?/u',
            static function (array $matches): string {
                $expr = $matches[0];

                return '$'.$expr.'$';
            },
            $text
        ) ?? $text;
    }

    private static function wrapFunctionEqualsFracOutsideMath(string $text): string
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
                $output .= preg_replace_callback(
                    '/(?<!\$)\b([a-zA-Z]\s*\(\s*[a-zA-Z0-9]+\s*\)\s*=\s*\\\\frac\{[^}]+\}\{[^}]+\})(?!\$)/u',
                    static function (array $matches): string {
                        return '$'.self::normalizeMathExpression($matches[1]).'$';
                    },
                    $part
                ) ?? $part;
            }
        }

        return $output;
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
