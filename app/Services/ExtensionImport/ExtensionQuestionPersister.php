<?php

namespace App\Services\ExtensionImport;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Unit;
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
        $title = $payload['title'];
        $content = $payload['content'] ?? $title;

        $question = Question::create([
            'type' => $type,
            'title' => $title,
            'content' => $content,
            'explanation' => $payload['explanation'] ?? null,
            'difficulty' => $payload['difficulty'] ?? 'medium',
            'default_points' => $payload['default_points'] ?? 1,
            'blank_answers' => $type === 'fill_blanks' ? ($payload['blank_answers'] ?? []) : null,
            'case_sensitive' => $payload['case_sensitive'] ?? false,
            'is_active' => true,
            'created_by' => $userId,
            'subject_id' => $subjectId,
        ]);

        if ($unitId) {
            $question->units()->sync([$unitId]);
        }

        if ($type === 'fill_blanks') {
            $blanks = $payload['blank_answers'] ?? [];
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
                    'content' => $option['text'],
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
                    'content' => $answer,
                    'is_correct' => true,
                    'order' => 1,
                ]);
            }
        }
    }
}
