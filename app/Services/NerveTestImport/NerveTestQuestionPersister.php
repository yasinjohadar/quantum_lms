<?php

namespace App\Services\NerveTestImport;

use App\DataTransferObjects\NerveTest\NerveTestQuestionData;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NerveTestQuestionPersister
{
    /**
     * @param  array<int, NerveTestQuestionData>|Collection<int, NerveTestQuestionData>  $questions
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
                throw new NerveTestParseException('الوحدة لا تنتمي للمادة المحددة.');
            }
        }

        $count = 0;
        $questionIds = [];

        DB::transaction(function () use ($questions, $subjectId, $unitId, $userId, &$count, &$questionIds) {
            foreach ($questions as $dto) {
                $question = Question::create([
                    'type' => $dto->type,
                    'title' => $dto->title,
                    'content' => $dto->title,
                    'explanation' => $dto->explanation,
                    'difficulty' => $dto->difficulty,
                    'default_points' => $dto->points,
                    'is_active' => true,
                    'created_by' => $userId,
                    'subject_id' => $subjectId,
                ]);

                $this->createTrueFalseOptions($question, $dto);

                if ($unitId) {
                    $question->units()->sync([$unitId]);
                }

                $questionIds[] = (int) $question->id;
                $count++;
            }
        });

        return [
            'count' => $count,
            'question_ids' => $questionIds,
        ];
    }

    protected function createTrueFalseOptions(Question $question, NerveTestQuestionData $dto): void
    {
        QuestionOption::create([
            'question_id' => $question->id,
            'content' => $dto->optionA,
            'is_correct' => $dto->correctLetter === 'A',
            'order' => 1,
        ]);

        QuestionOption::create([
            'question_id' => $question->id,
            'content' => $dto->optionB,
            'is_correct' => $dto->correctLetter === 'B',
            'order' => 2,
        ]);
    }
}
