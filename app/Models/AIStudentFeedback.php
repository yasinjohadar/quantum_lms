<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIStudentFeedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'quiz_attempt_id',
        'feedback_type',
        'feedback_text',
        'suggestions',
        'ai_model_id',
        'tokens_used',
        'cost',
    ];

    protected $casts = [
        'suggestions' => 'array',
        'tokens_used' => 'integer',
        'cost' => 'decimal:6',
    ];

    /**
     * أنواع الملاحظات
     */
    public const FEEDBACK_TYPES = [
        'performance' => 'ملاحظات الأداء',
        'general' => 'ملاحظات عامة',
        'improvement' => 'اقتراحات التحسين',
    ];

    /**
     * العلاقات
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function quizAttempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class);
    }

    public function aiModel(): BelongsTo
    {
        return $this->belongsTo(AIModel::class);
    }
}


