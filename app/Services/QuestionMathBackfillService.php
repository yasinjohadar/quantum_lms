<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Services\AI\MathLatexRepairService;
use App\Support\QuestionMarkupFormatter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
    public function __construct(
        private MathLatexRepairService $mathLatexRepair,
    ) {}

    public function totals(): array
    {
        return [
            'questions' => Question::query()->count(),
            'options' => QuestionOption::query()->count(),
        ];
    }

    /**
     * حالة أداة الإصلاح الذكي بالذكاء الاصطناعي: هل هناك موديل متاح، وتقدير عدد
     * الأسئلة المشتبه بها (يُحسب فقط عند الطلب لأنه أبطأ من totals() العادية).
     */
    public function aiRepairStatus(): array
    {
        return [
            'has_model' => $this->mathLatexRepair->hasAvailableModel(),
            'suspicious_questions' => $this->countSuspiciousQuestions(),
            'total_questions' => Question::query()->count(),
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

    /**
     * عدد الأسئلة التي لا تزال "مشتبهاً بها" (بعد التطبيع المجاني بالأنماط النصية)
     * وتحتاج مراجعة الذكاء الاصطناعي — يُستخدم لعرض تقدير حجم العمل قبل بدء
     * المرحلة الباهظة نسبياً (استدعاءات AI) في واجهة الإدارة.
     */
    public function countSuspiciousQuestions(): int
    {
        return Question::query()
            ->with('options:id,question_id,content,match_target,feedback')
            ->get(['id', 'title', 'content', 'explanation'])
            ->filter(fn (Question $question) => $this->questionLooksSuspicious($question))
            ->count();
    }

    /**
     * @return array{scanned: int, ai_checked: int, updated: int, next_after_id: int|null, done: bool, error: string|null}
     */
    public function processAiRepairBatch(int $afterId, int $limit): array
    {
        if (! $this->mathLatexRepair->hasAvailableModel()) {
            return [
                'scanned' => 0,
                'ai_checked' => 0,
                'updated' => 0,
                'next_after_id' => $afterId,
                'done' => true,
                'error' => 'لا يوجد موديل AI متاح. أضف/فعِّل موديلاً في إدارة الذكاء الاصطناعي أولاً.',
            ];
        }

        $questions = Question::query()
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->limit($limit)
            ->with('options')
            ->get(['id', 'title', 'content', 'explanation']);

        $scanned = $questions->count();
        $aiChecked = 0;
        $updated = 0;
        $lastId = $afterId;
        $error = null;

        foreach ($questions as $question) {
            $lastId = $question->id;

            // خطوة مجانية أولى: طبّق التطبيع بالأنماط النصية أولاً (بلا أي تكلفة)
            // حتى لو لم تُشغَّل مرحلة "الإصلاح الشامل" الحرة على هذا السؤال بعد.
            if ($this->applyRegexNormalization($question)) {
                $updated++;
            }

            if (! $this->questionLooksSuspicious($question)) {
                continue;
            }

            $aiChecked++;

            try {
                $corrected = $this->mathLatexRepair->repairQuestionMath($question);
            } catch (\Throwable $e) {
                Log::error('QuestionMathBackfillService: AI repair failed', [
                    'question_id' => $question->id,
                    'error' => $e->getMessage(),
                ]);
                $error = $e->getMessage();

                break;
            }

            if ($corrected !== [] && $this->applyCorrectedFields($question, $corrected)) {
                $updated++;
            }
        }

        return [
            'scanned' => $scanned,
            'ai_checked' => $aiChecked,
            'updated' => $updated,
            'next_after_id' => $lastId,
            'done' => $error === null && $scanned < $limit,
            'error' => $error,
        ];
    }

    private function questionLooksSuspicious(Question $question): bool
    {
        foreach (['title', 'content', 'explanation'] as $field) {
            if (QuestionMarkupFormatter::hasSuspiciousBareLatex($question->{$field})) {
                return true;
            }
        }

        foreach ($question->options as $option) {
            foreach (['content', 'match_target', 'feedback'] as $field) {
                if (QuestionMarkupFormatter::hasSuspiciousBareLatex($option->{$field})) {
                    return true;
                }
            }
        }

        return false;
    }

    private function applyRegexNormalization(Question $question): bool
    {
        $changed = false;
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

        if ($changes !== []) {
            $question->fill($changes);
            $question->save();
            $changed = true;
        }

        foreach ($question->options as $option) {
            $optionChanges = [];
            foreach (['content', 'match_target', 'feedback'] as $field) {
                $original = $option->{$field};
                if (! is_string($original) || trim($original) === '') {
                    continue;
                }
                $normalized = QuestionMarkupFormatter::deepNormalizeForStorage($original);
                if ($normalized !== $original) {
                    $optionChanges[$field] = $normalized;
                }
            }
            if ($optionChanges !== []) {
                $option->fill($optionChanges);
                $option->save();
                $changed = true;
            }
        }

        return $changed;
    }

    /**
     * @param  array<string, string>  $corrected  خريطة بمفاتيح title/content/explanation/option_{id}_{field}
     */
    private function applyCorrectedFields(Question $question, array $corrected): bool
    {
        $changed = false;

        DB::transaction(function () use ($question, $corrected, &$changed) {
            $questionChanges = [];
            foreach (['title', 'content', 'explanation'] as $field) {
                if (isset($corrected[$field]) && $corrected[$field] !== $question->{$field}) {
                    $questionChanges[$field] = $corrected[$field];
                }
            }

            if ($questionChanges !== []) {
                $question->fill($questionChanges);
                $question->save();
                $changed = true;
            }

            foreach ($question->options as $option) {
                $optionChanges = [];
                foreach (['content', 'match_target', 'feedback'] as $field) {
                    $key = 'option_'.$option->id.'_'.$field;
                    if (isset($corrected[$key]) && $corrected[$key] !== $option->{$field}) {
                        $optionChanges[$field] = $corrected[$key];
                    }
                }

                if ($optionChanges !== []) {
                    $option->fill($optionChanges);
                    $option->save();
                    $changed = true;
                }
            }
        });

        return $changed;
    }
}
