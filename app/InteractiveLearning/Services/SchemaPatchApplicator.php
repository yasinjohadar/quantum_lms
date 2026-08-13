<?php

namespace App\InteractiveLearning\Services;

use App\InteractiveLearning\Support\FeedbackPhrases;
use InvalidArgumentException;

class SchemaPatchApplicator
{
    /**
     * @param  array<string, mixed>  $schema
     * @param  list<array<string, mixed>>  $operations
     * @return array{schema: array<string, mixed>, applied: int, errors: list<string>}
     */
    public function apply(array $schema, array $operations): array
    {
        $errors = [];
        $applied = 0;
        $next = $schema;

        foreach ($operations as $index => $operation) {
            if (! is_array($operation)) {
                $errors[] = "العملية #{$index} غير صالحة.";
                continue;
            }

            $op = (string) ($operation['op'] ?? '');

            try {
                $next = match ($op) {
                    'update_question' => $this->updateQuestion($next, $operation),
                    'update_meta' => $this->updateMeta($next, $operation),
                    'update_messages' => $this->updateMessages($next, $operation),
                    'update_rules' => $this->updateRules($next, $operation),
                    'add_question' => $this->addQuestion($next, $operation),
                    default => throw new InvalidArgumentException("عملية غير معروفة: {$op}"),
                };
                $applied++;
            } catch (\Throwable $e) {
                $errors[] = "العملية #{$index}: ".$e->getMessage();
            }
        }

        return [
            'schema' => $next,
            'applied' => $applied,
            'errors' => $errors,
        ];
    }

    /**
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $operation
     * @return array<string, mixed>
     */
    protected function updateQuestion(array $schema, array $operation): array
    {
        $questionId = (string) ($operation['questionId'] ?? '');
        $fields = $operation['fields'] ?? null;
        if ($questionId === '' || ! is_array($fields)) {
            throw new InvalidArgumentException('questionId و fields مطلوبان.');
        }

        $questions = $schema['questions'] ?? [];
        $found = false;
        foreach ($questions as $i => $question) {
            if (($question['id'] ?? null) !== $questionId) {
                continue;
            }
            $found = true;
            foreach (['stem', 'explanation', 'difficulty'] as $key) {
                if (array_key_exists($key, $fields) && is_scalar($fields[$key])) {
                    $questions[$i][$key] = (string) $fields[$key];
                }
            }
            // الرسائل مقيّدة بقائمة العبارات المسجّلة صوتياً — أي عبارة أخرى تُضبط عليها
            foreach (['successMessage' => FeedbackPhrases::KIND_SUCCESS, 'errorMessage' => FeedbackPhrases::KIND_FAIL] as $key => $kind) {
                if (array_key_exists($key, $fields) && is_scalar($fields[$key])) {
                    $questions[$i][$key] = FeedbackPhrases::snap((string) $fields[$key], $kind, $i);
                }
            }
            if (isset($fields['hints']) && is_array($fields['hints'])) {
                $questions[$i]['hints'] = array_values(array_map('strval', $fields['hints']));
            }
            if (isset($fields['tags']) && is_array($fields['tags'])) {
                $questions[$i]['tags'] = array_values(array_map('strval', $fields['tags']));
            }
            if (isset($fields['learningObjectives']) && is_array($fields['learningObjectives'])) {
                $questions[$i]['learningObjectives'] = array_values(array_map('strval', $fields['learningObjectives']));
            }
            if (isset($fields['points']) && is_numeric($fields['points'])) {
                $questions[$i]['points'] = (float) $fields['points'];
            }
            if (isset($fields['estimatedSeconds']) && is_numeric($fields['estimatedSeconds'])) {
                $questions[$i]['estimatedSeconds'] = (int) $fields['estimatedSeconds'];
            }
            if (isset($fields['payload']) && is_array($fields['payload'])) {
                $questions[$i]['payload'] = array_replace_recursive(
                    is_array($questions[$i]['payload'] ?? null) ? $questions[$i]['payload'] : [],
                    $fields['payload']
                );
            }
        }

        if (! $found) {
            throw new InvalidArgumentException("لم يُعثر على السؤال {$questionId}");
        }

        $schema['questions'] = $questions;

        return $schema;
    }

    /**
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $operation
     * @return array<string, mixed>
     */
    protected function updateMeta(array $schema, array $operation): array
    {
        $fields = $operation['fields'] ?? [];
        if (! is_array($fields)) {
            throw new InvalidArgumentException('fields مطلوبة.');
        }
        $schema['meta'] = array_replace(
            is_array($schema['meta'] ?? null) ? $schema['meta'] : [],
            array_intersect_key($fields, array_flip(['title', 'locale', 'rtl']))
        );

        return $schema;
    }

    /**
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $operation
     * @return array<string, mixed>
     */
    protected function updateMessages(array $schema, array $operation): array
    {
        $fields = $operation['fields'] ?? [];
        if (! is_array($fields)) {
            throw new InvalidArgumentException('fields مطلوبة.');
        }
        $messages = is_array($schema['messages'] ?? null) ? $schema['messages'] : [];
        foreach (['success', 'error', 'encourage'] as $key) {
            if (isset($fields[$key]) && is_array($fields[$key])) {
                $messages[$key] = array_values(array_map('strval', $fields[$key]));
            }
        }
        $schema['messages'] = $messages;

        return $schema;
    }

    /**
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $operation
     * @return array<string, mixed>
     */
    protected function updateRules(array $schema, array $operation): array
    {
        $fields = $operation['fields'] ?? [];
        if (! is_array($fields)) {
            throw new InvalidArgumentException('fields مطلوبة.');
        }
        $schema['rules'] = array_replace(
            is_array($schema['rules'] ?? null) ? $schema['rules'] : [],
            $fields
        );

        return $schema;
    }

    /**
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $operation
     * @return array<string, mixed>
     */
    protected function addQuestion(array $schema, array $operation): array
    {
        $question = $operation['question'] ?? null;
        if (! is_array($question)) {
            throw new InvalidArgumentException('question مطلوب.');
        }
        $schema['questions'] = array_values($schema['questions'] ?? []);
        $schema['questions'][] = $question;

        return $schema;
    }
}
