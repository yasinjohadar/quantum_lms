<?php

namespace App\InteractiveLearning\Services;

use App\InteractiveLearning\Support\QuestionTypeRegistry;
use App\Services\QuestionPackImport\CsvQuestionPackParser;
use App\Services\QuestionPackImport\MarkdownQuestionPackParser;
use App\Services\QuestionPackImport\QuestionPackParseException;
use App\Support\QuestionMarkupFormatter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Parse CSV / Markdown / JSON into Interactive Learning Experience question schemas,
 * with KaTeX-ready preview payloads (same spirit as materials math import).
 */
class ExperienceQuestionImportService
{
    public function __construct(
        protected SchemaValidator $validator,
        protected ContentLogicChecker $logicChecker,
        protected CsvQuestionPackParser $csvPackParser,
        protected MarkdownQuestionPackParser $mdPackParser,
    ) {}

    /**
     * @return array{questions: list<array<string, mixed>>, previews: list<array<string, mixed>>, suspicious_count: int}
     */
    public function parseUploadedFile(UploadedFile $file, string $format, string $experienceMode = 'classic'): array
    {
        $format = strtolower($format);
        if (! in_array($format, ['csv', 'md', 'json'], true)) {
            throw new ExperienceQuestionImportException('الصيغة غير مدعومة. استخدم csv أو md أو json.');
        }

        $content = (string) file_get_contents($file->getRealPath());
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;

        return $this->parseContent($content, $format, $experienceMode);
    }

