<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIQuestionSolution extends Model
{
    use HasFactory;

    protected $table = 'ai_question_solutions';

    protected $fillable = [
        'question_id',
        'ai_model_id',
        'solution',
        'explanation',
        'confidence_score',
        'tokens_used',
        'cost',
        'is_verified',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'confidence_score' => 'decimal:2',
        'tokens_used' => 'integer',
        'cost' => 'decimal:6',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    /**
     * العلاقة مع السؤال
     */
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    /**
     * العلاقة مع الموديل
     */
    public function model()
    {
        return $this->belongsTo(AIModel::class, 'ai_model_id');
    }

    /**
     * العلاقة مع المتحقق
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * نطاق الحلول الم verified
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * نطاق الحلول غير الم verified
     */
    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    /**
     * نطاق حلول سؤال معين
     */
    public function scopeForQuestion($query, int $questionId)
    {
        return $query->where('question_id', $questionId);
    }

    /**
     * التحقق من الحل
     */
    public function verify(User $verifier): bool
    {
        return $this->update([
            'is_verified' => true,
            'verified_by' => $verifier->id,
            'verified_at' => now(),
        ]);
    }

    /**
     * الحصول على الدقة
     */
    public function getAccuracy(): float
    {
        // سيتم حساب هذا بناءً على مقارنة الحل مع الإجابة الصحيحة
        return $this->confidence_score ?? 0;
    }
}
