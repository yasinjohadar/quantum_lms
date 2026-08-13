<?php

namespace App\InteractiveLearning\Services;

use App\InteractiveLearning\Support\FeedbackPhrases;
use App\InteractiveLearning\Support\QuestionTypeRegistry;
use Illuminate\Support\Str;

class SchemaValidator
{
    public const SCHEMA_VERSION = '1.0';

    public const SCHEMA_VERSION_DYNAMIC = '2.0';

    public const ENGINE_VERSION = '1.1';

    /** @var list<string> */
    public const ALLOWED_LIBRARIES = ['katex', 'icons', 'stickers', 'lottie', 'tts'];

    /** @var list<string> */
    public const ALLOWED_BLOCKS = ['text', 'math', 'icon', 'sticker', 'image', 'audio', 'scene'];

    /**
     * Types with a native "dynamic poster" layout (frontend DynamicInteraction.js).
     * All other registered types are still allowed in dynamic-mode schemas — they
     * just render via the classic module UI beneath the rich stemBlocks content.
     *
     * @var list<string>
     */
    public const DYNAMIC_INTERACTION_TYPES = [
        'true_false',
        'single_choice',
        'multiple_choice',
        'numerical',
        'short_answer',
        'listen_choose',
    ];

    /**
     * @param  array<string, mixed>  $schema
     */
    public function resolveMode(array $schema): string
    {
        $mode = (string) ($schema['mode'] ?? '');
        if ($mode === 'dynamic' || ($schema['version'] ?? null) === self::SCHEMA_VERSION_DYNAMIC) {
            return 'dynamic';
        }

        return 'classic';
    }

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate(array $schema): array
    {
        $mode = $this->resolveMode($schema);

        return $mode === 'dynamic'
            ? $this->validateDynamic($schema)
            : $this->validateClassic($schema);
    }

    /**
     * @param  array<string, mixed>  $schema
     * @return array{valid: bool, errors: list<string>}
     */
    protected function validateClassic(array $schema): array
    {
        $errors = [];

        if (($schema['version'] ?? null) !== self::SCHEMA_VERSION) {
            $errors[] = 'إصدار Schema غير مدعوم. المتوقع: '.self::SCHEMA_VERSION;
        }

        $errors = array_merge($errors, $this->validateMetaAndQuestionsRoot($schema));

        if (isset($schema['questions']) && is_array($schema['questions'])) {
            foreach ($schema['questions'] as $index => $question) {
                $errors = array_merge($errors, $this->validateClassicQuestion($question, $index));
            }
        }

        return [
            'valid' => $errors === [],
            'errors' => $errors,
        ];
    }

    /**
     * @param  array<string, mixed>  $schema
     * @return array{valid: bool, errors: list<string>}
     */
    protected function validateDynamic(array $schema): array
    {
        $errors = [];

        if (($schema['version'] ?? null) !== self::SCHEMA_VERSION_DYNAMIC) {
            $errors[] = 'إصدار Schema الديناميك غير مدعوم. المتوقع: '.self::SCHEMA_VERSION_DYNAMIC;
        }

        if (($schema['mode'] ?? null) !== 'dynamic') {
            $errors[] = 'حقل mode يجب أن يكون dynamic.';
        }

        $errors = array_merge($errors, $this->validateMetaAndQuestionsRoot($schema));
        $errors = array_merge($errors, $this->validateLibraries($schema['assets']['libraries'] ?? null, 'assets.libraries'));

        if (isset($schema['questions']) && is_array($schema['questions'])) {
            foreach ($schema['questions'] as $index => $question) {
                $errors = array_merge($errors, $this->validateDynamicQuestion($question, $index));
            }
        }

        return [
            'valid' => $errors === [],
            'errors' => $errors,
        ];
    }

