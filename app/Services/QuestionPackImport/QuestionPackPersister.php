<?php

namespace App\Services\QuestionPackImport;

use App\DataTransferObjects\QuestionPack\QuestionPackQuestionData;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Unit;
use App\Support\QuestionMarkupFormatter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QuestionPackPersister
{
    /**
     * @param  array<int, QuestionPackQuestionData>|Collection<int, QuestionPackQuestionData>  $questions
     * @return array{count: int, question_ids: array<int, int>}
     */
    public function persist(Collection|array $questions, ?int $subjectId, ?int $unitId, int $userId): array
    {
        $questions = $questions instanceof Collection ? $questions : collect($questions);

        if ($unitId && $subjectId) {
            $valid = Unit::query()
                ->whereKey($unitId)
                ->whereHas('section', fn ($q) => $q->where('subject_id', $subjectId))
                ->exists();
            if (! $valid) {
                throw new QuestionPackParseException('الوحدة لا تنتمي للمادة المحددة.');
            }
        }

        $count = 0;
        $questionIds = [];

        DB::transaction(function () use ($questions, $subjectId, $unitId, $userId, &$count, &$questionIds) {
            foreach ($questions as $dto) {
                if ($dto->targetType === 'fill_blanks') {
                    $questionIds[] = $this->persistFillBlanks($dto, $subjectId, $unitId, $userId);
                } else {
                    $questionIds[] = $this->persistSingleChoice($dto, $subjectId, $unitId, $userId);
                }
                $count++;
            }
        });

        return [
            'count' => $count,
            'question_ids' => $questionIds,
        ];
    }

    protected function persistFillBlanks(
        QuestionPackQuestionData $dto,
        ?int $subjectId,
        ?int $unitId,
        int $userId
    ): int {
        $blankAnswers = array_map(
            fn ($answer) => is_string($answer) ? QuestionMarkupFormatter::normalizeForStorage($answer) : $answer,
            $dto->blankAnswers()
        );
        if ($blankAnswers === []) {
            throw new QuestionPackParseException("السؤال رقم {$dto->number}: إجابة الفراغ مفقودة.");
        }

        $question = Question::create([
            'type' => 'fill_blanks',
            'title' => QuestionMarkupFormatter::normalizeForStorage($dto->title),
            'content' => QuestionMarkupFormatter::normalizeForStorage($dto->title),
            'explanation' => QuestionMarkupFormatter::normalizeForStorage($dto->explanation),
            'difficulty' => $dto->difficulty,
            'default_points' => $dto->points,
            'blank_answers' => $blankAnswers,
            'is_active' => true,
            'created_by' => $userId,
            'subject_id' => $subjectId,
        ]);

        if ($unitId) {
            $question->units()->sync([$unitId]);
        }

        return (int) $question->id;
    }

    protected function persistSingleChoice(
        QuestionPackQuestionData $dto,
        ?int $subjectId,
        ?int $unitId,
        int $userId
    ): int {
        $question = Question::create([
            'type' => 'single_choice',
            'title' => QuestionMarkupFormatter::normalizeForStorage($dto->title),
            'content' => QuestionMarkupFormatter::normalizeForStorage($dto->title),
            'explanation' => QuestionMarkupFormatter::normalizeForStorage($dto->explanation),
            'difficulty' => $dto->difficulty,
            'default_points' => $dto->points,
            'is_active' => true,
            'created_by' => $userId,
            'subject_id' => $subjectId,
        ]);

        $order = 1;
        foreach (['A', 'B', 'C', 'D'] as $letter) {
            if (empty($dto->options[$letter])) {
                continue;
            }

            QuestionOption::create([
                'question_id' => $question->id,
                'content' => QuestionMarkupFormatter::normalizeForStorage($dto->options[$letter]),
                'is_correct' => strtoupper($dto->correctLetter) === $letter,
                'order' => $order++,
            ]);
        }

        if ($unitId) {
            $question->units()->sync([$unitId]);
        }

        return (int) $question->id;
    }
}
