<?php

namespace App\Services;

use App\Models\QuestionAttempt;
use App\Models\QuestionAnswer;
use App\Models\Question;
use App\Models\QuizAnswer;
use App\Services\GamificationService;
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
            
            // ربط مع نظام التحفيز
            $gamificationService = app(GamificationService::class);
            $gamificationService->processQuestionCompletion($attempt->fresh());
            
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
        
        // التحقق من إمكانية استخدام AI grading للأسئلة المقالية
        if ($question->type === 'essay') {
            $aiGradingEnabled = \App\Models\SystemSetting::get('ai_essay_grading_enabled', false);
            
            if ($aiGradingEnabled && !empty($answer->answer_text)) {
                try {
                    $answer->aiGrade();
                    return;
                } catch (\Exception $e) {
                    \Log::error('AI grading failed in QuestionAttemptService: ' . $e->getMessage());
                    // في حالة فشل AI grading، ننتقل للتصحيح اليدوي
                }
            }
        }
        
        $needsManualGrading = in_array($question->type, ['essay', 'short_answer']);

        if ($needsManualGrading) {
            $answer->needs_manual_grading = true;
            $answer->is_graded = false;
            $answer->save();
            return;
        }

        // تصحيح تلقائي حسب نوع السؤال
        $isCorrect = false;
        $pointsEarned = null;
        $isPartiallyCorrect = false;

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
                $result = $this->gradeMatching($question, $answer);
                $isCorrect = $result['is_correct'];
                $pointsEarned = $result['points'];
                $isPartiallyCorrect = ! $isCorrect && $pointsEarned > 0;
                break;
            case 'ordering':
                $result = $this->gradeOrdering($question, $answer);
                $isCorrect = $result['is_correct'];
                $pointsEarned = $result['points'];
                $isPartiallyCorrect = ! $isCorrect && $pointsEarned > 0;
                break;
            case 'numerical':
                $isCorrect = $this->gradeNumerical($question, $answer);
                break;
            case 'fill_blanks':
                $isCorrect = $this->gradeFillBlanks($question, $answer);
                break;
            case 'drag_drop':
                $result = $this->gradeDragDrop($question, $answer);
                $isCorrect = $result['is_correct'];
                $pointsEarned = $result['points'];
                $isPartiallyCorrect = ! $isCorrect && $pointsEarned > 0;
                break;
        }

        if ($pointsEarned === null) {
            $pointsEarned = $isCorrect ? $answer->max_points : 0;
        }

        $answer->is_correct = $isCorrect;
        $answer->is_partially_correct = $isPartiallyCorrect;
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
     * تصحيح المطابقة — احتساب جزئي لكل زوج صحيح: (صحيح / إجمالي) × max_points
     * صيغة الإجابة: { option_id: match_target_text }
     */
    private function gradeMatching($question, $answer): array
    {
        $options = $question->relationLoaded('options')
            ? $question->options
            : $question->options()->get();
        $pairs = $answer->matching_pairs ?? [];

        if (empty($pairs)) {
            return ['is_correct' => false, 'points' => 0.0];
        }

        $correctPairs = 0;
        $totalPairs = $options->count();

        if ($totalPairs === 0) {
            return ['is_correct' => false, 'points' => 0.0];
        }

        foreach ($options as $option) {
            $submitted = $pairs[$option->id] ?? $pairs[(string) $option->id] ?? null;
            if ($submitted !== null && $submitted == $option->match_target) {
                $correctPairs++;
            }
        }

        $maxPoints = (float) ($answer->max_points ?? 0);
        $points = $maxPoints > 0
            ? ($correctPairs / $totalPairs) * $maxPoints
            : 0.0;
        $isCorrect = $correctPairs === $totalPairs;

        return ['is_correct' => $isCorrect, 'points' => $points];
    }

    /**
     * تصحيح الترتيب — احتساب جزئي لكل موضع صحيح مقابل correct_order
     * صيغة الإجابة: [optionId, optionId, ...] بالترتيب الذي وضعه الطالب
     */
    private function gradeOrdering($question, $answer): array
    {
        $options = $question->relationLoaded('options')
            ? $question->options->sortBy('correct_order')->values()
            : $question->options()->orderBy('correct_order')->get();

        $ordering = $answer->ordering ?? [];
        if (is_string($ordering)) {
            $decoded = json_decode($ordering, true);
            if (is_array($decoded)) {
                $ordering = $decoded;
            } else {
                $ordering = array_values(array_filter(array_map('trim', explode(',', $ordering))));
            }
        }

        if (empty($ordering)) {
            return ['is_correct' => false, 'points' => 0.0];
        }

        $correctPositions = 0;
        $totalPositions = $options->count();

        if ($totalPositions === 0) {
            return ['is_correct' => false, 'points' => 0.0];
        }

        foreach ($options as $index => $option) {
            if (isset($ordering[$index]) && $ordering[$index] == $option->id) {
                $correctPositions++;
            }
        }

        $maxPoints = (float) ($answer->max_points ?? 0);
        $points = $maxPoints > 0
            ? ($correctPositions / $totalPositions) * $maxPoints
            : 0.0;
        $isCorrect = $correctPositions === $totalPositions;

        return ['is_correct' => $isCorrect, 'points' => $points];
    }

    /**
     * تصحيح الإجابة الرقمية — |user − correct| ≤ tolerance
     */
    private function gradeNumerical($question, $answer): bool
    {
        $correctOption = $question->relationLoaded('correctOptions')
            ? $question->correctOptions->first()
            : $question->correctOptions()->first();

        if (! $correctOption || $answer->numeric_answer === null || $answer->numeric_answer === '') {
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

    /**
     * تصحيح السحب والإفلات — { option_id: zone_label } مقابل match_target
     */
    private function gradeDragDrop($question, $answer): array
    {
        $options = $question->relationLoaded('options')
            ? $question->options
            : $question->options()->get();

        $assignments = $answer->answer ?? [];
        if (is_string($assignments)) {
            $decoded = json_decode($assignments, true);
            $assignments = is_array($decoded) ? $decoded : [];
        }

        if (empty($assignments)) {
            return ['is_correct' => false, 'points' => 0.0];
        }

        $correctCount = 0;
        $totalItems = $options->count();

        if ($totalItems === 0) {
            return ['is_correct' => false, 'points' => 0.0];
        }

        foreach ($options as $option) {
            $submitted = $assignments[$option->id] ?? $assignments[(string) $option->id] ?? null;
            if ($submitted !== null && $submitted !== '' && $submitted == $option->match_target) {
                $correctCount++;
            }
        }

        $maxPoints = (float) ($answer->max_points ?? 0);
        $points = $maxPoints > 0
            ? ($correctCount / $totalItems) * $maxPoints
            : 0.0;
        $isCorrect = $correctCount === $totalItems;

        return ['is_correct' => $isCorrect, 'points' => $points];
    }
}


