<?php

namespace App\InteractiveLearning\Services;

use App\InteractiveLearning\Support\QuestionTypeRegistry;
use App\Models\AIModel;
use App\Services\AI\AIModelService;
use App\Services\AI\AIProviderFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class AiSessionGenerationService
{
    /**
     * Curated sticker/icon names available as real SVG images (not just emoji).
     * Must mirror STICKER_MAP keys in resources/js/interactive-engine/dynamic/allowlist.js —
     * update both together when adding new icons.
     */
    protected const AVAILABLE_STICKERS = <<<'STICKERS'
حيوانات: lion, tiger, elephant, monkey, rabbit, bear, panda, fox, wolf, horse, cow, pig, sheep, goat, chicken, duck, penguin, owl, eagle, parrot, whale, dolphin, shark, octopus, turtle, snake, frog, bee, butterfly, ladybug, spider, snail, camel, kangaroo, koala, hedgehog, mouse-animal, bat, cat, dog, fish, tropical-fish, bird
حاسوب وتقنية: computer, laptop, computer-mouse, keyboard, printer, phone, telephone, camera, video-camera, tv, video-game, joystick, floppy-disk, battery, satellite
طعام: apple, banana, grape, orange, strawberry, watermelon, pineapple, cherries, peach, pear, carrot, corn, bread, cheese, pizza, hamburger, fries, ice-cream, cake, cookie, hot-dog
طبيعة وطقس: tree, palm-tree, cactus, tulip, rose, sunflower, flower, four-leaf-clover, sun, moon, star, cloud, rainbow, snowflake, lightning, umbrella, thermometer
مواصلات: car, taxi, bus, truck, ambulance, fire-engine, police-car, bicycle, motorcycle, airplane, rocket, sailboat, ship, train, helicopter
مدرسة وأدوات: book, pencil, pen, backpack, graduation-cap, school, ruler, scissors, clipboard, calendar, alarm-clock, light-bulb, magnifying-glass, globe, key, lock
رياضة: ball, basketball, tennis, baseball, trophy, medal, running, swimming
أخرى: gear, wrench, hammer, microscope, test-tube, stethoscope, syringe, pill, shopping-cart, money-bag, palette, paintbrush, puzzle-piece, gem, compass, anchor, gift, balloon
أرقام: number-1, number-2, number-3, number-4, number-5, number-6, number-7, number-8, number-9, number-10
STICKERS;

    public function __construct(
        protected AIModelService $modelService,
        protected SchemaValidator $validator,
        protected ContentLogicChecker $logicChecker
    ) {}

    /**
     * @param  list<string>  $types
     * @return array{questions: list<array<string, mixed>>, summary: string, model: string}
     */
    public function generate(
        string $topic,
        array $types,
        int $count = 5,
        string $difficulty = 'medium',
        string $objectives = '',
        ?int $modelId = null,
        string $experienceMode = 'classic'
    ): array {
        $experienceMode = $experienceMode === 'dynamic' ? 'dynamic' : 'classic';

        // Any registered question type is allowed in both modes now: dynamic-mode
        // questions outside DYNAMIC_INTERACTION_TYPES simply render via the classic
        // module UI beneath the rich stemBlocks content (see QuizSession.js).
        $types = array_values(array_filter($types, fn ($t) => QuestionTypeRegistry::has((string) $t)));
        if ($types === []) {
            $types = QuestionTypeRegistry::types();
        }

        $count = max(1, min(15, $count));
        $model = $this->resolveModel($modelId);
        $provider = AIProviderFactory::create($model);

        $prompt = $experienceMode === 'dynamic'
            ? $this->buildDynamicPrompt($topic, $types, $count, $difficulty, $objectives)
            : $this->buildPrompt($topic, $types, $count, $difficulty, $objectives);
        $maxTokens = min(max(2500, $count * 700), $model->max_tokens ?: 8000);

        $response = $provider->generateText($prompt, [
            'max_tokens' => $maxTokens,
            'temperature' => 0.55,
        ]);

        if (! $response) {
            throw new RuntimeException($provider->getLastError() ?: 'فشل استدعاء الذكاء الاصطناعي.');
        }

        $questions = $this->parseQuestions($response);
        $normalized = [];
        foreach ($questions as $question) {
            $row = $experienceMode === 'dynamic'
                ? $this->normalizeDynamicQuestion($question, $types)
                : $this->normalizeQuestion($question, $types);
            if ($row) {
                $normalized[] = $row;
            }
        }
        $normalized = array_values(array_filter($normalized));

        if ($normalized === []) {
            throw new RuntimeException('لم يُرجع الذكاء الاصطناعي أسئلة صالحة. حاول مجدداً أو غيّر النموذج.');
        }

        $normalized = array_slice($normalized, 0, $count);

        return [
            'questions' => $normalized,
            'summary' => 'تم توليد '.count($normalized).' سؤال/أسئلة حول: '.$topic,
            'model' => $model->name ?? $model->model_id ?? (string) $model->id,
        ];
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
     * @param  list<string>  $types
     */
    protected function buildPrompt(
        string $topic,
        array $types,
        int $count,
        string $difficulty,
        string $objectives
    ): string {
        $typesList = implode(', ', $types);
        $examples = json_encode([
            'true_false' => [
                'type' => 'true_false',
                'stem' => 'عبارة...',
                'points' => 1,
                'difficulty' => 'easy',
                'hints' => ['تلميح'],
                'explanation' => 'شرح',
                'successMessage' => 'شرح',
                'errorMessage' => 'حاول مرة أخرى',
                'estimatedSeconds' => 20,
                'payload' => ['correct' => true],
            ],
            'single_choice' => [
                'type' => 'single_choice',
                'stem' => 'سؤال؟',
                'points' => 2,
                'payload' => [
                    'options' => [
                        ['id' => 'a', 'label' => 'خيار أ', 'icon' => '⭐'],
                        ['id' => 'b', 'label' => 'خيار ب', 'icon' => '🌟'],
                    ],
                    'correctId' => 'a',
                ],
            ],
            'multiple_choice' => [
                'type' => 'multiple_choice',
                'stem' => 'اختر كل ما ينطبق',
                'payload' => [
                    'options' => [
                        ['id' => 'a', 'label' => 'أ', 'icon' => '🍎'],
                        ['id' => 'b', 'label' => 'ب', 'icon' => '🍌'],
                        ['id' => 'c', 'label' => 'ج', 'icon' => '🍇'],
                    ],
                    'correctIds' => ['a', 'c'],
                ],
            ],
            'drag_drop' => [
                'type' => 'drag_drop',
                'stem' => 'ضع كل عنصر في منطقته',
                'payload' => [
                    'items' => [
                        ['id' => 'i1', 'label' => 'عنصر', 'icon' => '🧩'],
                        ['id' => 'i2', 'label' => 'عنصر 2', 'icon' => '🎯'],
                    ],
                    'zones' => [
                        ['id' => 'z1', 'label' => 'منطقة 1', 'icon' => '📦'],
                        ['id' => 'z2', 'label' => 'منطقة 2', 'icon' => '🧺'],
                    ],
                    'assignments' => ['i1' => 'z1', 'i2' => 'z2'],
                ],
            ],
            'matching' => [
                'type' => 'matching',
                'stem' => 'طابق العناصر',
                'payload' => [
                    'left' => [
                        ['id' => 'l1', 'label' => 'يسار 1', 'icon' => 'cat'],
                        ['id' => 'l2', 'label' => 'يسار 2', 'icon' => 'lion'],
                    ],
                    'right' => [
                        ['id' => 'r1', 'label' => 'يمين 1', 'icon' => '1️⃣'],
                        ['id' => 'r2', 'label' => 'يمين 2', 'icon' => '2️⃣'],
                    ],
                    'pairs' => ['l1' => 'r1', 'l2' => 'r2'],
                ],
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $objectivesLine = $objectives !== '' ? "الأهداف التعليمية: {$objectives}" : 'الأهداف: فهم أساسي للموضوع.';
        $stickersClassic = self::AVAILABLE_STICKERS;

        return <<<PROMPT
أنت مصمم تجارب تعليمية تفاعلية عربية لطلاب صغار (مرحلة ابتدائية).
أنشئ أسئلة ممتعة وواضحة بلغة عربية بسيطة حماسية تناسب الأطفال.

الموضوع: {$topic}
{$objectivesLine}
عدد الأسئلة المطلوب: {$count}
مستوى الصعوبة العام: {$difficulty}
الأنواع المسموحة فقط: {$typesList}

وزّع الأنواع على الأسئلة بشكل متوازن قدر الإمكان (لا تكرر نوعاً واحداً فقط إن أمكن).

الملصقات/الأيقونات المتاحة كصور SVG حقيقية لحقل icon (استخدم هذه الأسماء بالضبط عندما تناسب الموضوع، بدل إيموجي عام):
{$stickersClassic}

لـ ordering: items + correctOrder
لـ fill_blank: template مع ___ و mode=choice|text و options و correct يجب أن يكون id الخيار (مثل a) وليس نص التسمية
لـ categorize: items + categories + correct map
لـ listen_choose: options + correctId + prompt.text (النص المنطوق مثل: سبعة) — مهم جداً
لـ connect_lines / memory_cards: left + right + pairs
لـ hotspot: spots[{id,label,x,y,w,h}] + correctId
لـ puzzle_pieces: pieces + correctOrder
لـ numerical: correct (رقم) + tolerance اختيارية
لـ short_answer: correct + acceptedAnswers

أعد JSON فقط بهذا الشكل:
{
  "summary": "ملخص قصير",
  "questions": [ ... ]
}

قواعد صارمة:
1) كل سؤال يجب أن يحتوي: type, stem, points, difficulty, hints (مصفوفة), explanation, successMessage, errorMessage, estimatedSeconds, payload
2) لا تضع حقل id (سيُولَّد لاحقاً)
3) لا تخرج HTML أو JavaScript
4) الإجابات الصحيحة يجب أن تكون دقيقة وواضحة داخل payload وفق النوع
5) لـ single_choice: options + correctId
6) لـ multiple_choice: options + correctIds (مصفوفة)
7) لـ true_false: payload.correct boolean
8) لـ drag_drop: items, zones, assignments
9) لـ matching: left, right, pairs
10) استخدم ids قصيرة مثل a,b,c أو i1,z1,l1,r1
11) successMessage بعبارات أطفال حماسية مثل: "يا بطل!", "أحسنت يا شاطر!", "أنت نجم!", "وووو رائع!"
12) errorMessage مشجّعة وليست قاسية مثل: "جرّب مرة ثانية!", "أنت قادر!", "فكّر بهدوء…"
13) نص السؤال والخيارات قصير وبسيط ومناسب لعمر صغير
14) لكل خيار/عنصر أضف حقل icon: فضّل اسم ملصق من القائمة أعلاه (مثل lion) عندما يناسب الموضوع، وإلا إيموجي مناسب واحد فقط (مثل 🦁) — ممنوع تكرار الإيموجي كعدّاد (❌🍎🍎🍎)
15) يمكن ترك imageUrl و audioUrl فارغين (null)
16) لأسئلة العدّ لا تعتمد على «يراها» دون صورة؛ فضّل وصفاً واضحاً أو استخدم تجربة ديناميك

