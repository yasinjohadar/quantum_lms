<?php

namespace App\Services;

use App\Models\QuestionAttempt;
use App\Models\QuestionAnswer;
use App\Models\Question;
use App\Models\QuizAnswer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuestionAttemptService
{
    /**
     * إنشاء محاولة جديدة للإجابة على سؤال
     */
    public function createAttempt($userId, $questionId, $lessonId = null, $timeLimit = null): QuestionAttempt
    {
        // الحصول على آخر رقم محاولة
        $lastAttempt = QuestionAttempt::where('user_id', $userId)
            ->where('question_id', $questionId)
            ->orderBy('attempt_number', 'desc')
            ->first();

        $attemptNumber = $lastAttempt ? $lastAttempt->attempt_number + 1 : 1;

        // التحقق من وجود محاولة جارية
        $inProgressAttempt = QuestionAttempt::where('user_id', $userId)
            ->where('question_id', $questionId)
            ->where('status', 'in_progress')
            ->first();

        if ($inProgressAttempt) {
            return $inProgressAttempt;
        }

        $question = Question::findOrFail($questionId);

        $attempt = QuestionAttempt::create([
            'user_id' => $userId,
            'question_id' => $questionId,
            'lesson_id' => $lessonId,
            'attempt_number' => $attemptNumber,
            'started_at' => now(),
            'status' => 'in_progress',
            'time_limit' => $timeLimit,
            'max_score' => $question->default_points ?? 0,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $attempt;
    }

    /**
     * حفظ الإجابة
     */
    public function saveAnswer($attemptId, $answerData): QuestionAnswer
    {
        $attempt = QuestionAttempt::findOrFail($attemptId);

        if ($attempt->status !== 'in_progress') {
            throw new \Exception('لا يمكن تعديل محاولة مكتملة');
        }

        $answer = QuestionAnswer::updateOrCreate(
            [
                'attempt_id' => $attemptId,
                'question_id' => $attempt->question_id,
            ],
            [
                'answer' => $answerData['answer'] ?? null,
                'answer_text' => $answerData['answer_text'] ?? null,
                'selected_options' => $answerData['selected_options'] ?? null,
                'matching_pairs' => $answerData['matching_pairs'] ?? null,
                'ordering' => $answerData['ordering'] ?? null,
                'numeric_answer' => $answerData['numeric_answer'] ?? null,
                'fill_blanks_answers' => $answerData['fill_blanks_answers'] ?? null,
                'options_order' => $answerData['options_order'] ?? null,
                'answered_at' => now(),
                'time_spent' => $attempt->started_at->diffInSeconds(now()),
                'max_points' => $attempt->max_score,
            ]
        );

        $attempt->updateActivity();

        return $answer;
    }

    /**
     * إرسال الإجابة النهائية
     */
    public function submitAnswer($attemptId): QuestionAttempt
    {
        $attempt = QuestionAttempt::findOrFail($attemptId);

        if ($attempt->status !== 'in_progress') {
            throw new \Exception('لا يمكن إرسال محاولة مكتملة');
        }

        DB::beginTransaction();
        try {
            $attempt->finish();
            
            // تصحيح الإجابة تلقائياً
            $this->gradeAnswer($attemptId);
            
            DB::commit();
            return $attempt->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error submitting answer: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * تصحيح الإجابة تلقائياً
     */
    public function gradeAnswer($attemptId): void
    {
        $attempt = QuestionAttempt::with('question')->findOrFail($attemptId);
        $answer = QuestionAnswer::where('attempt_id', $attemptId)->first();

        if (!$answer) {
            return;
        }

        $question = $attempt->question;
        $needsManualGrading = in_array($question->type, ['essay', 'short_answer']);

        if ($needsManualGrading) {
            $answer->needs_manual_grading = true;
            $answer->is_graded = false;
            $answer->save();
            return;
        }

        // تصحيح تلقائي حسب نوع السؤال
        $isCorrect = false;
        $pointsEarned = 0;

        switch ($question->type) {
            case 'single_choice':
                $isCorrect = $this->gradeSingleChoice($question, $answer);
                break;
            case 'multiple_choice':
                $isCorrect = $this->gradeMultipleChoice($question, $answer);
                break;
            case 'true_false':
                $isCorrect = $this->gradeTrueFalse($question, $answer);
                break;
            case 'matching':
                $isCorrect = $this->gradeMatching($question, $answer);
                break;
            case 'ordering':
                $isCorrect = $this->gradeOrdering($question, $answer);
                break;
            case 'numerical':
                $isCorrect = $this->gradeNumerical($question, $answer);
                break;
            case 'fill_blanks':
                $isCorrect = $this->gradeFillBlanks($question, $answer);
                break;
        }

        $pointsEarned = $isCorrect ? $answer->max_points : 0;

        $answer->is_correct = $isCorrect;
        $answer->points_earned = $pointsEarned;
        $answer->is_graded = true;
        $answer->graded_at = now();
        $answer->save();

        $attempt->calculateScore();
    }

    /**
     * تصحيح اختيار واحد
     */
    private function gradeSingleChoice($question, $answer): bool
    {
        $correctOption = $question->correctOptions()->first();
        if (!$correctOption) {
            return false;
        }

        $selectedOptionId = $answer->selected_options[0] ?? null;
        return $selectedOptionId == $correctOption->id;
    }

    /**
     * تصحيح اختيار متعدد
     */
    private function gradeMultipleChoice($question, $answer): bool
    {
        $correctOptionIds = $question->correctOptions()->pluck('id')->sort()->values()->toArray();
        $selectedOptionIds = collect($answer->selected_options ?? [])->sort()->values()->toArray();

        return $correctOptionIds === $selectedOptionIds;
    }

    /**
     * تصحيح صح/خطأ
     */
    private function gradeTrueFalse($question, $answer): bool
    {
        $correctOption = $question->correctOptions()->first();
        if (!$correctOption) {
            return false;
        }

        $selectedOptionId = $answer->selected_options[0] ?? null;
        return $selectedOptionId == $correctOption->id;
    }

    /**
     * تصحيح المطابقة
     */
    private function gradeMatching($question, $answer): bool
    {
        // يجب أن تكون جميع الأزواج صحيحة
        $correctPairs = $question->options()
            ->where('is_correct', true)
            ->get()
            ->map(function($option) {
                return [
                    'left' => $option->content,
                    'right' => $option->matching_content,
                ];
            })
            ->sortBy('left')
            ->values()
            ->toArray();

        $userPairs = collect($answer->matching_pairs ?? [])
            ->sortBy('left')
            ->values()
            ->toArray();

        return $correctPairs === $userPairs;
    }

    /**
     * تصحيح الترتيب
     */
    private function gradeOrdering($question, $answer): bool
    {
        $correctOrder = $question->options()
            ->orderBy('order')
            ->pluck('id')
            ->toArray();

        $userOrder = $answer->ordering ?? [];

        return $correctOrder === $userOrder;
    }

    /**
     * تصحيح الإجابة الرقمية
     */
    private function gradeNumerical($question, $answer): bool
    {
        $correctOption = $question->correctOptions()->first();
        if (!$correctOption || !$answer->numeric_answer) {
            return false;
        }

        $correctValue = (float) $correctOption->content;
        $userValue = (float) $answer->numeric_answer;
        $tolerance = (float) ($question->tolerance ?? 0);

        return abs($correctValue - $userValue) <= $tolerance;
    }

    /**
     * تصحيح ملء الفراغات
     */
    private function gradeFillBlanks($question, $answer): bool
    {
        $correctAnswers = $question->blank_answers ?? [];
        $userAnswers = $answer->fill_blanks_answers ?? [];

        if (count($correctAnswers) !== count($userAnswers)) {
            return false;
        }

        foreach ($correctAnswers as $index => $correctAnswer) {
            $userAnswer = $userAnswers[$index] ?? '';
            $caseSensitive = $question->case_sensitive ?? false;

            if (!$caseSensitive) {
                $correctAnswer = mb_strtolower($correctAnswer);
                $userAnswer = mb_strtolower($userAnswer);
            }

            if (trim($correctAnswer) !== trim($userAnswer)) {
                return false;
            }
        }

        return true;
    }
}

