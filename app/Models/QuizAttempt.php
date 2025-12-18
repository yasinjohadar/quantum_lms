<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class QuizAttempt extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * حالات المحاولة
     */
    public const STATUSES = [
        'in_progress' => 'جاري',
        'completed' => 'مكتمل',
        'abandoned' => 'متروك',
        'timed_out' => 'انتهى الوقت',
        'under_review' => 'قيد المراجعة',
    ];

    protected $fillable = [
        'quiz_id',
        'user_id',
        'attempt_number',
        'started_at',
        'finished_at',
        'time_spent',
        'last_activity_at',
        'score',
        'max_score',
        'percentage',
        'passed',
        'status',
        'questions_answered',
        'questions_correct',
        'questions_wrong',
        'questions_skipped',
        'question_order',
        'ip_address',
        'user_agent',
        'grader_notes',
        'graded_by',
        'graded_at',
    ];

    protected $casts = [
        'attempt_number' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'time_spent' => 'integer',
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'percentage' => 'decimal:2',
        'passed' => 'boolean',
        'questions_answered' => 'integer',
        'questions_correct' => 'integer',
        'questions_wrong' => 'integer',
        'questions_skipped' => 'integer',
        'question_order' => 'array',
        'graded_at' => 'datetime',
    ];

    /**
     * العلاقات
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class, 'attempt_id');
    }

    /**
     * Scopes
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ['completed', 'timed_out']);
    }

    public function scopeNeedsGrading($query)
    {
        return $query->where('status', 'under_review');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePassed($query)
    {
        return $query->where('passed', true);
    }

    public function scopeFailed($query)
    {
        return $query->where('passed', false)->whereIn('status', ['completed', 'timed_out']);
    }

    /**
     * Accessors
     */
    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'in_progress' => 'warning',
            'completed' => 'success',
            'abandoned' => 'secondary',
            'timed_out' => 'danger',
            'under_review' => 'info',
            default => 'dark',
        };
    }

    public function getFormattedTimeSpentAttribute(): string
    {
        $seconds = $this->time_spent;
        
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
        }
        
        return sprintf('%d:%02d', $minutes, $secs);
    }

    public function getIsInProgressAttribute(): bool
    {
        return $this->status === 'in_progress';
    }

    public function getIsCompletedAttribute(): bool
    {
        return in_array($this->status, ['completed', 'timed_out']);
    }

    public function getRemainingTimeAttribute(): ?int
    {
        if (!$this->is_in_progress || !$this->quiz->duration_minutes) {
            return null;
        }

        $endTime = $this->started_at->addMinutes($this->quiz->duration_minutes);
        $remaining = now()->diffInSeconds($endTime, false);

        return max(0, $remaining);
    }

    public function getFormattedRemainingTimeAttribute(): ?string
    {
        $remaining = $this->remaining_time;
        
        if ($remaining === null) {
            return null;
        }

        $minutes = intdiv($remaining, 60);
        $seconds = $remaining % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function getPassStatusAttribute(): string
    {
        if (!$this->is_completed) {
            return 'pending';
        }
        
        return $this->passed ? 'passed' : 'failed';
    }

    public function getPassStatusNameAttribute(): string
    {
        return match($this->pass_status) {
            'passed' => 'ناجح',
            'failed' => 'راسب',
            'pending' => 'قيد الانتظار',
            default => 'غير معروف',
        };
    }

    public function getPassStatusColorAttribute(): string
    {
        return match($this->pass_status) {
            'passed' => 'success',
            'failed' => 'danger',
            'pending' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Methods
     */
    public function calculateScore(): void
    {
        $answers = $this->answers()->with('question')->get();
        
        $score = 0;
        $maxScore = 0;
        $correct = 0;
        $wrong = 0;
        $skipped = 0;
        $answered = 0;

        foreach ($answers as $answer) {
            $maxScore += $answer->max_points;
            
            if ($answer->is_graded) {
                $score += $answer->points_earned;
                $answered++;
                
                if ($answer->is_correct) {
                    $correct++;
                } elseif ($answer->answer !== null) {
                    $wrong++;
                } else {
                    $skipped++;
                }
            } elseif ($answer->answer === null) {
                $skipped++;
            }
        }

        $this->score = $score;
        $this->max_score = $maxScore;
        $this->percentage = $maxScore > 0 ? ($score / $maxScore) * 100 : 0;
        $this->passed = $this->percentage >= $this->quiz->pass_percentage;
        $this->questions_answered = $answered;
        $this->questions_correct = $correct;
        $this->questions_wrong = $wrong;
        $this->questions_skipped = $skipped;
        $this->save();
    }

    public function finish(): void
    {
        $this->finished_at = now();
        $this->time_spent = $this->started_at->diffInSeconds($this->finished_at);
        $this->status = 'completed';
        
        // التحقق من وجود أسئلة تحتاج تصحيح يدوي
        $needsManualGrading = $this->answers()
            ->where('needs_manual_grading', true)
            ->where('is_graded', false)
            ->exists();

        if ($needsManualGrading) {
            $this->status = 'under_review';
        }

        $this->save();
        $this->calculateScore();
    }

    public function timeout(): void
    {
        $this->finished_at = now();
        $this->time_spent = $this->quiz->duration_minutes * 60;
        $this->status = 'timed_out';
        $this->save();
        $this->calculateScore();
    }

    public function abandon(): void
    {
        $this->finished_at = now();
        $this->time_spent = $this->started_at->diffInSeconds($this->finished_at);
        $this->status = 'abandoned';
        $this->save();
    }

    public function updateActivity(): void
    {
        $this->last_activity_at = now();
        $this->time_spent = $this->started_at->diffInSeconds(now());
        $this->save();
    }
}