    /**
     * @param  array<string, mixed>  $schema
     * @return list<string>
     */
    protected function validateMetaAndQuestionsRoot(array $schema): array
    {
        $errors = [];

        if (! isset($schema['meta']) || ! is_array($schema['meta'])) {
            $errors[] = 'حقل meta مطلوب.';
        } elseif (trim((string) ($schema['meta']['title'] ?? '')) === '') {
            $errors[] = 'عنوان الجلسة (meta.title) مطلوب.';
        }

        if (! isset($schema['questions']) || ! is_array($schema['questions'])) {
            $errors[] = 'قائمة questions مطلوبة.';
        } elseif (count($schema['questions']) < 1) {
            $errors[] = 'يجب إضافة سؤال واحد على الأقل.';
        }

        return $errors;
    }

    /**
     * @param  mixed  $libraries
     * @return list<string>
     */
    protected function validateLibraries(mixed $libraries, string $label): array
    {
        if ($libraries === null) {
            return [];
        }
        if (! is_array($libraries)) {
            return ["{$label}: يجب أن تكون مصفوفة."];
        }

        $errors = [];
        foreach ($libraries as $lib) {
            if (! is_string($lib) || ! in_array($lib, self::ALLOWED_LIBRARIES, true)) {
                $errors[] = "{$label}: مكتبة غير معتمدة ({$lib}).";
            }
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    protected function validateClassicQuestion(mixed $question, int $index): array
    {
        $errors = [];
        $label = 'السؤال #'.($index + 1);

        if (! is_array($question)) {
            return ["{$label}: بنية غير صالحة."];
        }

        if (trim((string) ($question['id'] ?? '')) === '') {
            $errors[] = "{$label}: المعرف id مطلوب.";
        }

        $type = (string) ($question['type'] ?? '');
        if (! QuestionTypeRegistry::has($type)) {
            $errors[] = "{$label}: نوع غير مدعوم ({$type}).";
        }

        if (trim((string) ($question['stem'] ?? '')) === '') {
            $errors[] = "{$label}: نص السؤال مطلوب.";
        }

        $points = $question['points'] ?? 1;
        if (! is_numeric($points) || (float) $points < 0) {
            $errors[] = "{$label}: النقاط غير صالحة.";
        }

        $payload = $question['payload'] ?? null;
        if (! is_array($payload)) {
            $errors[] = "{$label}: payload مطلوب.";
        } elseif ($type !== '') {
            $errors = array_merge($errors, $this->validatePayload($type, $payload, $label));
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    protected function validateDynamicQuestion(mixed $question, int $index): array
    {
        $errors = [];
        $label = 'السؤال #'.($index + 1);

        if (! is_array($question)) {
            return ["{$label}: بنية غير صالحة."];
        }

        if (trim((string) ($question['id'] ?? '')) === '') {
            $errors[] = "{$label}: المعرف id مطلوب.";
        }

        $blocks = $question['stemBlocks'] ?? null;
        if (! is_array($blocks) || $blocks === []) {
            $errors[] = "{$label}: stemBlocks مطلوبة وغير فارغة.";
        } else {
            foreach ($blocks as $bi => $block) {
                $errors = array_merge($errors, $this->validateBlock($block, "{$label} كتلة #".($bi + 1)));
            }
        }

        $interaction = $question['interaction'] ?? null;
        if (! is_array($interaction)) {
            $errors[] = "{$label}: interaction مطلوب.";
        } else {
            $type = (string) ($interaction['type'] ?? '');
            if (! QuestionTypeRegistry::has($type)) {
                $errors[] = "{$label}: نوع تفاعل غير مدعوم في الديناميك ({$type}).";
            }
            $payload = $interaction['payload'] ?? null;
            if (! is_array($payload)) {
                $errors[] = "{$label}: interaction.payload مطلوب.";
            } elseif ($type !== '') {
                $errors = array_merge($errors, $this->validatePayload($type, $payload, $label));
            }
        }

        $points = $question['points'] ?? 1;
        if (! is_numeric($points) || (float) $points < 0) {
            $errors[] = "{$label}: النقاط غير صالحة.";
        }

        if (isset($question['assets']['libraries'])) {
            $errors = array_merge($errors, $this->validateLibraries($question['assets']['libraries'], "{$label} assets.libraries"));
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    protected function validateBlock(mixed $block, string $label): array
    {
        if (! is_array($block)) {
            return ["{$label}: بنية غير صالحة."];
        }

        $type = (string) ($block['type'] ?? '');
        if (! in_array($type, self::ALLOWED_BLOCKS, true)) {
            return ["{$label}: نوع كتلة غير معتمد ({$type})."];
        }

        return match ($type) {
            'text' => trim((string) ($block['text'] ?? '')) === ''
                ? ["{$label}: text مطلوب."]
                : [],
            'math' => trim((string) ($block['latex'] ?? '')) === ''
                ? ["{$label}: latex مطلوب."]
                : [],
            'icon' => trim((string) ($block['name'] ?? '')) === ''
                ? ["{$label}: name مطلوب."]
                : [],
            'sticker' => trim((string) ($block['name'] ?? '')) === ''
                ? ["{$label}: name مطلوب."]
                : [],
            'image' => trim((string) ($block['url'] ?? '')) === ''
                ? ["{$label}: url مطلوب."]
                : [],
            'audio' => (trim((string) ($block['text'] ?? '')) === '' && trim((string) ($block['audioUrl'] ?? '')) === '')
                ? ["{$label}: text أو audioUrl مطلوب."]
                : [],
            'scene' => $this->validateSceneBlock($block, $label),
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $block
     * @return list<string>
     */
    protected function validateSceneBlock(array $block, string $label): array
    {
        $errors = [];
        if (trim((string) ($block['item'] ?? '')) === '') {
            $errors[] = "{$label}: item مطلوب.";
        }
        if (! isset($block['count']) || ! is_numeric($block['count']) || (int) $block['count'] < 0) {
            $errors[] = "{$label}: count يجب أن يكون عدداً ≥ 0.";
        }

        return $errors;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<string>
     */
    protected function validatePayload(string $type, array $payload, string $label): array
    {
        return match ($type) {
            'true_false' => isset($payload['correct']) && is_bool($payload['correct'])
                ? []
                : ["{$label}: correct (boolean) مطلوب."],
            'single_choice' => $this->validateChoicePayload($payload, $label, false),
            'multiple_choice' => $this->validateChoicePayload($payload, $label, true),
            'drag_drop' => $this->validateDragDropPayload($payload, $label),
            'matching', 'connect_lines', 'memory_cards' => $this->validateMatchingPayload($payload, $label),
            'ordering', 'puzzle_pieces' => $this->validateOrderingPayload($payload, $label, $type),
            'fill_blank' => $this->validateFillBlankPayload($payload, $label),
            'categorize' => $this->validateCategorizePayload($payload, $label),
            'listen_choose' => $this->validateChoicePayload($payload, $label, false),
            'hotspot' => $this->validateHotspotPayload($payload, $label),
            'numerical' => $this->validateNumericalPayload($payload, $label),
            'short_answer' => $this->validateShortAnswerPayload($payload, $label),
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<string>
     */
    protected function validateOrderingPayload(array $payload, string $label, string $type): array
    {
        $errors = [];
        $key = $type === 'puzzle_pieces' ? 'pieces' : 'items';
        $items = $payload[$key] ?? null;
        if (! is_array($items) || count($items) < 2) {
            $errors[] = "{$label}: يلزم عنصران على الأقل.";
        }
        $order = $payload['correctOrder'] ?? null;
        if (! is_array($order) || count($order) < 2) {
            $errors[] = "{$label}: correctOrder مطلوبة.";
        }

        return $errors;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<string>
     */
    protected function validateFillBlankPayload(array $payload, string $label): array
    {
        $errors = [];
        if (trim((string) ($payload['template'] ?? '')) === '') {
            $errors[] = "{$label}: template مطلوب.";
        }
        $mode = $payload['mode'] ?? 'choice';
        if ($mode === 'text') {
            if (trim((string) ($payload['correct'] ?? '')) === '' && empty($payload['acceptedAnswers'])) {
                $errors[] = "{$label}: correct أو acceptedAnswers مطلوبة.";
            }
        } else {
            $errors = array_merge($errors, $this->validateChoicePayload([
                'options' => $payload['options'] ?? [],
                'correctId' => $payload['correct'] ?? ($payload['correctId'] ?? ''),
            ], $label, false));
        }

        return $errors;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<string>
     */
    protected function validateCategorizePayload(array $payload, string $label): array
    {
        $errors = [];
        if (! is_array($payload['items'] ?? null) || count($payload['items']) < 1) {
            $errors[] = "{$label}: items مطلوبة.";
        }
        if (! is_array($payload['categories'] ?? null) || count($payload['categories']) < 2) {
            $errors[] = "{$label}: يلزم تصنيفان على الأقل.";
        }
        if (! is_array($payload['correct'] ?? null) || ($payload['correct'] ?? []) === []) {
            $errors[] = "{$label}: correct مطلوبة.";
        }

        return $errors;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<string>
     */
    protected function validateHotspotPayload(array $payload, string $label): array
    {
        $errors = [];
        if (! is_array($payload['spots'] ?? null) || count($payload['spots']) < 2) {
            $errors[] = "{$label}: يلزم منطقتان على الأقل.";
        }
        if (trim((string) ($payload['correctId'] ?? '')) === '') {
            $errors[] = "{$label}: correctId مطلوب.";
        }

        return $errors;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<string>
     */
    protected function validateNumericalPayload(array $payload, string $label): array
    {
        if (! isset($payload['correct']) || ! is_numeric($payload['correct'])) {
            return ["{$label}: correct (رقم) مطلوب."];
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<string>
     */
    protected function validateShortAnswerPayload(array $payload, string $label): array
    {
        $hasCorrect = trim((string) ($payload['correct'] ?? '')) !== '';
        $hasAccepted = is_array($payload['acceptedAnswers'] ?? null) && ($payload['acceptedAnswers'] ?? []) !== [];
        if (! $hasCorrect && ! $hasAccepted) {
            return ["{$label}: correct أو acceptedAnswers مطلوبة."];
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<string>
     */
    protected function validateChoicePayload(array $payload, string $label, bool $multiple): array
    {
        $errors = [];
        $options = $payload['options'] ?? null;
        if (! is_array($options) || count($options) < 2) {
            $errors[] = "{$label}: يلزم خياران على الأقل.";
        } else {
            foreach ($options as $oi => $opt) {
                $errors = array_merge($errors, $this->validateMediaFields($opt, "{$label} خيار #".($oi + 1)));
            }
        }

        if ($multiple) {
            $ids = $payload['correctIds'] ?? null;
            if (! is_array($ids) || $ids === []) {
                $errors[] = "{$label}: correctIds مطلوبة.";
            }
        } elseif (trim((string) ($payload['correctId'] ?? '')) === '') {
            $errors[] = "{$label}: correctId مطلوب.";
        }

        return $errors;
    }

    /**
     * @param  mixed  $item
     * @return list<string>
     */
    protected function validateMediaFields(mixed $item, string $label): array
    {
        if (! is_array($item)) {
            return [];
        }

        $errors = [];
        foreach (['icon', 'imageUrl', 'audioUrl'] as $field) {
            if (! array_key_exists($field, $item) || $item[$field] === null || $item[$field] === '') {
                continue;
            }
            if (! is_string($item[$field])) {
                $errors[] = "{$label}: {$field} يجب أن يكون نصاً.";
            }
        }

        return $errors;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<string>
     */
    protected function validateDragDropPayload(array $payload, string $label): array
    {
        $errors = [];
        $items = $payload['items'] ?? null;
        $zones = $payload['zones'] ?? null;
        $assignments = $payload['assignments'] ?? null;

        if (! is_array($items) || count($items) < 1) {
            $errors[] = "{$label}: items مطلوبة.";
        } else {
            foreach ($items as $ii => $item) {
                $errors = array_merge($errors, $this->validateMediaFields($item, "{$label} عنصر #".($ii + 1)));
            }
        }
        if (! is_array($zones) || count($zones) < 1) {
            $errors[] = "{$label}: zones مطلوبة.";
        } else {
            foreach ($zones as $zi => $zone) {
                $errors = array_merge($errors, $this->validateMediaFields($zone, "{$label} منطقة #".($zi + 1)));
            }
        }
        if (! is_array($assignments) || $assignments === []) {
            $errors[] = "{$label}: assignments مطلوبة.";
        }

        return $errors;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<string>
     */
    protected function validateMatchingPayload(array $payload, string $label): array
    {
        $errors = [];
        if (! is_array($payload['left'] ?? null) || count($payload['left']) < 1) {
            $errors[] = "{$label}: left مطلوبة.";
        } else {
            foreach ($payload['left'] as $li => $left) {
                $errors = array_merge($errors, $this->validateMediaFields($left, "{$label} يسار #".($li + 1)));
            }
        }
        if (! is_array($payload['right'] ?? null) || count($payload['right']) < 1) {
            $errors[] = "{$label}: right مطلوبة.";
        } else {
            foreach ($payload['right'] as $ri => $right) {
                $errors = array_merge($errors, $this->validateMediaFields($right, "{$label} يمين #".($ri + 1)));
            }
        }
        if (! is_array($payload['pairs'] ?? null) || ($payload['pairs'] ?? []) === []) {
            $errors[] = "{$label}: pairs مطلوبة.";
        }

        return $errors;
    }

    /**
     * @return array<string, mixed>
     */
    public function emptySchema(string $title = 'تجربة تعليمية جديدة', string $mode = 'classic'): array
    {
        $mode = $mode === 'dynamic' ? 'dynamic' : 'classic';

        $schema = [
            'version' => $mode === 'dynamic' ? self::SCHEMA_VERSION_DYNAMIC : self::SCHEMA_VERSION,
            'mode' => $mode,
            'meta' => [
                'title' => $title,
                'locale' => 'ar',
                'rtl' => true,
            ],
            'theme' => [
                'themeId' => 'kids',
                'accent' => '#22c55e',
                'font' => 'system',
                'density' => 'comfortable',
                'motion' => 'full',
                'mode' => 'light',
            ],
            'rules' => [
                'allowBack' => true,
                'shuffleQuestions' => false,
                'maxWrong' => null,
                'showExplanation' => true,
                'attemptsPerQuestion' => 1,
                'timerSeconds' => null,
            ],
            'messages' => [
                'success' => [
                    'يا بطل! ⭐',
                    'أحسنت يا شاطر!',
                    'ممتاز جداً!',
                    'أنت نجم!',
                    'وووو رائع!',
                    'صح! براڤو!',
                    'عاش! أنت مبدع',
                    'تسلم إيدك!',
                    'ذكي جداً!',
                ],
                'error' => [
                    'جرّب مرة ثانية!',
                    'أنت قادر!',
                    'فكّر بهدوء…',
                    'لا بأس، جرّب!',
                    'قرّبنا… حاول!',
                ],
                'encourage' => [
                    'هيا بنا يا بطل!',
                    'أنت تقترب!',
                    'واصل يا شاطر',
                    'يلا نكمّل!',
                ],
            ],
            'questions' => [],
        ];

        if ($mode === 'dynamic') {
            $schema['assets'] = [
                'libraries' => ['katex', 'icons', 'stickers', 'lottie', 'tts'],
            ];
        }

        return $schema;
    }

    /**
     * @return array<string, mixed>
     */
    public function makeBlankDynamicQuestion(string $interactionType = 'single_choice'): array
    {
        if (! QuestionTypeRegistry::has($interactionType)) {
            $interactionType = 'single_choice';
        }

        $classic = $this->makeBlankQuestion($interactionType);
        $stem = (string) ($classic['stem'] ?? 'اكتب نص السؤال…');

        return [
            'id' => (string) Str::uuid(),
            'stem' => $stem,
            'stemBlocks' => [
                ['type' => 'text', 'text' => $stem],
            ],
            'interaction' => [
                'type' => $interactionType,
                'payload' => $classic['payload'],
            ],
            'optionBlocks' => [],
            'assets' => [
                'libraries' => ['stickers', 'tts'],
            ],
            'points' => $classic['points'] ?? 1,
            'difficulty' => $classic['difficulty'] ?? 'medium',
            'hints' => [],
            'explanation' => '',
            'successMessage' => $classic['successMessage'] ?? FeedbackPhrases::texts(FeedbackPhrases::KIND_SUCCESS)[0],
            'errorMessage' => $classic['errorMessage'] ?? FeedbackPhrases::texts(FeedbackPhrases::KIND_FAIL)[0],
            'estimatedSeconds' => $classic['estimatedSeconds'] ?? 30,
            'tags' => [],
            'learningObjectives' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function makeBlankQuestion(string $type): array
    {
        $id = (string) Str::uuid();

        $base = [
            'id' => $id,
            'type' => $type,
            'stem' => match ($type) {
                'true_false' => 'اكتب عبارة صح/خطأ هنا…',
                'single_choice' => 'اكتب سؤال الاختيار الواحد هنا…',
                'multiple_choice' => 'اكتب سؤال الاختيار المتعدد هنا…',
                'drag_drop' => 'اكتب تعليمات السحب والإفلات هنا…',
                'matching' => 'اكتب تعليمات المطابقة هنا…',
                'ordering' => 'رتّب العناصر بالترتيب الصحيح…',
                'fill_blank' => 'أكمل الجملة بالكلمة المناسبة…',
                'categorize' => 'صنّف العناصر في المجموعات الصحيحة…',
                'listen_choose' => 'استمع ثم اختر الإجابة الصحيحة…',
                'connect_lines' => 'صل كل عنصر بما يناسبه…',
                'memory_cards' => 'اطابق البطاقات المتشابهة…',
                'hotspot' => 'اضغط على المكان الصحيح…',
                'puzzle_pieces' => 'ضع القطع في الترتيب الصحيح…',
                'numerical' => 'ما هو الناتج؟',
                'short_answer' => 'اكتب الإجابة القصيرة…',
                default => 'اكتب نص السؤال هنا…',
            },
            'points' => 1,
            'difficulty' => 'medium',
            'hints' => [],
            'explanation' => '',
            // القيم الافتراضية من قائمة العبارات المسجّلة صوتياً (FeedbackPhrases)
            'successMessage' => FeedbackPhrases::texts(FeedbackPhrases::KIND_SUCCESS)[0],
            'errorMessage' => FeedbackPhrases::texts(FeedbackPhrases::KIND_FAIL)[0],
            'estimatedSeconds' => 30,
            'tags' => [],
            'learningObjectives' => [],
        ];

        $base['payload'] = match ($type) {
            'true_false' => ['correct' => true],
            'single_choice' => [
                'options' => [
                    ['id' => 'a', 'label' => 'خيار أ', 'icon' => '⭐', 'imageUrl' => null, 'audioUrl' => null],
                    ['id' => 'b', 'label' => 'خيار ب', 'icon' => '🌟', 'imageUrl' => null, 'audioUrl' => null],
                ],
                'correctId' => 'a',
            ],
            'multiple_choice' => [
                'options' => [
                    ['id' => 'a', 'label' => 'خيار أ', 'icon' => '🍎', 'imageUrl' => null, 'audioUrl' => null],
                    ['id' => 'b', 'label' => 'خيار ب', 'icon' => '🍌', 'imageUrl' => null, 'audioUrl' => null],
                    ['id' => 'c', 'label' => 'خيار ج', 'icon' => '🍇', 'imageUrl' => null, 'audioUrl' => null],
                ],
                'correctIds' => ['a'],
            ],
            'drag_drop' => [
                'items' => [
                    ['id' => 'i1', 'label' => 'عنصر 1', 'icon' => '🧩'],
                    ['id' => 'i2', 'label' => 'عنصر 2', 'icon' => '🎯'],
                ],
                'zones' => [
                    ['id' => 'z1', 'label' => 'منطقة 1', 'icon' => '📦'],
                    ['id' => 'z2', 'label' => 'منطقة 2', 'icon' => '🧺'],
                ],
                'assignments' => ['i1' => 'z1', 'i2' => 'z2'],
            ],
            'matching' => [
                'left' => [
                    ['id' => 'l1', 'label' => 'يسار 1', 'icon' => '🐱'],
                    ['id' => 'l2', 'label' => 'يسار 2', 'icon' => '🦁'],
                ],
                'right' => [
                    ['id' => 'r1', 'label' => 'يمين 1', 'icon' => '1️⃣'],
                    ['id' => 'r2', 'label' => 'يمين 2', 'icon' => '2️⃣'],
                ],
                'pairs' => ['l1' => 'r1', 'l2' => 'r2'],
            ],
            'ordering' => [
                'items' => [
                    ['id' => 'a', 'label' => 'أولاً', 'icon' => '1️⃣'],
                    ['id' => 'b', 'label' => 'ثانياً', 'icon' => '2️⃣'],
                    ['id' => 'c', 'label' => 'ثالثاً', 'icon' => '3️⃣'],
                ],
                'correctOrder' => ['a', 'b', 'c'],
            ],
            'fill_blank' => [
                'template' => 'الماء ___',
                'mode' => 'choice',
                'options' => [
                    ['id' => 'a', 'label' => 'سائل', 'icon' => '💧'],
                    ['id' => 'b', 'label' => 'نار', 'icon' => '🔥'],
                ],
                'correct' => 'a',
            ],
            'categorize' => [
                'items' => [
                    ['id' => 'i1', 'label' => 'قطة', 'icon' => '🐱'],
                    ['id' => 'i2', 'label' => 'تفاحة', 'icon' => '🍎'],
                ],
                'categories' => [
                    ['id' => 'c1', 'label' => 'حيوانات', 'icon' => '🐾'],
                    ['id' => 'c2', 'label' => 'طعام', 'icon' => '🍽️'],
                ],
                'correct' => ['i1' => 'c1', 'i2' => 'c2'],
            ],
            'listen_choose' => [
                'prompt' => ['label' => 'استمع', 'text' => 'سبعة', 'icon' => '🎧', 'audioUrl' => null],
                'options' => [
                    ['id' => 'a', 'label' => 'أسد', 'icon' => '🦁'],
                    ['id' => 'b', 'label' => 'قط', 'icon' => '🐱'],
                ],
                'correctId' => 'a',
            ],
            'connect_lines' => [
                'left' => [
                    ['id' => 'l1', 'label' => 'شمس', 'icon' => '☀️'],
                    ['id' => 'l2', 'label' => 'قمر', 'icon' => '🌙'],
                ],
                'right' => [
                    ['id' => 'r1', 'label' => 'نهار', 'icon' => '🌤️'],
                    ['id' => 'r2', 'label' => 'ليل', 'icon' => '🌃'],
                ],
                'pairs' => ['l1' => 'r1', 'l2' => 'r2'],
            ],
            'memory_cards' => [
                'left' => [
                    ['id' => 'l1', 'label' => 'قطة', 'icon' => '🐱'],
                    ['id' => 'l2', 'label' => 'كلب', 'icon' => '🐶'],
                ],
                'right' => [
                    ['id' => 'r1', 'label' => 'مواء', 'icon' => '🔊'],
                    ['id' => 'r2', 'label' => 'نباح', 'icon' => '🔊'],
                ],
                'pairs' => ['l1' => 'r1', 'l2' => 'r2'],
            ],
            'hotspot' => [
                'imageUrl' => null,
                'spots' => [
                    ['id' => 's1', 'label' => 'أ', 'x' => 10, 'y' => 20, 'w' => 30, 'h' => 30],
                    ['id' => 's2', 'label' => 'ب', 'x' => 55, 'y' => 20, 'w' => 30, 'h' => 30],
                ],
                'correctId' => 's1',
            ],
            'puzzle_pieces' => [
                'pieces' => [
                    ['id' => 'p1', 'label' => 'قطعة 1', 'icon' => '🧩'],
                    ['id' => 'p2', 'label' => 'قطعة 2', 'icon' => '🧩'],
                    ['id' => 'p3', 'label' => 'قطعة 3', 'icon' => '🧩'],
                ],
                'correctOrder' => ['p1', 'p2', 'p3'],
            ],
            'numerical' => [
                'correct' => 4,
                'tolerance' => 0,
                'unit' => '',
                'hint' => '٢ + ٢ = ؟',
            ],
            'short_answer' => [
                'correct' => 'ماء',
                'acceptedAnswers' => ['ماء', 'الماء'],
                'placeholder' => 'اكتب الإجابة',
            ],
            default => [],
        };

        return $base;
    }
}
