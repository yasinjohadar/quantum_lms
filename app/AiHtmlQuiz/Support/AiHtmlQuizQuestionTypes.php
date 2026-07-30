<?php

namespace App\AiHtmlQuiz\Support;

/**
 * Interaction / question styles for AI HTML quizzes (guidance for generation, not fixed runtime templates).
 */
class AiHtmlQuizQuestionTypes
{
    /**
     * @return array<string, array{label: string, hint: string}>
     */
    public static function all(): array
    {
        return [
            'single_choice' => [
                'label' => 'اختيار من متعدد',
                'hint' => 'سؤال واحد وإجابة صحيحة واحدة من عدة خيارات',
            ],
            'true_false' => [
                'label' => 'صح أو خطأ',
                'hint' => 'عبارة يختار الطالب صحتها أو خطئها',
            ],
            'multiple_select' => [
                'label' => 'اختيار متعدد الإجابات',
                'hint' => 'أكثر من إجابة صحيحة يمكن تحديدها معاً',
            ],
            'fill_blank' => [
                'label' => 'أكمل الفراغ',
                'hint' => 'نص ناقص يُكمل بكلمة أو رقم',
            ],
            'matching' => [
                'label' => 'مطابقة',
                'hint' => 'ربط عناصر من عمودين بشكل صحيح',
            ],
            'ordering' => [
                'label' => 'ترتيب',
                'hint' => 'ترتيب خطوات أو عناصر بالتسلسل الصحيح',
            ],
            'drag_drop' => [
                'label' => 'سحب وإفلات',
                'hint' => 'سحب عناصر إلى أماكنها الصحيحة',
            ],
            'click_hotspot' => [
                'label' => 'انقر على المنطقة',
                'hint' => 'النقر على منطقة/عنصر صحيح في المشهد',
            ],
            'memory' => [
                'label' => 'بطاقات ذاكرة',
                'hint' => 'قلب بطاقات وإيجاد الأزواج المتطابقة',
            ],
            'short_answer' => [
                'label' => 'إجابة قصيرة',
                'hint' => 'إدخال نص أو رقم قصير للتحقق',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    /**
     * @param  list<mixed>  $types
     * @return list<string>
     */
    public static function filterValid(array $types): array
    {
        $allowed = self::keys();
        $out = [];
        foreach ($types as $type) {
            $key = is_string($type) ? trim($type) : '';
            if ($key !== '' && in_array($key, $allowed, true) && ! in_array($key, $out, true)) {
                $out[] = $key;
            }
        }

        return $out;
    }

    /**
     * @param  list<string>  $types
     */
    public static function labelsFor(array $types): string
    {
        $all = self::all();
        $parts = [];
        foreach (self::filterValid($types) as $key) {
            $parts[] = ($all[$key]['label'] ?? $key).' ('.$key.') — '.($all[$key]['hint'] ?? '');
        }

        return implode("\n", $parts);
    }
}
