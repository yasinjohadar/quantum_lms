<?php

namespace App\Services\QuestionPackImport;

use App\DataTransferObjects\QuestionPack\QuestionPackQuestionData;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QuestionPackPersister
{
    /**
     * @param  array<int, QuestionPackQuestionData>|Collection<int, QuestionPackQuestionData>  $questions
     */
    public function persist(Collection|array $questions, ?int $subjectId, ?int $unitId, int $userId): int
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

        DB::transaction(function () use ($questions, $subjectId, $unitId, $userId, &$count) {
            foreach ($questions as $dto) {
                if ($dto->targetType === 'fill_blanks') {
                    $this->persistFillBlanks($dto, $subjectId, $unitId, $userId);
                } else {
                    $this->persistSingleChoice($dto, $subjectId, $unitId, $userId);
                }
                $count++;
            }
        });

        return $count;
    }

    protected function persistFillBlanks(
        QuestionPackQuestionData $dto,
        ?int $subjectId,
        ?int $unitId,
        int $userId
    ): void {
        $blankAnswers = $dto->blankAnswers();
        if ($blankAnswers === []) {
            throw new QuestionPackParseException("السؤال رقم {$dto->number}: إجابة الفراغ مفقودة.");
        }

        $question = Question::create([
            'type' => 'fill_blanks',
            'title' => $dto->title,
            'content' => $dto->title,
            'explanation' => $dto->explanation,
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
    }

    protected function persistSingleChoice(
        QuestionPackQuestionData $dto,
        ?int $subjectId,
        ?int $unitId,
        int $userId
    ): void {
        $question = Question::create([
            'type' => 'single_choice',
            'title' => $dto->title,
            'content' => $dto->title,
            'explanation' => $dto->explanation,
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
                'content' => $dto->options[$letter],
                'is_correct' => strtoupper($dto->correctLetter) === $letter,
                'order' => $order++,
            ]);
        }

        if ($unitId) {
            $question->units()->sync([$unitId]);
        }
    }
}
