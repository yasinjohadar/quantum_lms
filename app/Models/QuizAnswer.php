<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'attempt_id',
        'question_id',
        'answer',
        'answer_text',
        'selected_options',
        'matching_pairs',
        'ordering',
        'fill_blanks_answers',
        'drag_drop_assignments',
        'numeric_answer',
        'is_correct',
        'is_partially_correct',
        'points_earned',
        'max_points',
        'feedback',
        'needs_manual_grading',
        'is_graded',
        'graded_by',
        'graded_at',
        'ai_graded',
        'ai_grading_data',
        'ai_graded_at',
        'ai_grading_model_id',
        'answered_at',
        'time_spent',
        'options_order',
    ];

    protected $casts = [
        'answer' => 'array',
        'selected_options' => 'array',
        'matching_pairs' => 'array',
        'ordering' => 'array',
        'fill_blanks_answers' => 'array',
        'drag_drop_assignments' => 'array',
        'numeric_answer' => 'decimal:6',
        'is_correct' => 'boolean',
        'is_partially_correct' => 'boolean',
        'points_earned' => 'decimal:2',
        'max_points' => 'decimal:2',
        'needs_manual_grading' => 'boolean',
        'is_graded' => 'boolean',
        'graded_at' => 'datetime',
        'answered_at' => 'datetime',
        'time_spent' => 'integer',
        'options_order' => 'array',
        'ai_graded' => 'boolean',
        'ai_grading_data' => 'array',
        'ai_graded_at' => 'datetime',
    ];

    /**
     * العلاقات
     */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function aiGradingModel(): BelongsTo
    {
        return $this->belongsTo(AIModel::class, 'ai_grading_model_id');
    }

    /**
     * Scopes
     */
    public function scopeGraded($query)
    {
        return $query->where('is_graded', true);
    }

    public function scopeUngraded($query)
    {
        return $query->where('is_graded', false);
    }

    public function scopeNeedsManualGrading($query)
    {
        return $query->where('needs_manual_grading', true)->where('is_graded', false);
    }

    public function scopeAiGraded($query)
    {
        return $query->where('ai_graded', true);
    }

    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    public function scopeWrong($query)
    {
        return $query->where('is_correct', false)->whereNotNull('answer');
    }

    public function scopeSkipped($query)
    {
        return $query->whereNull('answer')->whereNull('answer_text');
    }

    /**
     * Accessors
     */
    public function getStatusAttribute(): string
    {
        if (!$this->is_graded) {
            return 'pending';
        }
        
        if ($this->is_correct) {
            return 'correct';
        }
        
        if ($this->is_partially_correct) {
            return 'partial';
        }
        
        return 'wrong';
    }

    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            'correct' => 'صحيحة',
            'partial' => 'صحيحة جزئياً',
            'wrong' => 'خاطئة',
            'pending' => 'قيد التصحيح',
            default => 'غير معروف',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'correct' => 'success',
            'partial' => 'warning',
            'wrong' => 'danger',
            'pending' => 'secondary',
            default => 'dark',
        };
    }

    public function getFormattedTimeSpentAttribute(): string
    {
        $seconds = $this->time_spent;
        
        $minutes = intdiv($seconds, 60);
        $secs = $seconds % 60;

        if ($minutes > 0) {
            return "{$minutes} د {$secs} ث";
        }
        
        return "{$secs} ثانية";
    }

    public function getIsAnsweredAttribute(): bool
    {
        return $this->answer !== null || $this->answer_text !== null || 
               $this->selected_options !== null || $this->numeric_answer !== null;
    }

    /**
     * Methods
     */
    public function autoGrade(): void
    {
        $question = $this->question;
        
        // التحقق من إمكانية استخدام AI grading للأسئلة المقالية
        if ($question->type === 'essay') {
            $aiGradingEnabled = \App\Models\SystemSetting::get('ai_essay_grading_enabled', false);
            $autoGradeEnabled = \App\Models\SystemSetting::get('ai_essay_auto_grade', false);
            
            if ($aiGradingEnabled && $autoGradeEnabled && !empty($this->answer_text)) {
                try {
                    $this->aiGrade();
                    return;
                } catch (\Exception $e) {
                    \Log::error('AI grading failed, falling back to manual: ' . $e->getMessage());
                    // في حالة فشل AI grading، ننتقل للتصحيح اليدوي
                }
            }
        }
        
        // الأسئلة التي تحتاج تصحيح يدوي
        if ($question->needs_manual_grading || $question->type === 'essay') {
            $this->needs_manual_grading = true;
            $this->is_graded = false;
            $this->save();
            return;
        }

        // Check if answer exists
        $hasAnswer = false;
        if ($this->selected_options && (is_array($this->selected_options) ? count($this->selected_options) > 0 : !empty($this->selected_options))) {
            $hasAnswer = true;
        } elseif ($this->answer_text && trim($this->answer_text) !== '') {
            $hasAnswer = true;
        } elseif ($this->numeric_answer !== null) {
            $hasAnswer = true;
        } elseif ($this->matching_pairs && (is_array($this->matching_pairs) ? count($this->matching_pairs) > 0 : !empty($this->matching_pairs))) {
            $hasAnswer = true;
        } elseif ($this->ordering && (is_array($this->ordering) ? count($this->ordering) > 0 : !empty($this->ordering))) {
            $hasAnswer = true;
        } elseif ($this->fill_blanks_answers && (is_array($this->fill_blanks_answers) ? count($this->fill_blanks_answers) > 0 : !empty($this->fill_blanks_answers))) {
            $hasAnswer = true;
        } elseif ($this->drag_drop_assignments && (is_array($this->drag_drop_assignments) ? count($this->drag_drop_assignments) > 0 : !empty($this->drag_drop_assignments))) {
            $hasAnswer = true;
        }
        
        if (!$hasAnswer) {
            // No answer provided, mark as incorrect
            $this->is_correct = false;
            $this->points_earned = 0;
            $this->is_graded = true;
            $this->graded_at = now();
            $this->save();
            return;
        }

        $isCorrect = false;
        $pointsEarned = 0;

        switch ($question->type) {
            case 'single_choice':
                $isCorrect = $this->gradeSingleChoice();
                break;
                
            case 'multiple_choice':
                $result = $this->gradeMultipleChoice();
                $isCorrect = $result['is_correct'];
                $pointsEarned = $result['points'];
                break;
                
            case 'true_false':
                $isCorrect = $this->gradeTrueFalse();
                break;
                
            case 'matching':
                $result = $this->gradeMatching();
                $isCorrect = $result['is_correct'];
                $pointsEarned = $result['points'];
                break;
                
            case 'ordering':
                $result = $this->gradeOrdering();
                $isCorrect = $result['is_correct'];
                $pointsEarned = $result['points'];
                break;
                
            case 'numerical':
                $isCorrect = $this->gradeNumerical();
                break;
                
            case 'fill_blanks':
            case 'fill_blank':
                $result = $this->gradeFillBlanks();
                $isCorrect = $result['is_correct'];
                $pointsEarned = $result['points'];
                break;

            case 'drag_drop':
                $result = $this->gradeDragDrop();
                $isCorrect = $result['is_correct'];
                $pointsEarned = $result['points'];
                break;
        }

        // إذا لم يتم تحديد درجة جزئية
        if ($pointsEarned === 0 && $isCorrect) {
            $pointsEarned = $this->max_points;
        }

        $this->is_correct = $isCorrect;
        $this->is_partially_correct = !$isCorrect && $pointsEarned > 0;
        $this->points_earned = $pointsEarned;
        $this->is_graded = true;
        $this->graded_at = now();
        $this->save();
    }

    /**
     * حساب نقاط التصحيح الجزئي دون قسمة على صفر.
     */
    protected function pointsForCorrectParts(int $correctCount, int $totalParts): float
    {
        if ($totalParts <= 0) {
            return 0.0;
        }

        $maxPoints = (float) ($this->max_points ?? 0);
        if ($maxPoints <= 0) {
            return 0.0;
        }

        return ($correctCount / $totalParts) * $maxPoints;
    }

    protected function gradeSingleChoice(): bool
    {
        $correctOption = $this->question->correctOptions()->first();
        
        if (!$correctOption || !$this->selected_options) {
            \Log::debug('gradeSingleChoice: No correct option or selected options', [
                'correctOption' => $correctOption?->id,
                'selected_options' => $this->selected_options,
            ]);
            return false;
        }

        $selectedIds = is_array($this->selected_options) ? $this->selected_options : [$this->selected_options];
        
        // تحويل جميع القيم إلى integers للمقارنة الصحيحة
        $selectedIds = array_map('intval', $selectedIds);
        $correctId = (int) $correctOption->id;
        
        \Log::debug('gradeSingleChoice: Comparing', [
            'correctId' => $correctId,
            'selectedIds' => $selectedIds,
            'result' => in_array($correctId, $selectedIds, true),
        ]);
        
        return in_array($correctId, $selectedIds, true);
    }

    protected function gradeMultipleChoice(): array
    {
        $correctOptions = $this->question->relationLoaded('correctOptions')
            ? $this->question->correctOptions->pluck('id')->all()
            : $this->question->correctOptions()->pluck('id')->all();

        $selectedIds = $this->selected_options ?? [];
        
        if (empty($selectedIds)) {
            return ['is_correct' => false, 'points' => 0];
        }

        // تحويل جميع القيم إلى integers للمقارنة الصحيحة
        $selectedIds = array_map('intval', $selectedIds);
        $correctOptions = array_map('intval', $correctOptions);

        $correctCount = count(array_intersect($selectedIds, $correctOptions));
        $wrongCount = count(array_diff($selectedIds, $correctOptions));
        $totalCorrect = count($correctOptions);

        if ($totalCorrect === 0) {
            return ['is_correct' => false, 'points' => 0];
        }

        $pointsPerCorrect = (float) $this->max_points / $totalCorrect;
        $points = ($correctCount * $pointsPerCorrect) - ($wrongCount * $pointsPerCorrect);
        $points = max(0, $points);

        $isCorrect = $correctCount === $totalCorrect && $wrongCount === 0;

        return ['is_correct' => $isCorrect, 'points' => $points];
    }

    protected function gradeTrueFalse(): bool
    {
        return $this->gradeSingleChoice();
    }

    protected function gradeMatching(): array
    {
        $options = $this->question->options;
        $pairs = $this->matching_pairs ?? [];
        
        if (empty($pairs)) {
            return ['is_correct' => false, 'points' => 0];
        }

        $correctPairs = 0;
        $totalPairs = $options->count();

        if ($totalPairs === 0) {
            return ['is_correct' => false, 'points' => 0];
        }

        foreach ($options as $option) {
            $submitted = $pairs[$option->id] ?? $pairs[(string) $option->id] ?? null;
            if ($submitted !== null && $submitted == $option->match_target) {
                $correctPairs++;
            }
        }

        $points = $this->pointsForCorrectParts($correctPairs, $totalPairs);
        $isCorrect = $correctPairs === $totalPairs;

        return ['is_correct' => $isCorrect, 'points' => $points];
    }

    protected function gradeOrdering(): array
    {
        $options = $this->question->relationLoaded('options')
            ? $this->question->options->sortBy('correct_order')->values()
            : $this->question->options()->orderBy('correct_order')->get();

        $ordering = $this->ordering ?? [];
        
        if (empty($ordering)) {
            return ['is_correct' => false, 'points' => 0];
        }

        $correctPositions = 0;
        $totalPositions = $options->count();

        if ($totalPositions === 0) {
            return ['is_correct' => false, 'points' => 0];
        }

        foreach ($options as $index => $option) {
            if (isset($ordering[$index]) && $ordering[$index] == $option->id) {
                $correctPositions++;
            }
        }

        $points = $this->pointsForCorrectParts($correctPositions, $totalPositions);
        $isCorrect = $correctPositions === $totalPositions;

        return ['is_correct' => $isCorrect, 'points' => $points];
    }

    protected function gradeNumerical(): bool
    {
        $correctOption = $this->question->relationLoaded('correctOptions')
            ? $this->question->correctOptions->first()
            : $this->question->correctOptions()->first();

        if (! $correctOption || $this->numeric_answer === null || $this->numeric_answer === '') {
            return false;
        }

        $correctValue = (float) $correctOption->content;
        $tolerance = (float) ($this->question->tolerance ?? 0);
        $userAnswer = (float) $this->numeric_answer;

        return abs($userAnswer - $correctValue) <= $tolerance;
    }

    /**
     * تصحيح السحب والإفلات — كل عنصر يُقارن بمنطقته عبر match_target (اسم المنطقة).
     * صيغة الإجابة: { option_id: zone_label }
     */
    protected function gradeDragDrop(): array
    {
        $options = $this->question->relationLoaded('options')
            ? $this->question->options
            : $this->question->options()->get();
        $assignments = $this->drag_drop_assignments ?? [];

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

        $points = $this->pointsForCorrectParts($correctCount, $totalItems);
        $isCorrect = $correctCount === $totalItems;

        return ['is_correct' => $isCorrect, 'points' => $points];
    }

    protected function gradeFillBlanks(): array
    {
        $blankAnswers = $this->question->blank_answers ?? [];
        $userAnswers = $this->fill_blanks_answers ?? [];

        if (empty($userAnswers)) {
            return ['is_correct' => false, 'points' => 0];
        }

        $totalBlanks = count($blankAnswers);

        if ($totalBlanks === 0) {
            return ['is_correct' => false, 'points' => 0];
        }

        $correctBlanks = 0;

        foreach ($blankAnswers as $index => $correctAnswer) {
            $userAnswer = $userAnswers[$index] ?? '';

            if ($this->question->case_sensitive) {
                if ($userAnswer === $correctAnswer) {
                    $correctBlanks++;
                }
            } else {
                if (strtolower(trim((string) $userAnswer)) === strtolower(trim((string) $correctAnswer))) {
                    $correctBlanks++;
                }
            }
        }

        $points = $this->pointsForCorrectParts($correctBlanks, $totalBlanks);
        $isCorrect = $correctBlanks === $totalBlanks;

        return ['is_correct' => $isCorrect, 'points' => $points];
    }

    public function manualGrade(float $points, ?string $feedback = null, ?int $graderId = null): void
    {
        $this->points_earned = min($points, $this->max_points);
        $this->is_correct = $this->points_earned >= $this->max_points;
        $this->is_partially_correct = !$this->is_correct && $this->points_earned > 0;
        $this->feedback = $feedback;
        $this->is_graded = true;
        $this->graded_by = $graderId ?? auth()->id();
        $this->graded_at = now();
        $this->save();

        // إعادة حساب درجة المحاولة
        $this->attempt->calculateScore();
    }
}
