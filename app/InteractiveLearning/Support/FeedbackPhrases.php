<?php

namespace App\InteractiveLearning\Support;

/**
 * عبارات التغذية الراجعة للتجارب التفاعلية مع تسجيلاتها الصوتية.
 *
 * مصدر الحقيقة الوحيد للربط بين العبارة المعروضة على الشاشة وملف الصوت الذي ينطقها،
 * حتى يبقى المكتوب هو المسموع حرفياً. تُستخدم في المشغّل (resources/js/interactive-engine)
 * وفي محرّر الإدمن وفي برومبتات التوليد بالذكاء الاصطناعي.
 *
 * تنبيه: أسماء الملفات أدناه منسوخة كما هي على القرص، وفيها فروقات إملائية ومسافات
 * زائدة عن نص العبارة (مثل «مزهل» و«حاول مر أخرى» ومسافة قبل .ogg). لا تُعِد كتابتها
 * انطلاقاً من نص العبارة — أي «تصحيح» لها يكسر تشغيل الصوت.
 */
class FeedbackPhrases
{
    public const KIND_SUCCESS = 'success';

    public const KIND_FAIL = 'fail';

    /**
     * @var array<string, list<array{text: string, file: string}>>
     */
    private const MAP = [
        'success' => [
            ['text' => 'إجابة ممتازة أحسنت التفكير', 'file' => 'إجابة ممتازة أحسنت التفكير.ogg'],
            ['text' => 'إجابة صحيحة عمل رائع', 'file' => 'إجابة صحيحة عمل رائع .ogg'],
            ['text' => 'أحسنت إجابة رائعة', 'file' => 'أحسنت إجابة رائعة .ogg'],
            ['text' => 'ممتاز واصل التقدم', 'file' => 'ممتاز واصل التقدم.ogg'],
            ['text' => 'أحسنت كثيراً أنا فخور بك', 'file' => 'أحسنت كثيرا أنا فخور بك .ogg'],
            ['text' => 'إجابة دقيقة أحسنت', 'file' => 'إجابة دقيقة أحسنت .ogg'],
            ['text' => 'أنت رائع استمر', 'file' => 'أنت رائع إستمر.ogg'],
            ['text' => 'عمل رائع أكمل التحدي', 'file' => 'عمل رائع أكمل التحدي.ogg'],
            ['text' => 'مذهل أنت تتعلم بسرعة', 'file' => 'مزهل أنت تتعلم بسرعة.ogg'],
            ['text' => 'أحسنت خطوة جديدة نحو النجاح', 'file' => 'أحسنت خطوة جديدة نحو النجاح.ogg'],
        ],
        'fail' => [
            ['text' => 'أعد التفكير بالإجابة', 'file' => 'أعد التفكير بالإجابة .ogg'],
            ['text' => 'هيا جرب مرة أخرى', 'file' => 'هيا جرب مرة أخرى .ogg'],
            ['text' => 'لا تتوقف عن المحاولة', 'file' => 'لاتتوقف عن المحاولة .ogg'],
            ['text' => 'حاول مرة أخرى بثقة', 'file' => 'حاول مر أخرى بثقة.ogg'],
            ['text' => 'ركز قليلاً ثم أعد المحاولة', 'file' => 'ركز قليلا ثم أعد المحاولة .ogg'],
            ['text' => 'لا تيأس أنت قادر', 'file' => 'لا تيأس أنت قادر .ogg'],
            ['text' => 'حاول من جديد', 'file' => 'حاول من جديد.ogg'],
            ['text' => 'المحاولة القادمة ستكون أفضل', 'file' => 'المحاولة القادمة ستكون أفضل .ogg'],
            ['text' => 'تعلم من الخطأ', 'file' => 'تعلم من الخطأ.ogg'],
            ['text' => 'لا تستعجل الإجابة', 'file' => 'لاتستعجل الاجابة .ogg'],
        ],
    ];

    /**
     * الخريطة الكاملة كما هي (نص + اسم الملف) — للتحقق وأوامر الترحيل.
     *
     * @return array<string, list<array{text: string, file: string}>>
     */
    public static function all(): array
    {
        return self::MAP;
    }

    /**
     * توحيد اسم النوع: success مقابل fail (مع قبول error / wrong كمرادفات).
     */
    public static function kind(string $kind): string
    {
        return in_array($kind, ['fail', 'error', 'wrong'], true)
            ? self::KIND_FAIL
            : self::KIND_SUCCESS;
    }

    /**
     * نصوص العبارات فقط — لبناء القائمة المنسدلة وبرومبتات الذكاء الاصطناعي.
     *
     * @return list<string>
     */
    public static function texts(string $kind): array
    {
        return array_column(self::MAP[self::kind($kind)], 'text');
    }

