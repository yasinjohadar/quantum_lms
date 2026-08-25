<?php

namespace App\AiHtmlQuiz\Services;

use App\AiHtmlQuiz\Support\AiHtmlQuizQuestionTypes;
use App\Models\AIModel;
use App\Services\AI\AIModelService;
use App\Services\AI\AIProviderFactory;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AiHtmlQuizGenerationService
{
    public const KEEP_MARKER = '__KEEP__';

    public function __construct(
        protected AIModelService $modelService,
        protected AiHtmlQuizBundleNormalizer $normalizer
    ) {}

    /**
     * @param  list<string>  $questionTypes
     * @return array{title: string, html: string, css: string, js: string, summary: string, answer_key: array|null, model: string}
     */
    public function generate(
        string $topic,
        string $objectives = '',
        int $questionCount = 5,
        string $difficulty = 'medium',
        string $interactionHints = '',
        ?int $modelId = null,
        array $questionTypes = [],
        array $availableLibs = AiHtmlQuizBundleNormalizer::AVAILABLE_LIBS
    ): array {
        $topic = trim($topic);
        if ($topic === '') {
            throw new RuntimeException('الموضوع مطلوب.');
        }

        $questionCount = max(3, min(8, $questionCount));
        $difficulty = in_array($difficulty, ['easy', 'medium', 'hard'], true) ? $difficulty : 'medium';
        $questionTypes = AiHtmlQuizQuestionTypes::filterValid($questionTypes);
        $availableLibs = $this->normalizer->filterLibs($availableLibs);

        $model = $this->resolveModel($modelId);
        $provider = AIProviderFactory::create($model);

        $prompt = $this->buildPrompt($topic, $objectives, $questionCount, $difficulty, $interactionHints, $questionTypes, $availableLibs);
        $maxTokens = $this->resolveMaxTokens($questionCount, $model);

        $response = $provider->generateText($prompt, [
            'max_tokens' => $maxTokens,
            'temperature' => 0.45,
        ]);

        if (! $response) {
            throw new RuntimeException($provider->getLastError() ?: 'فشل استدعاء الذكاء الاصطناعي.');
        }

        $parsed = $this->parseBundle($response);

        if ($this->normalizer->hasDisallowedExternalScripts(
            (string) ($parsed['html'] ?? ''),
            (string) ($parsed['js'] ?? '')
        )) {
            throw new RuntimeException('الحزمة تحتوي سكربتات خارجية غير مسموحة.');
        }

        $normalized = $this->normalizer->normalize($parsed);

        return [
            ...$normalized,
            'model' => $model->name ?? $model->model_id ?? (string) $model->id,
        ];
    }

    /**
     * Improve an existing HTML/CSS/JS bundle using a free-form refinement prompt.
     *
     * @return array{title: string, html: string, css: string, js: string, summary: string, answer_key: array|null, model: string}
     */
    public function refine(
        string $refinePrompt,
        string $html,
        string $css,
        string $js,
        string $title = '',
        ?int $modelId = null,
        array $currentLibs = [],
        array $availableLibs = AiHtmlQuizBundleNormalizer::AVAILABLE_LIBS
    ): array {
        $refinePrompt = trim($refinePrompt);
        if ($refinePrompt === '') {
            throw new RuntimeException('اكتب تعليمات التحسين أولاً.');
        }

        if (trim($html.$css.$js) === '') {
            throw new RuntimeException('لا توجد حزمة حالية للتحسين. ولّد أولاً أو احفظ HTML/CSS/JS.');
        }

        $currentLibs = $this->normalizer->filterLibs($currentLibs);
        $availableLibs = $this->normalizer->filterLibs($availableLibs);

        $model = $this->resolveModel($modelId);
        $provider = AIProviderFactory::create($model);

        $prompt = $this->buildRefinePrompt($refinePrompt, $html, $css, $js, $title, $currentLibs, $availableLibs);
        $maxTokens = min(12000, $model->max_tokens ?: 16000);

        $response = $provider->generateText($prompt, [
            'max_tokens' => $maxTokens,
            'temperature' => 0.35,
        ]);

        if (! $response) {
            throw new RuntimeException($provider->getLastError() ?: 'فشل استدعاء الذكاء الاصطناعي.');
        }

        try {
            $parsed = $this->parseBundle($response);
        } catch (RuntimeException $e) {
            Log::warning('AiHtmlQuiz refine parse failed, attempting repair pass', [
                'error' => $e->getMessage(),
                'snippet' => mb_substr($response, 0, 400),
            ]);
            $repair = $provider->generateText(
                $this->buildRepairPrompt($response),
                ['max_tokens' => min(4000, $model->max_tokens ?: 8000), 'temperature' => 0.1]
            );
            if (! $repair) {
                throw $e;
            }
            $parsed = $this->parseBundle($repair);
        }

        $parsed = $this->applyKeepMarkers($parsed, $title, $html, $css, $js, $currentLibs);

        if ($this->normalizer->hasDisallowedExternalScripts(
            (string) ($parsed['html'] ?? ''),
            (string) ($parsed['js'] ?? '')
        )) {
            throw new RuntimeException('الحزمة المحسّنة تحتوي سكربتات خارجية غير مسموحة.');
        }

        $normalized = $this->normalizer->normalize($parsed);

        return [
            ...$normalized,
            'model' => $model->name ?? $model->model_id ?? (string) $model->id,
        ];
    }

    /**
     * ميزانية توليد صفحة اختبار HTML+CSS+JS كاملة — أثقل بكثير من توليد أسئلة
     * كنص/JSON فقط: تصميم CSS كامل + HTML لكل نوع تفاعل + منطق JS للأسئلة
     * والتحقق والانتقال والنتيجة. سقف منخفض هنا هو السبب الأكيد لانقطاع الرد
     * قبل كتابة JS إطلاقاً (حزمة "ميتة" بلا أي تفاعل).
     */
    protected function resolveMaxTokens(int $questionCount, AIModel $model): int
    {
        $needed = 6000 + ($questionCount * 1800);
        $ceiling = ((int) $model->max_tokens) > 0 ? (int) $model->max_tokens : 20000;

        return max(8000, min($needed, $ceiling));
    }

    protected function resolveModel(?int $modelId): AIModel
    {
        if ($modelId) {
            $model = AIModel::query()->active()->find($modelId);
            if ($model) {
                return $model;
            }
        }

        $model = $this->modelService->getBestModelFor('text_generation')
            ?? $this->modelService->getDefaultModel();

        if (! $model) {
            throw new RuntimeException('لا يوجد نموذج ذكاء اصطناعي نشط. أضف نموذجاً من إعدادات AI.');
        }

        return $model;
    }

    /**
     * @param  list<string>  $questionTypes
     */
    protected function buildPrompt(
        string $topic,
        string $objectives,
        int $questionCount,
        string $difficulty,
        string $interactionHints,
        array $questionTypes = [],
        array $availableLibs = AiHtmlQuizBundleNormalizer::AVAILABLE_LIBS
    ): string {
        $objectives = trim($objectives) !== '' ? trim($objectives) : 'تعزيز الفهم والتفاعل حول الموضوع المحدد بدقة';
        $hints = trim($interactionHints);
        $typesBlock = AiHtmlQuizQuestionTypes::labelsFor($questionTypes);
        if ($typesBlock === '') {
            $typesBlock = 'لم يُحدد نوع معيّن — نوّع أنماط التفاعل بحرية مع البقاء داخل الموضوع.';
        }

        $hintsLine = $hints !== ''
            ? $hints
            : 'لا تلميحات إضافية — التزم بأنواع الأسئلة المختارة والموضوع فقط.';

        $libsBlock = $this->availableLibsBlock($availableLibs);

        $soundPaths = implode("\n", [
            '- /sounds/ai-html-quiz/success-01.mp3 (إجابة صحيحة)',
            '- /sounds/ai-html-quiz/wrong-01.mp3 (إجابة خاطئة)',
            '- /sounds/ai-html-quiz/pass-01.mp3 (إنهاء ناجح)',
            '- /sounds/ai-html-quiz/retry-01.mp3 (إعادة محاولة)',
            '- /sounds/ai-html-quiz/continue-01.mp3 (انتقال)',
        ]);

        $format = $this->bundleOutputFormatInstructions();

        return <<<PROMPT
أنت مطوّر واجهات تعليمية عربي للأطفال والطلاب. أنشئ صفحة اختبار تفاعلية مستقلة بالكامل (HTML + CSS + JS فقط).

## الموضوع (إلزامي — التزم به حرفياً)
الموضوع الدقيق الذي يجب أن تدور حوله كل الأسئلة والمحتوى والنصوص والأمثلة:
«{$topic}»

قواعد دقة الموضوع:
- كل سؤال ومثال وخيار يجب أن يتعلق مباشرة بهذا الموضوع فقط.
- ممنوع الخروج لموضوعات عامة أو غير مرتبطة.
- لا تستبدل الموضوع بعنوان جذاب مختلف المعنى؛ العنوان يمكن أن يكون جذاباً لكنه يبقى عن نفس الموضوع.
- إن وُجدت أرقام/مفاهيم/نطاق في الموضوع (مثل «حتى 20» أو «الجمع») فالتزم بها في كل الأسئلة.

الأهداف التعليمية: {$objectives}
عدد الأسئلة المطلوب بالضبط تقريباً: {$questionCount} (بين 3 و 8)
الصعوبة: {$difficulty}

## أنواع الأسئلة المطلوبة (إلزامية إن حُددت)
ابنِ الأسئلة باستخدام هذه الأنواع فقط (يمكنك توزيع الأسئلة عليها؛ لا تُضف أنواعاً أخرى إن وُجدت قائمة):
{$typesBlock}

تلميحات تفاعل إضافية (اختيارية): {$hintsLine}

{$libsBlock}

متطلبات إلزامية:
1) الواجهة عربية (dir=rtl)، جذابة، ملونة، مناسبة للأطفال/الطلاب — بدون مكتبات CDN وبدون روابط خارجية (خط Alexandria يُحقن تلقائياً من النظام؛ استخدم font-family: "Alexandria", sans-serif في css).
2) اجعل التصميم قوياً: تدرجات خلفية، بطاقة مركزية بظل واضح، أزرار كبيرة بوزن خط 700–800، شريط تقدّم، وحركات بسيطة (hover/انتقال). لا تعتمد على قوالب أسئلة ثابتة جاهزة من محركات أخرى؛ ابنِ الواجهة بحرية لكن وفق أنواع الأسئلة أعلاه والموضوع الدقيق.
3) لكل تفاعل: ميّز الإجابة الصحيحة والخاطئة بصرياً وصوتياً.
4) الأصوات: استخدم عناصر <audio> أو Audio() مع المسارات التالية فقط (نسبية من أصل الموقع):
{$soundPaths}
   يمكنك أيضاً استخدام WebAudio بسيط كنغمة احتياطية إن تعذر التحميل.
