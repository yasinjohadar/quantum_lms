<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionAnswer extends Model
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
        'numeric_answer',
        'fill_blanks_answers',
        'is_correct',
        'is_partially_correct',
        'points_earned',
        'max_points',
        'feedback',
        'needs_manual_grading',
        'is_graded',
        'graded_by',
        'graded_at',
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
    ];

    /**
     * العلاقات
     */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuestionAttempt::class, 'attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
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

    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    public function scopeWrong($query)
    {
        return $query->where('is_correct', false)->whereNotNull('answer');
    }
}