    /**
     * @return array{questions: list<array<string, mixed>>, previews: list<array<string, mixed>>, suspicious_count: int}
     */
    public function parseContent(string $content, string $format, string $experienceMode = 'classic'): array
    {
        $content = trim($content);
        if ($content === '') {
            throw new ExperienceQuestionImportException('الملف فارغ.');
        }

        $raw = match ($format) {
            'json' => $this->parseJson($content),
            'csv' => $this->parseCsv($content),
            'md' => $this->parseMarkdown($content),
            default => throw new ExperienceQuestionImportException('صيغة غير مدعومة.'),
        };

        $mode = $experienceMode === 'dynamic' ? 'dynamic' : 'classic';
        $questions = [];
        $errors = [];

        foreach ($raw as $index => $row) {
            try {
                $normalized = $this->normalizeRow($row, $mode);
                if ($normalized === null) {
                    $errors[] = 'سؤال #'.($index + 1).': نوع أو نص غير صالح.';
                    continue;
                }
                $questions[] = $normalized;
            } catch (\Throwable $e) {
                $errors[] = 'سؤال #'.($index + 1).': '.$e->getMessage();
            }
        }

        if ($questions === []) {
            $msg = 'لم يُستخرج أي سؤال صالح من الملف.';
            if ($errors !== []) {
                $msg .= ' '.implode(' ', array_slice($errors, 0, 5));
            }
            throw new ExperienceQuestionImportException($msg);
        }

        $previews = [];
        $suspicious = 0;
        foreach ($questions as $i => $q) {
            $preview = $this->toPreviewArray($q, $i + 1);
            if ($preview['has_warning']) {
                $suspicious++;
            }
            $previews[] = $preview;
        }

        return [
            'questions' => $questions,
            'previews' => $previews,
            'suspicious_count' => $suspicious,
            'parse_warnings' => $errors,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function parseJson(string $content): array
    {
        $decoded = json_decode($content, true);
        if (! is_array($decoded)) {
            throw new ExperienceQuestionImportException('ملف JSON غير صالح.');
        }

        if (isset($decoded['questions']) && is_array($decoded['questions'])) {
            $decoded = $decoded['questions'];
        }

        if ($decoded === [] || ! array_is_list($decoded)) {
            throw new ExperienceQuestionImportException('JSON يجب أن يكون مصفوفة أسئلة أو كائناً فيه questions[].');
        }

        return array_values(array_filter($decoded, 'is_array'));
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function parseCsv(string $content): array
    {
        $handle = fopen('php://memory', 'r+');
        if ($handle === false) {
            throw new ExperienceQuestionImportException('تعذر قراءة الملف.');
        }
        fwrite($handle, $content);
        rewind($handle);

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            throw new ExperienceQuestionImportException('رأس الجدول مفقود.');
        }

        $header = array_map(fn ($col) => trim(trim((string) $col, '"')), $header);
        $normalizedHeader = array_map(fn ($h) => strtolower($h), $header);

        // Math pack style (Question / Option A / Correct Answer) without type column
        $isMathPack = in_array('question', $normalizedHeader, true)
            && (in_array('option a', $normalizedHeader, true) || in_array('option_a', $normalizedHeader, true))
            && (in_array('correct answer', $normalizedHeader, true) || in_array('correct_answer', $normalizedHeader, true) || in_array('الإجابة', $normalizedHeader, true))
            && ! in_array('type', $normalizedHeader, true)
            && ! in_array('نوع', $normalizedHeader, true);

        if ($isMathPack) {
            fclose($handle);
            try {
                $dtos = $this->csvPackParser->parse($content, 'single_choice');
            } catch (QuestionPackParseException $e) {
                throw new ExperienceQuestionImportException($e->getMessage());
            }

            return array_map(fn ($dto) => $this->dtoToRow($dto), $dtos);
        }

        $map = $this->mapTypedCsvColumns($header);
        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            if ($this->isEmptyRow($row)) {
                continue;
            }
            $data = [];
            foreach ($map as $key => $index) {
                if ($index < 0) {
                    continue;
                }
                $data[$key] = trim(trim((string) ($row[$index] ?? ''), '"'));
            }
            $rows[] = $this->typedCsvRowToQuestion($data, count($rows) + 1);
        }
        fclose($handle);

        if ($rows === []) {
            throw new ExperienceQuestionImportException('لم يُستخرج أي سؤال من ملف CSV.');
        }

        return $rows;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function parseMarkdown(string $content): array
    {
        // Prefer typed MD blocks: ## N. stem + type: xxx
        if (preg_match('/^type\s*:/mi', $content) || preg_match('/^نوع\s*:/mi', $content)) {
            return $this->parseTypedMarkdown($content);
        }

        // Math / choice pack style
        try {
            $dtos = $this->mdPackParser->parse($content, 'single_choice');

            return array_map(fn ($dto) => $this->dtoToRow($dto), $dtos);
        } catch (QuestionPackParseException $e) {
            throw new ExperienceQuestionImportException($e->getMessage());
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function parseTypedMarkdown(string $content): array
    {
        $blocks = preg_split('/^##\s+\d+\.\s*/mu', $content, -1, PREG_SPLIT_NO_EMPTY);
        if ($blocks === false || $blocks === []) {
            throw new ExperienceQuestionImportException('لم يُعثر على أسئلة بصيغة ## رقم.');
        }

        $out = [];
        $n = 1;
        foreach ($blocks as $block) {
            $block = trim($block);
            if ($block === '') {
                continue;
            }
            $lines = preg_split('/\r\n|\r|\n/', $block) ?: [];
            $stem = trim((string) array_shift($lines));
            $meta = [];
            $options = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                if (preg_match('/^-\s*\*\*([A-F])\.\*\*\s*(.+)$/u', $line, $m)) {
                    $options[strtoupper($m[1])] = trim($m[2]);
                    continue;
                }
                if (preg_match('/^(type|نوع|difficulty|الصعوبة|points|النقاط|hint|تلميح|explanation|شرح|rationale|correct|الإجابة|tolerance|unit|الوحدة|accepted|template)\s*:\s*(.+)$/iu', $line, $m)) {
                    $key = strtolower($m[1]);
                    $key = match ($key) {
                        'نوع' => 'type',
                        'الصعوبة' => 'difficulty',
                        'النقاط' => 'points',
                        'تلميح' => 'hint',
                        'شرح', 'rationale' => 'explanation',
                        'الإجابة' => 'correct',
                        'الوحدة' => 'unit',
                        'accepted' => 'accepted',
                        default => $key,
                    };
                    $meta[$key] = trim($m[2]);
                    continue;
                }
                if (preg_match('/^>\s*\*?\*?Hint:?\*?\*?\s*(.+)$/iu', $line, $m)) {
                    $meta['hint'] = trim($m[1]);
                }
                if (preg_match('/^>\s*\*?\*?Answer:?\*?\*?\s*(.+)$/iu', $line, $m)) {
                    $meta['correct'] = trim($m[1]);
                }
                if (preg_match('/^>\s*\*?\*?Rationale:?\*?\*?\s*(.+)$/iu', $line, $m)) {
                    $meta['explanation'] = trim($m[1]);
                }
            }

            $type = $this->normalizeType($meta['type'] ?? ($options !== [] ? 'single_choice' : 'short_answer'));
            $row = [
                'type' => $type,
                'stem' => $stem,
                'difficulty' => $meta['difficulty'] ?? 'medium',
                'points' => $meta['points'] ?? 1,
                'hints' => ! empty($meta['hint']) ? [$meta['hint']] : [],
                'explanation' => $meta['explanation'] ?? '',
            ];

            if ($options !== []) {
                $payloadOptions = [];
                $letters = array_keys($options);
                foreach ($letters as $i => $letter) {
                    $id = strtolower($letter);
                    $payloadOptions[] = [
                        'id' => $id,
                        'label' => QuestionMarkupFormatter::deepNormalizeForStorage($options[$letter]),
                        'icon' => '⭐',
                    ];
                }
                $correctRaw = strtoupper((string) ($meta['correct'] ?? $letters[0] ?? 'A'));
                if (preg_match('/\b([A-F])\b/u', $correctRaw, $cm)) {
                    $correctRaw = $cm[1];
                }
                if ($type === 'multiple_choice') {
                    $ids = preg_split('/[,،\s]+/u', $correctRaw) ?: [];
                    $row['payload'] = [
                        'options' => $payloadOptions,
                        'correctIds' => array_values(array_map(fn ($l) => strtolower(trim($l)), array_filter($ids))),
                    ];
                } else {
                    $row['type'] = 'single_choice';
                    $row['payload'] = [
                        'options' => $payloadOptions,
                        'correctId' => strtolower($correctRaw),
                    ];
                }
            } elseif ($type === 'true_false') {
                $row['payload'] = ['correct' => $this->parseBool($meta['correct'] ?? 'true')];
            } elseif ($type === 'numerical') {
                $row['payload'] = [
                    'correct' => (string) ($meta['correct'] ?? ''),
                    'tolerance' => isset($meta['tolerance']) ? (float) $meta['tolerance'] : 0,
                    'unit' => (string) ($meta['unit'] ?? ''),
                ];
            } elseif ($type === 'short_answer') {
                $accepted = array_values(array_filter(array_map('trim', preg_split('/[,،]/u', (string) ($meta['accepted'] ?? $meta['correct'] ?? '')) ?: [])));
                $row['payload'] = [
                    'correct' => (string) ($meta['correct'] ?? ($accepted[0] ?? '')),
                    'acceptedAnswers' => $accepted !== [] ? $accepted : [(string) ($meta['correct'] ?? '')],
                ];
            } elseif ($type === 'fill_blank') {
                $row['payload'] = [
                    'template' => (string) ($meta['template'] ?? $stem),
                    'mode' => 'text',
                    'correct' => (string) ($meta['correct'] ?? ''),
                    'acceptedAnswers' => [(string) ($meta['correct'] ?? '')],
                ];
            }

            $out[] = $row;
            $n++;
        }

        if ($out === []) {
            throw new ExperienceQuestionImportException('لم يُستخرج أي سؤال من Markdown.');
        }

        return $out;
    }

    /**
     * @param  object{number:int,title:string,hint:string,options:array,correctLetter:string,explanation:string}  $dto
     * @return array<string, mixed>
     */
    protected function dtoToRow(object $dto): array
    {
        $options = [];
        foreach ($dto->options as $letter => $label) {
            $options[] = [
                'id' => strtolower((string) $letter),
                'label' => QuestionMarkupFormatter::deepNormalizeForStorage((string) $label),
                'icon' => '⭐',
            ];
        }

        return [
            'type' => 'single_choice',
            'stem' => QuestionMarkupFormatter::deepNormalizeForStorage($dto->title),
            'hints' => $dto->hint !== '' ? [QuestionMarkupFormatter::deepNormalizeForStorage($dto->hint)] : [],
            'explanation' => QuestionMarkupFormatter::deepNormalizeForStorage($dto->explanation),
            'difficulty' => 'medium',
            'points' => 1,
            'payload' => [
                'options' => $options,
                'correctId' => strtolower($dto->correctLetter),
            ],
        ];
    }

    /**
     * @param  array<int, string>  $header
     * @return array<string, int>
     */
    protected function mapTypedCsvColumns(array $header): array
    {
        $aliases = [
            'type' => ['type', 'نوع'],
            'stem' => ['stem', 'question', 'السؤال', 'عنوان', 'title'],
            'difficulty' => ['difficulty', 'الصعوبة'],
            'points' => ['points', 'النقاط'],
            'hint' => ['hint', 'تلميح'],
            'explanation' => ['explanation', 'rationale', 'شرح', 'التبرير'],
            'option_a' => ['option a', 'option_a', 'option1'],
            'option_b' => ['option b', 'option_b', 'option2'],
            'option_c' => ['option c', 'option_c', 'option3'],
            'option_d' => ['option d', 'option_d', 'option4'],
            'option_e' => ['option e', 'option_e', 'option5'],
            'option_f' => ['option f', 'option_f', 'option6'],
            'correct' => ['correct', 'correct answer', 'correct_answer', 'الإجابة', 'correctid', 'correct_ids'],
            'tolerance' => ['tolerance', 'التسامح'],
            'unit' => ['unit', 'الوحدة'],
            'accepted' => ['accepted', 'accepted_answers', 'إجابات مقبولة'],
            'template' => ['template', 'قالب'],
            'items' => ['items', 'العناصر'],
            'correct_order' => ['correct_order', 'الترتيب'],
            'payload_json' => ['payload', 'payload_json'],
        ];

        $map = [];
        foreach ($header as $index => $col) {
            $normalized = strtolower(trim($col));
            foreach ($aliases as $key => $names) {
                if (in_array($normalized, $names, true)) {
                    $map[$key] = $index;
                }
            }
        }

        if (! isset($map['stem'])) {
            throw new ExperienceQuestionImportException('عمود السؤال (stem/question) مطلوب.');
        }

        return $map;
    }

    /**
     * @param  array<string, string>  $data
     * @return array<string, mixed>
     */
    protected function typedCsvRowToQuestion(array $data, int $number): array
    {
        $stem = $data['stem'] ?? '';
        if ($stem === '') {
            throw new ExperienceQuestionImportException("الصف {$number}: نص السؤال مفقود.");
        }

        $type = $this->normalizeType($data['type'] ?? '');
        if ($type === '' || ! QuestionTypeRegistry::has($type)) {
            // Infer from columns
            if (! empty($data['option_a']) && ! empty($data['option_b'])) {
                $correct = (string) ($data['correct'] ?? '');
                $type = str_contains($correct, ',') || str_contains($correct, '،') ? 'multiple_choice' : 'single_choice';
            } elseif (isset($data['tolerance']) || (isset($data['correct']) && is_numeric(str_replace([',', ' '], ['.', ''], $data['correct'] ?? '')))) {
                $type = 'numerical';
            } else {
                $type = 'short_answer';
            }
        }

        $row = [
            'type' => $type,
            'stem' => QuestionMarkupFormatter::deepNormalizeForStorage($stem),
            'difficulty' => in_array(strtolower($data['difficulty'] ?? ''), ['easy', 'medium', 'hard'], true)
                ? strtolower($data['difficulty'])
                : 'medium',
            'points' => is_numeric($data['points'] ?? null) ? (float) $data['points'] : 1,
            'hints' => ! empty($data['hint']) ? [QuestionMarkupFormatter::deepNormalizeForStorage($data['hint'])] : [],
            'explanation' => QuestionMarkupFormatter::deepNormalizeForStorage($data['explanation'] ?? ''),
        ];

        if (! empty($data['payload_json'])) {
            $payload = json_decode($data['payload_json'], true);
            if (! is_array($payload)) {
                throw new ExperienceQuestionImportException("الصف {$number}: payload_json غير صالح.");
            }
            $row['payload'] = $payload;

            return $row;
        }

        $options = [];
        foreach (['a', 'b', 'c', 'd', 'e', 'f'] as $letter) {
            $val = trim($data['option_'.$letter] ?? '');
            if ($val === '') {
                continue;
            }
            $options[] = [
                'id' => $letter,
                'label' => QuestionMarkupFormatter::deepNormalizeForStorage($val),
                'icon' => '⭐',
            ];
        }

        $correct = trim((string) ($data['correct'] ?? ''));

        $row['payload'] = match ($type) {
            'true_false' => ['correct' => $this->parseBool($correct !== '' ? $correct : 'true')],
            'single_choice', 'listen_choose' => $this->choicePayload($options, $correct, false),
            'multiple_choice' => $this->choicePayload($options, $correct, true),
            'numerical' => [
                'correct' => $correct,
                'tolerance' => isset($data['tolerance']) && $data['tolerance'] !== '' ? (float) $data['tolerance'] : 0,
                'unit' => (string) ($data['unit'] ?? ''),
            ],
            'short_answer' => [
                'correct' => $correct,
                'acceptedAnswers' => $this->splitList($data['accepted'] ?? $correct),
            ],
            'fill_blank' => [
                'template' => (string) ($data['template'] ?? $stem),
                'mode' => $options !== [] ? 'choice' : 'text',
                'options' => $options,
                'correct' => $options !== [] ? $this->resolveCorrectId($options, $correct) : $correct,
                'acceptedAnswers' => $options === [] ? $this->splitList($data['accepted'] ?? $correct) : [],
            ],
            'ordering', 'puzzle_pieces' => $this->orderingPayload($data, $type),
            default => throw new ExperienceQuestionImportException(
                "الصف {$number}: النوع «{$type}» يتطلب JSON (عمود payload_json أو ملف .json) لضبط البنية بالكامل."
            ),
        };

        return $row;
    }

    /**
     * @param  list<array{id:string,label:string,icon?:string}>  $options
     * @return array<string, mixed>
     */
    protected function choicePayload(array $options, string $correct, bool $multiple): array
    {
        if (count($options) < 2) {
            throw new ExperienceQuestionImportException('أسئلة الاختيار تحتاج خيارين على الأقل.');
        }

        if ($multiple) {
            $letters = preg_split('/[,،\s]+/u', strtoupper($correct)) ?: [];
            $ids = [];
            foreach ($letters as $letter) {
                if (preg_match('/\b([A-F])\b/u', $letter, $m) || preg_match('/^([A-F])$/u', $letter, $m)) {
                    $ids[] = strtolower($m[1]);
                } else {
                    $resolved = $this->resolveCorrectId($options, $letter);
                    if ($resolved !== '') {
                        $ids[] = $resolved;
                    }
                }
            }
            $ids = array_values(array_unique(array_filter($ids)));
            if ($ids === []) {
                $ids = [$options[0]['id']];
            }

            return ['options' => $options, 'correctIds' => $ids];
        }

        $id = $this->resolveCorrectId($options, $correct);
        if ($id === '') {
            if (preg_match('/\b([A-F])\b/u', strtoupper($correct), $m)) {
                $id = strtolower($m[1]);
            } else {
                $id = $options[0]['id'];
            }
        }

        return ['options' => $options, 'correctId' => $id];
    }

    /**
     * @param  list<array{id:string,label:string}>  $options
     */
    protected function resolveCorrectId(array $options, string $correct): string
    {
        $correct = trim($correct);
        if ($correct === '') {
            return '';
        }
        if (preg_match('/\b([A-F])\b/u', strtoupper($correct), $m)) {
            $letter = strtolower($m[1]);
            foreach ($options as $opt) {
                if ($opt['id'] === $letter) {
                    return $letter;
                }
            }
        }
        foreach ($options as $opt) {
            if (strcasecmp(trim($opt['label']), $correct) === 0 || $opt['id'] === strtolower($correct)) {
                return $opt['id'];
            }
        }

        return '';
    }

    /**
     * @param  array<string, string>  $data
     * @return array<string, mixed>
     */
    protected function orderingPayload(array $data, string $type): array
    {
        $itemsRaw = $this->splitList($data['items'] ?? '', '/[|؛;]/u');
        if ($itemsRaw === []) {
            throw new ExperienceQuestionImportException('عمود items مطلوب لأنواع الترتيب/الأحجية (افصل بـ |).');
        }
        $items = [];
        foreach ($itemsRaw as $i => $label) {
            $id = chr(ord('a') + $i);
            $items[] = ['id' => $id, 'label' => $label, 'icon' => '🔢'];
        }
        $orderRaw = $this->splitList($data['correct_order'] ?? '', '/[|؛;,\s]+/u');
        $correctOrder = [];
        if ($orderRaw === []) {
            $correctOrder = array_column($items, 'id');
        } else {
            foreach ($orderRaw as $token) {
                if (preg_match('/^[A-Za-z]$/', $token)) {
                    $correctOrder[] = strtolower($token);
                } elseif (ctype_digit($token)) {
                    $idx = ((int) $token) - 1;
                    if (isset($items[$idx])) {
                        $correctOrder[] = $items[$idx]['id'];
                    }
                } else {
                    foreach ($items as $item) {
                        if (strcasecmp($item['label'], $token) === 0) {
                            $correctOrder[] = $item['id'];
                            break;
                        }
                    }
                }
            }
        }

        if ($type === 'puzzle_pieces') {
            return ['pieces' => $items, 'correctOrder' => $correctOrder];
        }

        return ['items' => $items, 'correctOrder' => $correctOrder];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>|null
     */
    protected function normalizeRow(array $row, string $mode): ?array
    {
        $type = $this->normalizeType((string) ($row['type'] ?? ($row['interaction']['type'] ?? '')));
        if ($type === '' || ! QuestionTypeRegistry::has($type)) {
            return null;
        }

        $stem = trim((string) ($row['stem'] ?? ''));
        if ($stem === '' && isset($row['stemBlocks']) && is_array($row['stemBlocks'])) {
            foreach ($row['stemBlocks'] as $block) {
                if (($block['type'] ?? '') === 'text' && ! empty($block['text'])) {
                    $stem = trim((string) $block['text']);
                    break;
                }
                if (($block['type'] ?? '') === 'math' && ! empty($block['latex'])) {
                    $stem = trim((string) $block['latex']);
                    break;
                }
            }
        }
        if ($stem === '') {
            return null;
        }

        $stem = QuestionMarkupFormatter::deepNormalizeForStorage($stem);
        $blank = $this->validator->makeBlankQuestion($type);
        $payload = is_array($row['payload'] ?? null)
            ? $row['payload']
            : (is_array($row['interaction']['payload'] ?? null) ? $row['interaction']['payload'] : $blank['payload']);

        // Normalize LaTeX in option labels
        if (isset($payload['options']) && is_array($payload['options'])) {
            foreach ($payload['options'] as &$opt) {
                if (is_array($opt) && isset($opt['label'])) {
                    $opt['label'] = QuestionMarkupFormatter::deepNormalizeForStorage((string) $opt['label']);
                }
            }
            unset($opt);
        }

        $classic = [
            'id' => (string) Str::uuid(),
            'type' => $type,
            'stem' => $stem,
            'points' => is_numeric($row['points'] ?? null) ? (float) $row['points'] : 1,
            'difficulty' => in_array(($row['difficulty'] ?? ''), ['easy', 'medium', 'hard'], true) ? $row['difficulty'] : 'medium',
            'hints' => is_array($row['hints'] ?? null)
                ? array_values(array_map(fn ($h) => QuestionMarkupFormatter::deepNormalizeForStorage((string) $h), $row['hints']))
                : (! empty($row['hint']) ? [QuestionMarkupFormatter::deepNormalizeForStorage((string) $row['hint'])] : []),
            'explanation' => QuestionMarkupFormatter::deepNormalizeForStorage((string) ($row['explanation'] ?? '')),
            'successMessage' => (string) ($row['successMessage'] ?? 'أحسنت!'),
            'errorMessage' => (string) ($row['errorMessage'] ?? 'حاول مرة أخرى'),
            'estimatedSeconds' => is_numeric($row['estimatedSeconds'] ?? null) ? (int) $row['estimatedSeconds'] : 30,
            'tags' => is_array($row['tags'] ?? null) ? array_values(array_map('strval', $row['tags'])) : [],
            'learningObjectives' => is_array($row['learningObjectives'] ?? null)
                ? array_values(array_map('strval', $row['learningObjectives']))
                : [],
            'payload' => $payload ?: $blank['payload'],
        ];

        // Structural fixes for common types
        if ($type === 'true_false') {
            $classic['payload']['correct'] = (bool) ($classic['payload']['correct'] ?? true);
        }
        if (in_array($type, ['single_choice', 'listen_choose'], true)) {
            $classic['payload']['options'] = array_values($classic['payload']['options'] ?? $blank['payload']['options']);
            $classic['payload']['correctId'] = (string) ($classic['payload']['correctId'] ?? ($classic['payload']['options'][0]['id'] ?? 'a'));
            if ($type === 'listen_choose' && empty($classic['payload']['prompt'])) {
                $classic['payload']['prompt'] = $blank['payload']['prompt'];
            }
        }
        if ($type === 'multiple_choice') {
            $classic['payload']['options'] = array_values($classic['payload']['options'] ?? $blank['payload']['options']);
            $ids = $classic['payload']['correctIds'] ?? [];
            $classic['payload']['correctIds'] = is_array($ids) ? array_values(array_map('strval', $ids)) : [];
        }
        if ($type === 'numerical') {
            $classic['payload']['correct'] = (string) ($classic['payload']['correct'] ?? '');
            $classic['payload']['tolerance'] = isset($classic['payload']['tolerance'])
                ? (float) $classic['payload']['tolerance']
                : 0;
            $classic['payload']['unit'] = (string) ($classic['payload']['unit'] ?? '');
        }

            $logic = $this->logicChecker->partition(
            $this->logicChecker->checkAndFix($classic, 'classic')
        );
        if (! $logic['ok']) {
            throw new ExperienceQuestionImportException(implode(' ', $logic['errors']));
        }
        $classic = $logic['question'];

        $mini = $this->validator->emptySchema('import-check', 'classic');
        $mini['questions'] = [$classic];
        $check = $this->validator->validate($mini);
        if (! $check['valid']) {
            throw new ExperienceQuestionImportException(implode(' ', $check['errors']));
        }

        if ($mode !== 'dynamic') {
            return $classic;
        }

        $blocks = is_array($row['stemBlocks'] ?? null) ? $row['stemBlocks'] : [];
        if ($blocks === []) {
            // Prefer math block when stem looks like latex
            if (str_contains($classic['stem'], '\\') || str_contains($classic['stem'], '$')) {
                $blocks = [
                    ['type' => 'math', 'latex' => $classic['stem']],
                    ['type' => 'text', 'text' => $classic['stem']],
                ];
            } else {
                $blocks = [['type' => 'text', 'text' => $classic['stem']]];
            }
        }

        $dynamic = [
            'id' => (string) Str::uuid(),
            'stem' => $classic['stem'],
            'stemBlocks' => $blocks,
            'interaction' => [
                'type' => $type,
                'payload' => $classic['payload'],
            ],
            'optionBlocks' => is_array($row['optionBlocks'] ?? null) ? $row['optionBlocks'] : [],
            'assets' => [
                'libraries' => ['katex', 'stickers', 'tts'],
            ],
            'points' => $classic['points'],
            'difficulty' => $classic['difficulty'],
            'hints' => $classic['hints'],
            'explanation' => $classic['explanation'],
            'successMessage' => $classic['successMessage'],
            'errorMessage' => $classic['errorMessage'],
            'estimatedSeconds' => $classic['estimatedSeconds'],
            'tags' => $classic['tags'],
            'learningObjectives' => $classic['learningObjectives'],
            // Keep type/payload mirrors used by the Alpine editor
            'type' => $type,
            'payload' => $classic['payload'],
        ];

        $logicDyn = $this->logicChecker->partition(
            $this->logicChecker->checkAndFix($dynamic, 'dynamic')
        );
        if (! $logicDyn['ok']) {
            throw new ExperienceQuestionImportException(implode(' ', $logicDyn['errors']));
        }
        $dynamic = $logicDyn['question'];

        $miniDyn = $this->validator->emptySchema('import-check', 'dynamic');
        $miniDyn['questions'] = [$dynamic];
        $checkDyn = $this->validator->validate($miniDyn);
        if (! $checkDyn['valid']) {
            throw new ExperienceQuestionImportException(implode(' ', $checkDyn['errors']));
        }

        return $dynamic;
    }

    /**
     * @param  array<string, mixed>  $question
     * @return array<string, mixed>
     */
    protected function toPreviewArray(array $question, int $number): array
    {
        $type = (string) ($question['type'] ?? $question['interaction']['type'] ?? '');
        $stem = (string) ($question['stem'] ?? '');
        $stemStored = QuestionMarkupFormatter::deepNormalizeForStorage($stem);
        $explanation = QuestionMarkupFormatter::deepNormalizeForStorage((string) ($question['explanation'] ?? ''));
        $hints = $question['hints'] ?? [];
        $hintStored = is_array($hints) && isset($hints[0])
            ? QuestionMarkupFormatter::deepNormalizeForStorage((string) $hints[0])
            : '';

        $hasWarning = QuestionMarkupFormatter::hasSuspiciousBareLatex($stemStored)
            || QuestionMarkupFormatter::hasSuspiciousBareLatex($explanation)
            || ($hintStored !== '' && QuestionMarkupFormatter::hasSuspiciousBareLatex($hintStored));

        $payload = is_array($question['payload'] ?? null)
            ? $question['payload']
            : (is_array($question['interaction']['payload'] ?? null) ? $question['interaction']['payload'] : []);

        $options = [];
        foreach ($payload['options'] ?? [] as $opt) {
            if (! is_array($opt)) {
                continue;
            }
            $labelStored = QuestionMarkupFormatter::deepNormalizeForStorage((string) ($opt['label'] ?? ''));
            $optWarn = QuestionMarkupFormatter::hasSuspiciousBareLatex($labelStored);
            $hasWarning = $hasWarning || $optWarn;
            $id = (string) ($opt['id'] ?? '');
            $isCorrect = false;
            if (isset($payload['correctId'])) {
                $isCorrect = $payload['correctId'] === $id;
            } elseif (isset($payload['correctIds']) && is_array($payload['correctIds'])) {
                $isCorrect = in_array($id, $payload['correctIds'], true);
            } elseif (isset($payload['correct'])) {
                $isCorrect = (string) $payload['correct'] === $id;
            }
            $options[] = [
                'id' => $id,
                'letter' => strtoupper($id),
                'is_correct' => $isCorrect,
                'html' => function_exists('format_question_markup') ? format_question_markup($labelStored) : e($labelStored),
                'has_warning' => $optWarn,
            ];
        }

        $metaLine = '';
        if ($type === 'numerical') {
            $metaLine = 'الإجابة: '.($payload['correct'] ?? '').
                (isset($payload['tolerance']) ? ' ± '.$payload['tolerance'] : '').
                (! empty($payload['unit']) ? ' '.$payload['unit'] : '');
        } elseif ($type === 'true_false') {
            $metaLine = ! empty($payload['correct']) ? 'صح' : 'خطأ';
        } elseif ($type === 'short_answer') {
            $metaLine = 'الإجابة: '.($payload['correct'] ?? '');
        }

        $typeMeta = QuestionTypeRegistry::keyed()[$type] ?? null;

        return [
            'number' => $number,
            'type' => $type,
            'type_name' => $typeMeta['name'] ?? $type,
            'type_color' => $typeMeta['color'] ?? '#64748b',
            'stem_html' => function_exists('format_question_markup') ? format_question_markup($stemStored) : e($stemStored),
            'hint_html' => $hintStored !== ''
                ? (function_exists('format_question_markup') ? format_question_markup($hintStored) : e($hintStored))
                : null,
            'explanation_html' => $explanation !== ''
                ? (function_exists('format_question_markup') ? format_question_markup($explanation) : e($explanation))
                : null,
            'options' => $options,
            'meta_line' => $metaLine,
            'difficulty' => $question['difficulty'] ?? 'medium',
            'points' => $question['points'] ?? 1,
            'has_warning' => $hasWarning,
            'question' => $question,
        ];
    }

    protected function normalizeType(string $type): string
    {
        $type = strtolower(trim($type));
        $aliases = [
            'truefalse' => 'true_false',
            'true/false' => 'true_false',
            'صح/خطأ' => 'true_false',
            'single' => 'single_choice',
            'mcq' => 'single_choice',
            'multiple' => 'multiple_choice',
            'multi' => 'multiple_choice',
            'number' => 'numerical',
            'numeric' => 'numerical',
            'math' => 'numerical',
            'short' => 'short_answer',
            'fill' => 'fill_blank',
            'blank' => 'fill_blank',
            'order' => 'ordering',
            'puzzle' => 'puzzle_pieces',
            'listen' => 'listen_choose',
        ];

        return $aliases[$type] ?? $type;
    }

    protected function parseBool(string $value): bool
    {
        $v = strtolower(trim($value));

        return in_array($v, ['1', 'true', 'yes', 'صح', 'صحيح', 't', 'y'], true);
    }

    /**
     * @return list<string>
     */
    protected function splitList(string $value, string $pattern = '/[,،]/u'): array
    {
        $parts = preg_split($pattern, $value) ?: [];

        return array_values(array_filter(array_map('trim', $parts), fn ($p) => $p !== ''));
    }

    /**
     * @param  array<int, string|null>  $row
     */
    protected function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }
}
