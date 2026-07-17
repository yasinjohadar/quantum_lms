<?php

namespace App\Services\ExtensionImport;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Unit;
use App\Support\QuestionMarkupFormatter;
use Illuminate\Support\Facades\DB;

class ExtensionQuestionPersister
{
    /**
     * @param  array<int, array<string, mixed>>  $questions
     * @return array{imported: int, skipped: int, errors: array<int, array{index: int, message: string}>}
     */
    public function persistMany(array $questions, int $subjectId, ?int $unitId, int $userId): array
    {
        if ($unitId) {
            $valid = Unit::query()
                ->whereKey($unitId)
                ->whereHas('section', fn ($q) => $q->where('subject_id', $subjectId))
                ->exists();

            if (! $valid) {
                throw new ExtensionImportException('الوحدة لا تنتمي للمادة المحددة.');
            }
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($questions as $index => $payload) {
            try {
                DB::transaction(function () use ($payload, $subjectId, $unitId, $userId) {
                    $this->persistOne($payload, $subjectId, $unitId, $userId);
                });
                $imported++;
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = [
                    'index' => $index,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function persistOne(array $payload, int $subjectId, ?int $unitId, int $userId): void
    {
        $type = $payload['type'];
        $title = QuestionMarkupFormatter::normalizeForStorage($payload['title'] ?? '');
        $content = QuestionMarkupFormatter::normalizeForStorage($payload['content'] ?? $payload['title'] ?? '');
        $explanation = isset($payload['explanation'])
            ? QuestionMarkupFormatter::normalizeForStorage($payload['explanation'])
            : null;

        $blankAnswers = null;
        if ($type === 'fill_blanks') {
            $blankAnswers = array_map(
                fn ($answer) => is_string($answer) ? QuestionMarkupFormatter::normalizeForStorage($answer) : $answer,
                $payload['blank_answers'] ?? []
            );
        }

        $question = Question::create([
            'type' => $type,
            'title' => $title,
            'content' => $content,
            'explanation' => $explanation,
            'difficulty' => $payload['difficulty'] ?? 'medium',
            'default_points' => $payload['default_points'] ?? 1,
            'blank_answers' => $blankAnswers,
            'case_sensitive' => $payload['case_sensitive'] ?? false,
            'is_active' => true,
            'created_by' => $userId,
            'subject_id' => $subjectId,
        ]);

        if ($unitId) {
            $question->units()->sync([$unitId]);
        }

        if ($type === 'fill_blanks') {
            $blanks = $blankAnswers ?? [];
            if ($blanks === []) {
                throw new ExtensionImportException('سؤال ملء الفراغات يحتاج إجابة واحدة على الأقل.');
            }

            return;
        }

        if ($question->has_options) {
            $order = 1;
            foreach ($payload['options'] ?? [] as $option) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'content' => QuestionMarkupFormatter::normalizeForStorage($option['text'] ?? ''),
                    'is_correct' => (bool) $option['is_correct'],
                    'order' => $order++,
                ]);
            }
        }

        if ($type === 'short_answer' || $type === 'numerical') {
            $answer = $payload['options'][0]['text'] ?? null;
            if ($answer) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'content' => QuestionMarkupFormatter::normalizeForStorage($answer),
                    'is_correct' => true,
                    'order' => 1,
                ]);
            }
        }
    }
}
