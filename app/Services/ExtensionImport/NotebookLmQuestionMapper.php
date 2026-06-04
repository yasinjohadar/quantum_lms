<?php

namespace App\Services\ExtensionImport;

use App\Models\Question;

class NotebookLmQuestionMapper
{
    /**
     * @param  array<int, array<string, mixed>>  $rawQuestions
     * @return array{questions: array<int, array<string, mixed>>, errors: array<int, array{index: int, message: string}>}
     */
    public function normalizeMany(array $rawQuestions): array
    {
        $normalized = [];
        $errors = [];

        foreach ($rawQuestions as $index => $raw) {
            if (! is_array($raw)) {
                $errors[] = [
                    'index' => $index,
                    'message' => 'صيغة السؤال رقم '.($index + 1).' غير صالحة.',
                ];

                continue;
            }

            try {
                $normalized[] = $this->normalizeOne($raw, $index);
            } catch (ExtensionImportException $e) {
                $errors[] = [
                    'index' => $index,
                    'message' => $e->getMessage(),
                ];
            }
        }

        if ($normalized === []) {
            throw new ExtensionImportException(
                $errors[0]['message'] ?? 'لا توجد أسئلة صالحة للاستيراد.'
            );
        }

        return [
            'questions' => $normalized,
            'errors' => $errors,
        ];
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    public function normalizeOne(array $raw, int $index = 0): array
    {
        $title = trim((string) ($raw['title'] ?? $raw['question'] ?? $raw['text'] ?? ''));
        if ($title === '') {
            throw new ExtensionImportException('السؤال رقم '.($index + 1).' بدون نص.');
        }

        $type = $this->resolveType((string) ($raw['type'] ?? 'single_choice'));
        $options = $this->normalizeOptions(
            $raw['options'] ?? $raw['answerOptions'] ?? $raw['choices'] ?? [],
            $type,
            $index
        );

        return [
            'title' => $title,
            'content' => trim((string) ($raw['content'] ?? '')) ?: null,
            'explanation' => trim((string) ($raw['explanation'] ?? $raw['rationale'] ?? '')) ?: null,
            'type' => $type,
            'difficulty' => $this->resolveDifficulty((string) ($raw['difficulty'] ?? 'medium')),
            'default_points' => max(0.01, (float) ($raw['default_points'] ?? $raw['points'] ?? 1)),
            'options' => $options,
            'blank_answers' => $this->normalizeBlankAnswers($raw['blank_answers'] ?? [], $type),
            'case_sensitive' => (bool) ($raw['case_sensitive'] ?? false),
        ];
    }

    protected function resolveType(string $type): string
    {
        $type = strtolower(trim($type));

        if (! array_key_exists($type, Question::TYPES)) {
            throw new ExtensionImportException("نوع السؤال «{$type}» غير مدعوم.");
        }

        return $type;
    }

    protected function resolveDifficulty(string $difficulty): string
    {
        $difficulty = strtolower(trim($difficulty));

        return array_key_exists($difficulty, Question::DIFFICULTIES) ? $difficulty : 'medium';
    }

    /**
     * @param  mixed  $options
     * @return array<int, array{text: string, is_correct: bool}>
     */
    protected function normalizeOptions(mixed $options, string $type, int $index): array
    {
        if (in_array($type, ['short_answer', 'essay', 'numerical', 'fill_blanks'], true)) {
            return [];
        }

        if (! is_array($options)) {
            throw new ExtensionImportException('السؤال رقم '.($index + 1).' يحتاج خيارات.');
        }

        $normalized = [];

        foreach ($options as $option) {
            if (is_string($option)) {
                $text = trim($option);
                if ($text !== '') {
                    $normalized[] = ['text' => $text, 'is_correct' => false];
                }

                continue;
            }

            if (! is_array($option)) {
                continue;
            }

            $text = $this->extractOptionText($option);
            if ($text === '') {
                continue;
            }

            $normalized[] = [
                'text' => $text,
                'is_correct' => (bool) ($option['is_correct'] ?? $option['correct'] ?? false),
            ];
        }

        if ($type === 'true_false' && count($normalized) < 2) {
            $normalized = [
                ['text' => 'صح', 'is_correct' => true],
                ['text' => 'خطأ', 'is_correct' => false],
            ];
        }

        if (in_array($type, ['single_choice', 'multiple_choice', 'true_false', 'matching', 'ordering'], true)) {
            if ($normalized === []) {
                throw new ExtensionImportException('السؤال رقم '.($index + 1).' بدون خيارات صالحة.');
            }

            $correctCount = count(array_filter($normalized, fn ($o) => $o['is_correct']));

            if ($type === 'single_choice' || $type === 'true_false') {
                if ($correctCount === 0) {
                    $normalized[0]['is_correct'] = true;
                } elseif ($correctCount > 1) {
                    $found = false;
                    foreach ($normalized as &$row) {
                        if ($row['is_correct'] && ! $found) {
                            $found = true;
                        } else {
                            $row['is_correct'] = false;
                        }
                    }
                    unset($row);
                }
            }

            if ($type === 'multiple_choice' && $correctCount === 0) {
                $normalized[0]['is_correct'] = true;
            }
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $option
     */
    protected function extractOptionText(array $option): string
    {
        $text = $option['text'] ?? $option['content'] ?? $option['label'] ?? $option['answer'] ?? $option['value'] ?? '';

        if (is_array($text)) {
            $text = $text['raw'] ?? $text['text'] ?? $text['plain'] ?? '';
        }

        $text = strip_tags((string) $text);

        return trim(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /**
     * @param  mixed  $blankAnswers
     * @return array<int, string>
     */
    protected function normalizeBlankAnswers(mixed $blankAnswers, string $type): array
    {
        if ($type !== 'fill_blanks') {
            return [];
        }

        if (is_string($blankAnswers)) {
            $blankAnswers = [$blankAnswers];
        }

        if (! is_array($blankAnswers)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($v) => trim((string) $v),
            $blankAnswers
        )));
    }
}
