<?php

namespace App\Services\MathQuestionImport;

use App\DataTransferObjects\QuestionPack\QuestionPackQuestionData;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Unit;
use App\Services\QuestionPackImport\QuestionPackParseException;
use App\Support\QuestionMarkupFormatter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * يحفظ أسئلة رياضيات (اختيار واحد A–D) مستوردة من نفس صيغة حزمة الأسئلة
 * (Question/Hint/Option A-D/Correct Answer/Rationale)، مع دمج التلميح والتفسير
 * في حقل شرح واحد، وتطبيع كل النصوص عبر QuestionMarkupFormatter لضمان عرض
 * LaTeX الصحيح عبر KaTeX عند العرض لاحقاً.
 */
class MathQuestionPackPersister
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
                $questionIds[] = $this->persistSingleChoice($dto, $subjectId, $unitId, $userId);
                $count++;
            }
        });

        return [
            'count' => $count,
            'question_ids' => $questionIds,
        ];
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
            'explanation' => QuestionMarkupFormatter::normalizeForStorage($this->combineHintAndRationale($dto)),
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

    /**
     * يدمج التلميح (Hint) والتفسير (Rationale) في نص شرح واحد، لأن نموذج
     * السؤال الحالي لا يملك حقل "تلميح" منفصلاً — يظهر هذا الشرح للطالب
     * في تقرير النتائج بعد الإجابة.
     */
    protected function combineHintAndRationale(QuestionPackQuestionData $dto): string
    {
        $parts = [];

        $hint = trim($dto->hint);
        if ($hint !== '') {
            $parts[] = "تلميح: {$hint}";
        }

        $rationale = trim($dto->explanation);
        if ($rationale !== '') {
            $parts[] = "التفسير: {$rationale}";
        }

        return implode("\n\n", $parts);
    }
}