أمثلة البنية:
{$examples}
PROMPT;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function parseQuestions(string $response): array
    {
        $raw = trim($response);
        if (preg_match('/\{.*\}/s', $raw, $m)) {
            $raw = $m[0];
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            Log::warning('AiSessionGenerationService: invalid JSON', ['snippet' => mb_substr($response, 0, 500)]);
            throw new RuntimeException('تعذر قراءة رد الذكاء الاصطناعي كـ JSON.');
        }

        $questions = $decoded['questions'] ?? null;
        if (! is_array($questions)) {
            return [];
        }

        return array_values(array_filter($questions, 'is_array'));
    }

    /**
     * @param  array<string, mixed>  $question
     * @param  list<string>  $allowedTypes
     * @return array<string, mixed>|null
     */
    protected function normalizeQuestion(array $question, array $allowedTypes): ?array
    {
        $type = (string) ($question['type'] ?? '');
        if (! in_array($type, $allowedTypes, true) || ! QuestionTypeRegistry::has($type)) {
            return null;
        }

        $stem = trim((string) ($question['stem'] ?? ''));
        if ($stem === '') {
            return null;
        }

        $blank = $this->validator->makeBlankQuestion($type);
        $payload = is_array($question['payload'] ?? null) ? $question['payload'] : $blank['payload'];

        $normalized = [
            'id' => (string) Str::uuid(),
            'type' => $type,
            'stem' => $stem,
            'points' => is_numeric($question['points'] ?? null) ? (float) $question['points'] : ($blank['points'] ?? 1),
            'difficulty' => in_array(($question['difficulty'] ?? ''), ['easy', 'medium', 'hard'], true)
                ? $question['difficulty']
                : 'medium',
            'hints' => is_array($question['hints'] ?? null)
                ? array_values(array_map('strval', $question['hints']))
                : [],
            'explanation' => (string) ($question['explanation'] ?? ''),
            'successMessage' => (string) ($question['successMessage'] ?? 'أحسنت!'),
            'errorMessage' => (string) ($question['errorMessage'] ?? 'حاول مرة أخرى'),
            'estimatedSeconds' => is_numeric($question['estimatedSeconds'] ?? null)
                ? (int) $question['estimatedSeconds']
                : 30,
            'tags' => is_array($question['tags'] ?? null) ? array_values(array_map('strval', $question['tags'])) : [],
            'learningObjectives' => is_array($question['learningObjectives'] ?? null)
                ? array_values(array_map('strval', $question['learningObjectives']))
                : [],
            'payload' => $payload,
        ];

        // Quick structural fixes
        if ($type === 'true_false') {
            $normalized['payload']['correct'] = (bool) ($payload['correct'] ?? true);
        }
        if ($type === 'single_choice') {
            $normalized['payload']['options'] = array_values(array_map(function ($opt) use ($blank) {
                $opt = is_array($opt) ? $opt : [];
                return [
                    'id' => (string) ($opt['id'] ?? 'a'),
                    'label' => (string) ($opt['label'] ?? 'خيار'),
                    'icon' => $this->singleIcon($opt['icon'] ?? '⭐'),
                    'imageUrl' => isset($opt['imageUrl']) && is_string($opt['imageUrl']) ? $opt['imageUrl'] : null,
                    'audioUrl' => isset($opt['audioUrl']) && is_string($opt['audioUrl']) ? $opt['audioUrl'] : null,
                ];
            }, $payload['options'] ?? $blank['payload']['options']));
            $normalized['payload']['correctId'] = (string) ($payload['correctId'] ?? ($normalized['payload']['options'][0]['id'] ?? 'a'));
        }
        if ($type === 'multiple_choice') {
            $normalized['payload']['options'] = array_values(array_map(function ($opt) {
                $opt = is_array($opt) ? $opt : [];
                return [
                    'id' => (string) ($opt['id'] ?? 'a'),
                    'label' => (string) ($opt['label'] ?? 'خيار'),
                    'icon' => $this->singleIcon($opt['icon'] ?? '⭐'),
                    'imageUrl' => isset($opt['imageUrl']) && is_string($opt['imageUrl']) ? $opt['imageUrl'] : null,
                    'audioUrl' => isset($opt['audioUrl']) && is_string($opt['audioUrl']) ? $opt['audioUrl'] : null,
                ];
            }, $payload['options'] ?? $blank['payload']['options']));
            $ids = $payload['correctIds'] ?? [];
            $normalized['payload']['correctIds'] = is_array($ids) ? array_values(array_map('strval', $ids)) : [];
            if ($normalized['payload']['correctIds'] === [] && isset($normalized['payload']['options'][0]['id'])) {
                $normalized['payload']['correctIds'] = [(string) $normalized['payload']['options'][0]['id']];
            }
        }
        if ($type === 'fill_blank') {
            $mode = ($payload['mode'] ?? '') === 'text' ? 'text' : 'choice';
            $normalized['payload']['mode'] = $mode;
            $normalized['payload']['template'] = (string) ($payload['template'] ?? 'أكمل: ___');
            if ($mode === 'choice') {
                $options = array_values(array_map(function ($opt) {
                    $opt = is_array($opt) ? $opt : [];

                    return [
                        'id' => (string) ($opt['id'] ?? 'a'),
                        'label' => (string) ($opt['label'] ?? 'خيار'),
                        'icon' => $this->singleIcon($opt['icon'] ?? '✏️'),
                        'imageUrl' => isset($opt['imageUrl']) && is_string($opt['imageUrl']) ? $opt['imageUrl'] : null,
                        'audioUrl' => isset($opt['audioUrl']) && is_string($opt['audioUrl']) ? $opt['audioUrl'] : null,
                    ];
                }, $payload['options'] ?? []));
                $normalized['payload']['options'] = $options;
                $correctRaw = (string) ($payload['correct'] ?? $payload['correctId'] ?? '');
                $resolved = $correctRaw;
                $ids = array_column($options, 'id');
                if ($correctRaw !== '' && ! in_array($correctRaw, $ids, true)) {
                    foreach ($options as $opt) {
                        if (trim((string) $opt['label']) === trim($correctRaw)) {
                            $resolved = (string) $opt['id'];
                            break;
                        }
                    }
                }
                if ($resolved === '' && isset($options[0]['id'])) {
                    $resolved = (string) $options[0]['id'];
                }
                $normalized['payload']['correct'] = $resolved;
            } else {
                $normalized['payload']['correct'] = (string) ($payload['correct'] ?? '');
                $accepted = $payload['acceptedAnswers'] ?? [$normalized['payload']['correct']];
                $normalized['payload']['acceptedAnswers'] = is_array($accepted)
                    ? array_values(array_map('strval', $accepted))
                    : [(string) $normalized['payload']['correct']];
            }
        }

        $logic = $this->logicChecker->partition(
            $this->logicChecker->checkAndFix($normalized, 'classic')
        );
        $normalized = $logic['question'];

        $check = $this->validator->validate([
            'version' => SchemaValidator::SCHEMA_VERSION,
            'mode' => 'classic',
            'meta' => ['title' => 'tmp', 'locale' => 'ar', 'rtl' => true],
            'questions' => [$normalized],
        ]);

        if (! $check['valid']) {
            Log::info('AiSessionGenerationService dropped invalid question', [
                'type' => $type,
                'errors' => $check['errors'],
            ]);

            return null;
        }

        return $normalized;
    }

    protected function singleIcon(mixed $icon): string
    {
        if (! is_string($icon) || trim($icon) === '') {
            return '⭐';
        }
        if ($this->logicChecker->isEmojiPile($icon)) {
            return $this->logicChecker->firstEmojiOrToken($icon);
        }

        return $icon;
    }

    /**
     * @param  list<string>  $types
     */
    protected function buildDynamicPrompt(
        string $topic,
        array $types,
        int $count,
        string $difficulty,
        string $objectives
    ): string {
        $typesList = implode(', ', $types);
        $objectivesLine = $objectives !== '' ? "الأهداف: {$objectives}" : 'فهم أساسي للموضوع.';
        $libs = implode(', ', SchemaValidator::ALLOWED_LIBRARIES);
        $stickers = self::AVAILABLE_STICKERS;

        return <<<PROMPT
أنت مصمم تجارب تعليمية ديناميكية عربية للأطفال. أخرج Schema كتل فقط — ممنوع HTML/JS.

الموضوع: {$topic}
{$objectivesLine}
عدد الأسئلة: {$count}
الصعوبة: {$difficulty}
أنواع التفاعل المسموحة: {$typesList}
المكتبات المعتمدة فقط: {$libs}

الملصقات/الأيقونات المتاحة كصور SVG حقيقية (استخدم هذه الأسماء بالضبط في حقل icon/sticker وفي كتل scene، واختر ما يناسب الموضوع فعلياً بدل النجمة الافتراضية):
{$stickers}

شكل كل سؤال:
{
  "stem": "نص مختصر",
  "stemBlocks": [
    {"type":"text","text":"..."},
    {"type":"scene","item":"apple","count":3,"layout":"row"},
    {"type":"math","latex":"2+2"}
  ],
  "interaction": {
    "type": "single_choice",
    "payload": {
      "options": [
        {"id":"a","label":"2","icon":"2️⃣"},
        {"id":"b","label":"3","icon":"3️⃣"}
      ],
      "correctId": "b"
    }
  },
  "points": 1,
  "difficulty": "easy",
  "hints": [],
  "explanation": "",
  "successMessage": "يا بطل!",
  "errorMessage": "جرّب مرة ثانية!",
  "estimatedSeconds": 20,
  "assets": {"libraries":["stickers","katex","tts"]}
}

شكل interaction.payload حسب interaction.type (نفس بنية الأنواع الكلاسيكية):
لـ true_false: payload.correct (boolean)
لـ single_choice: options + correctId
لـ multiple_choice: options + correctIds (مصفوفة)
لـ drag_drop: items + zones + assignments (خريطة itemId إلى zoneId)
لـ matching / connect_lines / memory_cards: left + right + pairs (خريطة leftId إلى rightId)
لـ ordering: items + correctOrder
لـ puzzle_pieces: pieces + correctOrder
لـ fill_blank: template (فيها ___) + mode=choice|text + options + correct (id الخيار وليس نص التسمية)
لـ categorize: items + categories + correct (خريطة itemId إلى categoryId)
لـ listen_choose: options + correctId + prompt.text (النص المنطوق مثل: سبعة) — مهم جداً
لـ hotspot: spots[{id,label,x,y,w,h}] + correctId
لـ numerical: correct (رقم) + tolerance اختيارية
لـ short_answer: correct + acceptedAnswers

قواعد صارمة:
1) لا تضع id للسؤال
2) stemBlocks غير فارغة
3) أسئلة العدّ يجب أن تتضمن كتلة scene و count = الإجابة الصحيحة
4) icon خيار واحد فقط — ممنوع 🍎🍎🍎. فضّل اسم ملصق من القائمة أعلاه (مثل lion وليس 🦁) عندما يناسب الموضوع؛ إيموجي مفرد مقبول أيضاً إن لم يوجد اسم مناسب
5) لـ math استخدم latex
6) interaction.type من القائمة فقط، وشكل payload يطابق النوع تماماً حسب الجدول أعلاه
7) استخدم ids قصيرة مثل a,b,c أو i1,z1,l1,r1
8) أعد JSON: {"summary":"...","questions":[...]}
PROMPT;
    }

    /**
     * @param  array<string, mixed>  $question
     * @param  list<string>  $allowedTypes
     * @return array<string, mixed>|null
     */
    protected function normalizeDynamicQuestion(array $question, array $allowedTypes): ?array
    {
        $interaction = is_array($question['interaction'] ?? null) ? $question['interaction'] : [];
        $type = (string) ($interaction['type'] ?? $question['type'] ?? '');
        if (! in_array($type, $allowedTypes, true)) {
            return null;
        }

        $payload = is_array($interaction['payload'] ?? null)
            ? $interaction['payload']
            : (is_array($question['payload'] ?? null) ? $question['payload'] : []);

        // Reuse classic option normalization via temporary classic question
        $classic = $this->normalizeQuestion([
            'type' => $type,
            'stem' => (string) ($question['stem'] ?? 'سؤال'),
            'points' => $question['points'] ?? 1,
            'difficulty' => $question['difficulty'] ?? 'medium',
            'hints' => $question['hints'] ?? [],
            'explanation' => $question['explanation'] ?? '',
            'successMessage' => $question['successMessage'] ?? 'يا بطل!',
            'errorMessage' => $question['errorMessage'] ?? 'جرّب مرة ثانية!',
            'estimatedSeconds' => $question['estimatedSeconds'] ?? 20,
            'payload' => $payload,
        ], $allowedTypes);

        if (! $classic) {
            return null;
        }

        $blocks = is_array($question['stemBlocks'] ?? null) ? $question['stemBlocks'] : [];
        if ($blocks === [] && ! empty($classic['stem'])) {
            $blocks = [['type' => 'text', 'text' => $classic['stem']]];
        }

        $dynamic = [
            'id' => (string) Str::uuid(),
            'stem' => $classic['stem'],
            'stemBlocks' => $blocks,
            'interaction' => [
                'type' => $type,
                'payload' => $classic['payload'],
            ],
            'optionBlocks' => is_array($question['optionBlocks'] ?? null) ? $question['optionBlocks'] : [],
            'assets' => [
                'libraries' => array_values(array_filter(
                    is_array($question['assets']['libraries'] ?? null) ? $question['assets']['libraries'] : ['stickers', 'tts'],
                    fn ($lib) => is_string($lib) && in_array($lib, SchemaValidator::ALLOWED_LIBRARIES, true)
                )),
            ],
            'points' => $classic['points'],
            'difficulty' => $classic['difficulty'],
            'hints' => $classic['hints'],
            'explanation' => $classic['explanation'],
            'successMessage' => $classic['successMessage'],
            'errorMessage' => $classic['errorMessage'],
            'estimatedSeconds' => $classic['estimatedSeconds'],
            'tags' => $classic['tags'] ?? [],
            'learningObjectives' => $classic['learningObjectives'] ?? [],
        ];

        $logic = $this->logicChecker->partition(
            $this->logicChecker->checkAndFix($dynamic, 'dynamic')
        );
        $dynamic = $logic['question'];

        if (! $logic['ok']) {
            Log::info('AiSessionGenerationService dropped dynamic logic failure', [
                'errors' => $logic['errors'],
            ]);

            return null;
        }

        $check = $this->validator->validate([
            'version' => SchemaValidator::SCHEMA_VERSION_DYNAMIC,
            'mode' => 'dynamic',
            'meta' => ['title' => 'tmp', 'locale' => 'ar', 'rtl' => true],
            'assets' => ['libraries' => SchemaValidator::ALLOWED_LIBRARIES],
            'questions' => [$dynamic],
        ]);

        if (! $check['valid']) {
            Log::info('AiSessionGenerationService dropped invalid dynamic question', [
                'errors' => $check['errors'],
            ]);

            return null;
        }

        return $dynamic;
    }
}
