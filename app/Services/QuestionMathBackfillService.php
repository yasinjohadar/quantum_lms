<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Support\QuestionMarkupFormatter;

/**
 * إصلاح شامل لعرض LaTeX عبر بنك الأسئلة الحالي بالكامل: يعيد تطبيع كل نص مخزَّن
 * (عنوان/محتوى/شرح/خيارات) عبر QuestionMarkupFormatter::deepNormalizeForStorage()
 * حتى تُصحَّح أسئلة قديمة خُزِّنت بمحدِّدات $...$ خاطئة من إصدارات سابقة من
 * الصانع (تظهر كلاتيك خام أو أخطاء KaTeX حمراء للطالب).
 *
 * مصمَّم للعمل على دفعات صغيرة (batch) يستدعيها المتصفح عبر AJAX متكرر بدل
 * معالجة كل بنك الأسئلة في طلب واحد قد يتجاوز مهلة الخادم — كل استدعاء يعالج
 * دفعة واحدة ويرجع "المؤشر" (آخر id) لاستدعاء الدفعة التالية.
 */
class QuestionMathBackfillService
{
    public function totals(): array
    {
        return [
            'questions' => Question::query()->count(),
            'options' => QuestionOption::query()->count(),
        ];
    }

    /**
     * @return array{scanned: int, updated: int, next_after_id: int|null, done: bool}
     */
    public function processQuestionsBatch(int $afterId, int $limit): array
    {
        $questions = Question::query()
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->limit($limit)
            ->get(['id', 'title', 'content', 'explanation', 'blank_answers']);

        $scanned = $questions->count();
        $updated = 0;
        $lastId = $afterId;

        foreach ($questions as $question) {
            $lastId = $question->id;
            $changes = [];

            foreach (['title', 'content', 'explanation'] as $field) {
                $original = $question->{$field};
                if (! is_string($original) || trim($original) === '') {
                    continue;
                }
                $normalized = QuestionMarkupFormatter::deepNormalizeForStorage($original);
                if ($normalized !== $original) {
                    $changes[$field] = $normalized;
                }
            }

            if (is_array($question->blank_answers) && $question->blank_answers !== []) {
                $normalizedBlanks = array_map(
                    fn ($answer) => is_string($answer)
                        ? QuestionMarkupFormatter::deepNormalizeForStorage($answer)
                        : $answer,
                    $question->blank_answers
                );
                if ($normalizedBlanks !== $question->blank_answers) {
                    $changes['blank_answers'] = $normalizedBlanks;
                }
            }

            if ($changes !== []) {
                $question->fill($changes);
                $question->save();
                $updated++;
            }
        }

        return [
            'scanned' => $scanned,
            'updated' => $updated,
            'next_after_id' => $lastId,
            'done' => $scanned < $limit,
        ];
    }

    /**
     * @return array{scanned: int, updated: int, next_after_id: int|null, done: bool}
     */
    public function processOptionsBatch(int $afterId, int $limit): array
    {
        $options = QuestionOption::query()
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->limit($limit)
            ->get(['id', 'content', 'match_target', 'feedback']);

        $scanned = $options->count();
        $updated = 0;
        $lastId = $afterId;

        foreach ($options as $option) {
            $lastId = $option->id;
            $changes = [];

            foreach (['content', 'match_target', 'feedback'] as $field) {
                $original = $option->{$field};
                if (! is_string($original) || trim($original) === '') {
                    continue;
                }
                $normalized = QuestionMarkupFormatter::deepNormalizeForStorage($original);
                if ($normalized !== $original) {
                    $changes[$field] = $normalized;
                }
            }

            if ($changes !== []) {
                $option->fill($changes);
                $option->save();
                $updated++;
            }
        }

        return [
            'scanned' => $scanned,
            'updated' => $updated,
            'next_after_id' => $lastId,
            'done' => $scanned < $limit,
        ];
    }
}
