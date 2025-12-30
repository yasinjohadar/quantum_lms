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
                $result = $this->gradeFillBlanks();
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

    protected function gradeSingleChoice(): bool
    {
        $correctOption = $this->question->correctOptions()->first();
        
        if (!$correctOption || !$this->selected_options) {
            return false;
        }

        $selectedIds = is_array($this->selected_options) ? $this->selected_options : [$this->selected_options];
        
        return in_array($correctOption->id, $selectedIds);
    }

    protected function gradeMultipleChoice(): array
    {
        $correctOptions = $this->question->correctOptions()->pluck('id')->toArray();
        $selectedIds = $this->selected_options ?? [];
        
        if (empty($selectedIds)) {
            return ['is_correct' => false, 'points' => 0];
        }

        $correctCount = count(array_intersect($selectedIds, $correctOptions));
        $wrongCount = count(array_diff($selectedIds, $correctOptions));
        $totalCorrect = count($correctOptions);

        // التصحيح الجزئي
        $pointsPerCorrect = $this->max_points / $totalCorrect;
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

        foreach ($options as $option) {
            if (isset($pairs[$option->id]) && $pairs[$option->id] == $option->match_target) {
                $correctPairs++;
            }
        }

        $pointsPerPair = $this->max_points / $totalPairs;
        $points = $correctPairs * $pointsPerPair;
        $isCorrect = $correctPairs === $totalPairs;

        return ['is_correct' => $isCorrect, 'points' => $points];
    }

    protected function gradeOrdering(): array
    {
        $options = $this->question->options()->orderBy('correct_order')->get();
        $ordering = $this->ordering ?? [];
        
        if (empty($ordering)) {
            return ['is_correct' => false, 'points' => 0];
        }

        $correctPositions = 0;
        $totalPositions = $options->count();

        foreach ($options as $index => $option) {
            if (isset($ordering[$index]) && $ordering[$index] == $option->id) {
                $correctPositions++;
            }
        }

        $pointsPerPosition = $this->max_points / $totalPositions;
        $points = $correctPositions * $pointsPerPosition;
        $isCorrect = $correctPositions === $totalPositions;

        return ['is_correct' => $isCorrect, 'points' => $points];
    }

    protected function gradeNumerical(): bool
    {
        $correctOption = $this->question->correctOptions()->first();
        
        if (!$correctOption || $this->numeric_answer === null) {
            return false;
        }

        $correctValue = (float) $correctOption->content;
        $tolerance = $this->question->tolerance ?? 0;
        $userAnswer = (float) $this->numeric_answer;

        return abs($userAnswer - $correctValue) <= $tolerance;
    }

    protected function gradeFillBlanks(): array
    {
        $blankAnswers = $this->question->blank_answers ?? [];
        $userAnswers = $this->answer ?? [];
        
        if (empty($userAnswers)) {
            return ['is_correct' => false, 'points' => 0];
        }

        $correctBlanks = 0;
        $totalBlanks = count($blankAnswers);

        foreach ($blankAnswers as $index => $correctAnswer) {
            $userAnswer = $userAnswers[$index] ?? '';
            
            if ($this->question->case_sensitive) {
                if ($userAnswer === $correctAnswer) {
                    $correctBlanks++;
                }
            } else {
                if (strtolower(trim($userAnswer)) === strtolower(trim($correctAnswer))) {
                    $correctBlanks++;
                }
            }
        }

        $pointsPerBlank = $this->max_points / $totalBlanks;
        $points = $correctBlanks * $pointsPerBlank;
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