5) عند انتهاء الاختبار بالكامل، استدعِ حتماً:
   window.parent.postMessage({
     type: "ile-html-quiz-result",
     payload: {
       score: <number>,
       total: <number>,
       percentage: <number 0-100>,
       answers: [ { id, correct: true|false, type?: string, ... } ],
       durationSeconds: <number>
     }
   }, "*");
6) لا تُضمّن <html>/<head>/<body> في html — أعطِ محتوى الجسم فقط. ضع الأنماط في css والسكربت في js.
7) امنع fetch/XHR لأي مصدر خارجي. لا script src خارجي — المكتبات المحلية أعلاه فقط عبر اختيارها بالاسم في AHQ_LIBS، لا عبر script src.

{$format}
PROMPT;
    }

    /**
     * @param  list<string>  $availableLibs
     */
    protected function availableLibsBlock(array $availableLibs): string
    {
        if ($availableLibs === []) {
            return "## مكتبات محلية متاحة\nلا توجد مكتبات متاحة لهذا الاختبار — لا تطلب أي مكتبة، واكتب AHQ_LIBS بقيمة none.";
        }

        $catalog = [
            'chart' => 'chart — Chart.js (متاح كـ Chart عالمياً). للرسوم البيانية/الإحصاءات/الأعداد المقارنة: new Chart(ctx, {type:"bar"|"line"|"pie", data:{...}}).',
            'confetti' => 'confetti — canvas-confetti (متاح كـ confetti عالمياً). احتفال بصري بسيط عند نجاح/انتهاء الاختبار: confetti().',
            'katex' => 'katex — KaTeX (متاح كـ katex عالمياً). لعرض معادلات/رموز رياضية بشكل جميل: katex.render("x^2+1", element).',
            'mermaid' => 'mermaid — Mermaid (متاح كـ mermaid عالمياً). لرسم مخططات/تسلسلات/خطوات عملية: ضع كود المخطط داخل <div class="mermaid">...</div> ثم نادِ mermaid.initialize({startOnLoad:true}) أو mermaid.run().',
        ];

        $lines = array_map(fn ($key) => '- '.$catalog[$key], $availableLibs);

        return "## مكتبات محلية متاحة (اختيارية — اختر منها فقط ما يخدم الموضوع فعلياً، لا تفرضها تعسفاً)\n"
            .implode("\n", $lines)
            ."\n\nهذه المكتبات تُحمَّل تلقائياً من الخادم (محلية بالكامل، بلا أي طلب شبكة خارجي) إن ذكرت مفتاحها في AHQ_LIBS فقط. "
            ."لا تكتب أنت أي <script src> أو <link> لتحميلها — أي محاولة كهذه سيتم حذفها/رفضها، والطريقة الوحيدة الفعّالة هي ذكر المفتاح في AHQ_LIBS ثم استخدام الكائن العالمي مباشرة في js.";
    }

    /**
     * @param  list<string>  $currentLibs
     * @param  list<string>  $availableLibs
     */
    protected function buildRefinePrompt(
        string $refinePrompt,
        string $html,
        string $css,
        string $js,
        string $title,
        array $currentLibs = [],
        array $availableLibs = AiHtmlQuizBundleNormalizer::AVAILABLE_LIBS
    ): string {
        $title = trim($title) !== '' ? trim($title) : 'اختبار تفاعلي';
        $html = mb_substr($html, 0, 24000);
        $css = mb_substr($css, 0, 16000);
        $js = mb_substr($js, 0, 24000);
        $keep = self::KEEP_MARKER;
        $format = $this->bundleOutputFormatInstructions(true);
        $libsBlock = $this->availableLibsBlock($availableLibs);
        $currentLibsLine = $currentLibs === [] ? 'لا توجد مكتبة مُفعّلة حالياً.' : implode(', ', $currentLibs);

        return <<<PROMPT
أنت مطوّر واجهات تعليمية عربي. حسّن حزمة اختبار HTML/CSS/JS موجودة حسب طلب الأدمن بدقة.

## طلب التحسين (إلزامي — نفّذه)
«{$refinePrompt}»

## قيود تقنية صارمة
- خط Alexandria متاح تلقائياً: استخدم font-family: "Alexandria", sans-serif.
- ممنوع CDN ومكتبات خارجية وscript src خارجي.
- حافظ على منطق الإجابات و postMessage من نوع ile-html-quiz-result.
- الأصوات فقط تحت /sounds/ai-html-quiz/
- لا تُرجع <html>/<head>/<body> داخل html.

{$libsBlock}

المكتبات المُفعّلة حالياً لهذا الاختبار: {$currentLibsLine}
إن لم يطلب التحسين تغييرها، ضع بالضبط {$keep} في AHQ_LIBS لإبقائها كما هي؛ وإلا اكتب القائمة الجديدة الكاملة.

## مهم لتوفير الحجم
- إن لم يتغير قسم معيّن، ضع بالضبط: {$keep}
- لتغيير لون/خط/مظهر فقط: غالباً يكفي تعديل CSS وأعد HTML/JS كـ {$keep}
- لا تُرجع شرحاً خارج صيغة الإخراج.

## الحزمة الحالية
title: {$title}

### html
{$html}

### css
{$css}

### js
{$js}

{$format}
PROMPT;
    }

    protected function buildRepairPrompt(string $brokenResponse): string
    {
        $snippet = mb_substr(trim($brokenResponse), 0, 12000);
        $format = $this->bundleOutputFormatInstructions(true);

        return <<<PROMPT
حوّل الرد التالي إلى صيغة الحزمة الصحيحة فقط بدون شرح.
إن نقص html أو css أو js فضع __KEEP__ لذلك القسم.

الرد السابق:
{$snippet}

{$format}
PROMPT;
    }

    protected function bundleOutputFormatInstructions(bool $allowKeep = false): string
    {
        $keepNote = $allowKeep
            ? "يمكنك وضع ".self::KEEP_MARKER." داخل أي قسم لم يتغيّر.\n"
            : '';

        return <<<FORMAT
## صيغة الإخراج (إلزامية — مفضّلة)
أرجع بالفواصل التالية فقط (بدون markdown، بدون JSON إن أمكن):
<<<AHQ_TITLE>>>
عنوان قصير
<<<AHQ_HTML>>>
محتوى الجسم فقط
<<<AHQ_CSS>>>
الأنماط
<<<AHQ_JS>>>
السكربت
<<<AHQ_LIBS>>>
قائمة مفصولة بفواصل من المفاتيح المستخدمة فقط (مثل: chart,confetti) أو none إن لم تُستخدم أي مكتبة
<<<AHQ_SUMMARY>>>
ملخص قصير
<<<AHQ_END>>>
{$keepNote}
بديل مقبول: JSON واحد صالح بالمفاتيح title,html,css,js,libs,summary — مع تهريب صحيح للعلامات المزدوجة.
FORMAT;
    }

    /**
     * @param  array<string, mixed>  $parsed
     * @return array{title: mixed, html: mixed, css: mixed, js: mixed, summary?: mixed, answer_key?: mixed}
     */
    protected function applyKeepMarkers(array $parsed, string $title, string $html, string $css, string $js, array $currentLibs = []): array
    {
        $parsed['title'] = $this->resolveKeep((string) ($parsed['title'] ?? ''), $title !== '' ? $title : 'اختبار تفاعلي');
        $parsed['html'] = $this->resolveKeep((string) ($parsed['html'] ?? ''), $html);
        $parsed['css'] = $this->resolveKeep((string) ($parsed['css'] ?? ''), $css);
        $parsed['js'] = $this->resolveKeep((string) ($parsed['js'] ?? ''), $js);

        $rawLibs = $parsed['libs'] ?? '';
        $libsValue = is_array($rawLibs) ? implode(',', $rawLibs) : (string) $rawLibs;
        $parsed['libs'] = trim($libsValue) === '' || trim($libsValue) === self::KEEP_MARKER
            ? $currentLibs
            : $rawLibs;

        return $parsed;
    }

    protected function resolveKeep(string $value, string $fallback): string
    {
        $trimmed = trim($value);
        if ($trimmed === '' || $trimmed === self::KEEP_MARKER) {
            return $fallback;
        }

        return $value;
    }

    /**
     * @return array{title?: mixed, html?: mixed, css?: mixed, js?: mixed, summary?: mixed, answer_key?: mixed}
     */
    public function parseBundle(string $response): array
    {
        $raw = trim($response);
        if ($raw === '') {
            throw new RuntimeException('رد الذكاء الاصطناعي فارغ.');
        }

        // Strip outer markdown fence if present.
        if (preg_match('/^```(?:json|html|text)?\s*([\s\S]*?)\s*```$/i', $raw, $fence)) {
            $raw = trim($fence[1]);
        }

        $fromMarkers = $this->parseDelimitedBundle($raw);
        if ($fromMarkers !== null) {
            return $fromMarkers;
        }

        $fromJson = $this->parseJsonBundle($raw);
        if ($fromJson !== null) {
            return $fromJson;
        }

        // Sometimes model wraps markers inside prose — search whole response.
        $fromMarkers = $this->parseDelimitedBundle($response);
        if ($fromMarkers !== null) {
            return $fromMarkers;
        }

        Log::warning('AiHtmlQuizGenerationService: invalid bundle response', [
            'snippet' => mb_substr($response, 0, 800),
            'json_error' => json_last_error_msg(),
        ]);

        throw new RuntimeException('تعذر قراءة رد الذكاء الاصطناعي. أعد المحاولة أو قصّر برومبت التحسين.');
    }

    /**
     * @return array{title?: string, html?: string, css?: string, js?: string, summary?: string}|null
     */
    protected function parseDelimitedBundle(string $raw): ?array
    {
        if (! str_contains($raw, '<<<AHQ_') && ! str_contains($raw, '<<<AHQ_HTML>>>')) {
            // still try if any marker exists
            if (! preg_match('/<<<\s*AHQ_(TITLE|HTML|CSS|JS|LIBS|SUMMARY)\s*>>>/i', $raw)) {
                return null;
            }
        }

        $get = function (string $name) use ($raw): ?string {
            $pattern = '/<<<\s*AHQ_'.preg_quote($name, '/').'\s*>>>\s*([\s\S]*?)(?=<<<\s*AHQ_|\z)/i';
            if (! preg_match($pattern, $raw, $m)) {
                return null;
            }

            return trim($m[1]);
        };

        $html = $get('HTML');
        $css = $get('CSS');
        $js = $get('JS');

        if ($html === null && $css === null && $js === null) {
            return null;
        }

        return [
            'title' => $get('TITLE') ?? '',
            'html' => $html ?? '',
            'css' => $css ?? '',
            'js' => $js ?? '',
            'libs' => $get('LIBS') ?? '',
            'summary' => $get('SUMMARY') ?? '',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function parseJsonBundle(string $raw): ?array
    {
        $candidates = [$raw];

        if (preg_match('/\{[\s\S]*\}/', $raw, $m)) {
            $candidates[] = $m[0];
        }

        // Extract fenced json blocks.
        if (preg_match_all('/```(?:json)?\s*([\s\S]*?)```/i', $raw, $blocks)) {
            foreach ($blocks[1] as $block) {
                $candidates[] = trim($block);
            }
        }

        foreach ($candidates as $candidate) {
            $decoded = json_decode($candidate, true);
            if (is_array($decoded) && $this->looksLikeBundle($decoded)) {
                return $decoded;
            }

            // Fix common invalid control characters inside strings.
            $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', ' ', $candidate) ?? $candidate;
            $decoded = json_decode($cleaned, true);
            if (is_array($decoded) && $this->looksLikeBundle($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $decoded
     */
    protected function looksLikeBundle(array $decoded): bool
    {
        return isset($decoded['html']) || isset($decoded['css']) || isset($decoded['js']);
    }
}