    /**
     * الخريطة المُعدّة للواجهة: نص + رابط الصوت الجاهز للتشغيل.
     *
     * asset() لا يرمّز الحروف العربية ولا المسافات، لذا يُرمَّز اسم الملف وحده.
     *
     * @return array<string, list<array{text: string, url: string}>>
     */
    public static function forPlayer(): array
    {
        $out = [];

        foreach (self::MAP as $kind => $rows) {
            $out[$kind] = array_map(fn (array $row) => [
                'text' => $row['text'],
                'url' => asset('sounds/'.$kind.'/'.rawurlencode($row['file'])),
            ], $rows);
        }

        return $out;
    }

    /**
     * مفتاح مقارنة متسامح: يتجاهل التشكيل وصيغ الألف والتاء المربوطة وعلامات الترقيم.
     */
    public static function normalizeKey(string $text): string
    {
        $text = preg_replace('/[\x{064B}-\x{0652}\x{0640}\x{0670}\x{06D6}-\x{06ED}]/u', '', $text) ?? $text;
        $text = str_replace(['أ', 'إ', 'آ', 'ٱ'], 'ا', $text);
        $text = str_replace('ة', 'ه', $text);
        $text = str_replace('ى', 'ي', $text);
        $text = str_replace(['!', '؟', '?', '.', '،', ',', '…', ':', '-', '—', '–'], ' ', $text);
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    /**
     * مفتاح احتياطي بلا مسافات — يعالج فروق الوصل مثل «لا تتوقف» و«لاتتوقف».
     */
    private static function compactKey(string $text): string
    {
        return str_replace(' ', '', self::normalizeKey($text));
    }

    /**
     * النص القانوني للعبارة إن كانت معروفة، وإلا null.
     */
    public static function match(?string $text, string $kind): ?string
    {
        $text = trim((string) $text);
        if ($text === '') {
            return null;
        }

        $rows = self::MAP[self::kind($kind)];
        $key = self::normalizeKey($text);

        foreach ($rows as $row) {
            if (self::normalizeKey($row['text']) === $key) {
                return $row['text'];
            }
        }

        $compact = self::compactKey($text);
        foreach ($rows as $row) {
            if (self::compactKey($row['text']) === $compact) {
                return $row['text'];
            }
        }

        return null;
    }

    /**
     * إرجاع عبارة مضمونة الصوت: النص القانوني إن طابَق، وإلا عبارة من القائمة
     * بالتناوب حسب $index حتى تتنوّع الرسائل بين أسئلة الاختبار الواحد.
     */
    public static function snap(?string $text, string $kind, int $index = 0): string
    {
        $matched = self::match($text, $kind);
        if ($matched !== null) {
            return $matched;
        }

        $texts = self::texts($kind);

        return $texts[abs($index) % count($texts)];
    }

    /**
     * اضبط رسائل كل أسئلة المخطط على عبارات لها تسجيل صوتي.
     *
     * @param  array<string, mixed>  $schema
     * @return array{schema: array<string, mixed>, changed: int}
     */
    public static function snapSchema(array $schema): array
    {
        if (! isset($schema['questions']) || ! is_array($schema['questions'])) {
            return ['schema' => $schema, 'changed' => 0];
        }

        $changed = 0;

        foreach (array_values($schema['questions']) as $index => $question) {
            if (! is_array($question)) {
                continue;
            }

            foreach (['successMessage' => self::KIND_SUCCESS, 'errorMessage' => self::KIND_FAIL] as $key => $kind) {
                $before = $question[$key] ?? null;
                $after = self::snap(is_scalar($before) ? (string) $before : null, $kind, $index);
                if ($before !== $after) {
                    $changed++;
                }
                $question[$key] = $after;
            }

            $schema['questions'][$index] = $question;
        }

        return ['schema' => $schema, 'changed' => $changed];
    }

    /**
     * اسم ملف الصوت الموافق للعبارة (كما هو على القرص).
     */
    public static function fileFor(?string $text, string $kind): ?string
    {
        $matched = self::match($text, $kind);
        if ($matched === null) {
            return null;
        }

        foreach (self::MAP[self::kind($kind)] as $row) {
            if ($row['text'] === $matched) {
                return $row['file'];
            }
        }

        return null;
    }

    /**
     * رابط الصوت الموافق للعبارة، أو null إن كانت العبارة غير معروفة.
     */
    public static function urlFor(?string $text, string $kind): ?string
    {
        $file = self::fileFor($text, $kind);
        $resolved = self::kind($kind);

        return $file === null ? null : asset('sounds/'.$resolved.'/'.rawurlencode($file));
    }
}
