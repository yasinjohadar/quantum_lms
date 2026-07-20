<?php

namespace App\Services\AI;

use App\Models\Question;
use App\Support\QuestionMarkupFormatter;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * إصلاح صيغة LaTeX المكسورة في سؤال بالكامل (عنوان/محتوى/شرح/خيارات) عبر الذكاء
 * الاصطناعي، للحالات التي يعجز التطبيع الآلي بالأنماط النصية (regex) عن حسمها
 * بثقة — أشهرها: \frac التي فقدت الشرطة المعكوسة *والأقواس معاً* فالتصق البسط
 * والمقام ببعضهما بلا أي فاصل نصي يحدد أين ينتهي أحدهما ويبدأ الآخر.
 *
 * تُستخدم من QuestionMathBackfillService::processAiRepairBatch() على الأسئلة
 * التي لا تزال "مشتبهاً بها" (QuestionMarkupFormatter::hasSuspiciousBareLatex)
 * بعد تشغيل الإصلاح الآلي المجاني، لتقليل عدد استدعاءات الذكاء الاصطناعي.
 */
class MathLatexRepairService
{
    /**
     * ترتيب تفضيل القدرات عند البحث عن موديل ذكاء اصطناعي: نبحث أولاً عن موديل
     * مخصَّص لهذه المهمة تحديداً، ثم نتراجع لموديل حل الأسئلة (يفهم الرياضيات
     * جيداً)، ثم أي موديل نشط افتراضي — لتعمل الأداة دون الحاجة لأي تهيئة إضافية
     * في لوحة إدارة موديلات الذكاء الاصطناعي.
     */
    private const CAPABILITY_FALLBACK_CHAIN = ['math_latex_repair', 'question_solving', 'all'];

    public function __construct(
        private AIModelService $modelService,
        private AIPromptService $promptService,
    ) {}

    /**
     * فحص سريع (بلا أي استدعاء شبكة) لوجود موديل AI متاح لهذه المهمة — يُستخدم
     * من واجهة الإصلاح الشامل لإظهار رسالة خطأ واضحة فوراً قبل بدء أي دفعة.
     */
    public function hasAvailableModel(): bool
    {
        return $this->resolveModel() !== null;
    }

    /**
     * يبني خريطة حقول السؤال (العنوان/المحتوى/الشرح وكل خيار)، يستدعي الذكاء
     * الاصطناعي لإصلاحها دفعة واحدة، ثم يُعيد تطبيع كل نص عبر
     * QuestionMarkupFormatter::deepNormalizeForStorage() كشبكة أمان أخيرة قبل
     * إعادته للمستدعي (الذي يتولى الحفظ في قاعدة البيانات).
     *
     * @return array<string, string> خريطة بنفس مفاتيح buildFieldMap() بعد الإصلاح والتطبيع
     *
     * @throws RuntimeException إذا تعذّر إيجاد موديل ذكاء اصطناعي متاح، أو تعذّر تحليل رده
     */
    public function repairQuestionMath(Question $question): array
    {
        $fields = $this->buildFieldMap($question);
        if ($fields === []) {
            return [];
        }

        $model = $this->resolveModel();
        if (! $model) {
            throw new RuntimeException('لا يوجد موديل AI متاح لإصلاح صيغة LaTeX. أضف/فعِّل موديلاً في إدارة الذكاء الاصطناعي.');
        }

        $prompt = $this->promptService->getMathLatexRepairPrompt($fields);

        try {
            $provider = AIProviderFactory::create($model);
            $response = $provider->generateText($prompt, [
                'max_tokens' => $model->max_tokens,
                'temperature' => 0.1,
            ]);
        } catch (\Throwable $e) {
            Log::error('MathLatexRepairService: AI call failed', [
                'question_id' => $question->id,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('فشل استدعاء الذكاء الاصطناعي لإصلاح السؤال #'.$question->id.': '.$e->getMessage(), previous: $e);
        }

        try {
            return $this->applyAiResponse($fields, $response);
        } catch (\RuntimeException $e) {
            Log::error('MathLatexRepairService: could not parse AI response as JSON', [
                'question_id' => $question->id,
                'response' => $response,
            ]);

            throw new RuntimeException('تعذّر تحليل رد الذكاء الاصطناعي كـ JSON للسؤال #'.$question->id.'.', previous: $e);
        }
    }

    /**
     * دالة نقية (بلا أي اتصال شبكة) تُحلِّل رد الذكاء الاصطناعي الخام (نص قد يحتوي
     * JSON محاطاً بشرح إضافي) وتُطبِّق القيم المُصحَّحة على خريطة الحقول الأصلية،
     * مع تمرير كل نتيجة عبر deepNormalizeForStorage كشبكة أمان أخيرة. مُستخرَجة
     * كدالة عامة مستقلة لتكون قابلة للاختبار مباشرة بردود AI محفوظة (canned)
     * دون حاجة لأي استدعاء AI حقيقي أو مُزيَّف.
     *
     * @param  array<string, string>  $originalFields  خريطة "اسم الحقل" => "النص الأصلي" (كما بُنيت في buildFieldMap)
     * @return array<string, string> خريطة بنفس المفاتيح بعد الإصلاح والتطبيع
     *
     * @throws RuntimeException إذا تعذّر إيجاد/تحليل كائن JSON صالح داخل الرد
     */
    public function applyAiResponse(array $originalFields, string $aiResponse): array
    {
        $corrected = $this->parseJsonResponse($aiResponse);
        if ($corrected === null) {
            throw new RuntimeException('تعذّر تحليل رد الذكاء الاصطناعي كـ JSON.');
        }

        $result = [];
        foreach ($originalFields as $key => $original) {
            $value = $corrected[$key] ?? $original;
            if (! is_string($value) || trim($value) === '') {
                $value = $original;
            }

            // شبكة أمان أخيرة: أي عدم اتساق طفيف في تنسيق رد الذكاء الاصطناعي
            // يُعاد تطبيعه بمنطقنا الحالي قبل التخزين.
            $result[$key] = QuestionMarkupFormatter::deepNormalizeForStorage($value);
        }

        return $result;
    }

    /**
     * @return array<string, string>
     */
    private function buildFieldMap(Question $question): array
    {
        $fields = [];

        foreach (['title', 'content', 'explanation'] as $field) {
            $value = $question->{$field};
            if (is_string($value) && trim($value) !== '') {
                $fields[$field] = $value;
            }
        }

        foreach ($question->options as $option) {
            foreach (['content', 'match_target', 'feedback'] as $field) {
                $value = $option->{$field};
                if (is_string($value) && trim($value) !== '') {
                    $fields['option_'.$option->id.'_'.$field] = $value;
                }
            }
        }

        return $fields;
    }

    private function resolveModel(): ?\App\Models\AIModel
    {
        foreach (self::CAPABILITY_FALLBACK_CHAIN as $capability) {
            $model = $capability === 'all'
                ? $this->modelService->getDefaultModel()
                : $this->modelService->getBestModelFor($capability);

            if ($model) {
                return $model;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function parseJsonResponse(string $response): ?array
    {
        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}');

        if ($jsonStart === false || $jsonEnd === false || $jsonEnd < $jsonStart) {
            return null;
        }

        $jsonString = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
        $decoded = json_decode($jsonString, true);

        return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : null;
    }
}
