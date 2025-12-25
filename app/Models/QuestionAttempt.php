<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class QuestionAttempt extends Model
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
    ];

    protected $fillable = [
        'user_id',
        'question_id',
        'lesson_id',
        'attempt_number',
        'started_at',
        'finished_at',
        'time_spent',
        'last_activity_at',
        'time_limit',
        'status',
        'score',
        'max_score',
        'is_correct',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'attempt_number' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'time_spent' => 'integer',
        'time_limit' => 'integer',
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'is_correct' => 'boolean',
    ];

    /**
     * العلاقات
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function answer(): HasMany
    {
        return $this->hasMany(QuestionAnswer::class, 'attempt_id');
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

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForQuestion($query, $questionId)
    {
        return $query->where('question_id', $questionId);
    }

    public function scopeForLesson($query, $lessonId)
    {
        return $query->where('lesson_id', $lessonId);
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
        if (!$this->is_in_progress || !$this->time_limit) {
            return null;
        }

        $endTime = $this->started_at->copy()->addSeconds($this->time_limit);
        $remaining = now()->diffInSeconds($endTime, false);

        return max(0, $remaining);
    }

    public function getFormattedRemainingTimeAttribute(): ?string
    {
        $remaining = $this->remaining_time;
        
        if ($remaining === null) {
            return null;
        }

        $hours = intdiv($remaining, 3600);
        $minutes = intdiv($remaining % 3600, 60);
        $seconds = $remaining % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Methods
     */
    public function finish(): void
    {
        $this->finished_at = now();
        $this->time_spent = $this->started_at->diffInSeconds($this->finished_at);
        $this->status = 'completed';
        $this->save();
        $this->calculateScore();
    }

    public function timeout(): void
    {
        $this->finished_at = now();
        $this->time_spent = $this->time_limit ?? $this->started_at->diffInSeconds(now());
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

    public function calculateScore(): void
    {
        $answer = $this->answer()->first();
        
        if (!$answer || !$answer->is_graded) {
            return;
        }

        $this->score = $answer->points_earned;
        $this->max_score = $answer->max_points;
        $this->is_correct = $answer->is_correct;
        $this->save();
    }
}