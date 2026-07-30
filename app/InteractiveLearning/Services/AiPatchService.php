<?php

namespace App\InteractiveLearning\Services;

use App\Services\AI\AIModelService;
use App\Services\AI\AIProviderFactory;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AiPatchService
{
    public function __construct(
        protected AIModelService $modelService,
        protected SchemaPatchApplicator $applicator,
        protected SchemaValidator $validator
    ) {}

    /**
     * @param  array<string, mixed>  $schema
     * @return array{operations: list<array<string, mixed>>, summary: string, raw?: string}
     */
    public function proposePatch(array $schema, string $intent = 'حسّن صياغة الأسئلة والشرح والرسائل دون تغيير الإجابات الصحيحة'): array
    {
        $model = $this->modelService->getDefaultModel();
        if (! $model) {
            throw new RuntimeException('لا يوجد نموذج ذكاء اصطناعي افتراضي مفعّل.');
        }

        $provider = AIProviderFactory::create($model);
        $prompt = $this->buildPrompt($schema, $intent);
        $response = $provider->generateText($prompt, [
            'max_tokens' => min(4000, $model->max_tokens ?: 4000),
            'temperature' => 0.4,
        ]);

        if (! $response) {
            throw new RuntimeException($provider->getLastError() ?: 'فشل استدعاء الذكاء الاصطناعي.');
        }

        $parsed = $this->parseOperations($response);
        if (($parsed['operations'] ?? []) === []) {
            throw new RuntimeException('لم يُرجع الذكاء الاصطناعي أي عمليات قابلة للتطبيق.');
        }

        return $parsed;
    }

    /**
     * @param  array<string, mixed>  $schema
     * @param  list<array<string, mixed>>  $operations
     * @return array{schema: array<string, mixed>, applied: int, errors: list<string>, validation: array{valid: bool, errors: list<string>}}
     */
    public function applyPatch(array $schema, array $operations): array
    {
        $result = $this->applicator->apply($schema, $operations);
        $validation = $this->validator->validate($result['schema']);

        return [
            'schema' => $result['schema'],
            'applied' => $result['applied'],
            'errors' => $result['errors'],
            'validation' => $validation,
        ];
    }

    /**
     * @param  array<string, mixed>  $schema
     */
    protected function buildPrompt(array $schema, string $intent): string
    {
        $json = json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return <<<PROMPT
أنت مساعد تأليف تجارب تعليمية تفاعلية لطلاب صغار.
مهمتك اقتراح تحسينات على Schema الجلسة دون تغيير المنطق الصحيح للإجابات.
استخدم لغة عربية بسيطة حماسية تناسب الأطفال (مثل: يا بطل، أحسنت يا شاطر، جرّب مرة ثانية).

النية: {$intent}

قواعد صارمة:
1) أعد JSON فقط بالشكل:
{
  "summary": "ملخص قصير بالعربية",
  "operations": [
    {
      "op": "update_question",
      "questionId": "...",
      "fields": {
        "stem": "...",
        "explanation": "...",
        "successMessage": "...",
        "errorMessage": "...",
        "hints": ["..."]
      }
    }
  ]
}
2) العمليات المسموحة فقط: update_question, update_meta, update_messages, update_rules
3) لا تغيّر ids الأسئلة.
4) لا تغيّر correct / correctId / correctIds / assignments / pairs إلا إذا كان النص فقط (labels) أوضح.
5) لا تُخرج HTML أو JavaScript.
6) لا تنشر — اقترح operations فقط.
7) successMessage و errorMessage و messages يجب أن تكون قصيرة وحماسية للأطفال.
8) عند تحسين الخيارات أضف أو حسّن حقل icon بإيموجي مناسب لكل خيار إن أمكن.

Schema الحالي:
{$json}
PROMPT;
    }

    /**
     * @return array{operations: list<array<string, mixed>>, summary: string, raw: string}
     */
    protected function parseOperations(string $response): array
    {
        $raw = trim($response);
        if (preg_match('/\{.*\}/s', $raw, $m)) {
            $raw = $m[0];
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            Log::warning('AiPatchService: invalid JSON', ['response' => $response]);
            throw new RuntimeException('تعذر قراءة رد الذكاء الاصطناعي كـ JSON.');
        }

        $operations = $decoded['operations'] ?? [];
        if (! is_array($operations)) {
            $operations = [];
        }

        return [
            'summary' => (string) ($decoded['summary'] ?? 'تحسينات مقترحة'),
            'operations' => array_values(array_filter($operations, 'is_array')),
            'raw' => $response,
        ];
    }
}
