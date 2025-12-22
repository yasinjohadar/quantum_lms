<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentQuestion extends Model
{
    use HasFactory;

    protected $table = 'assignment_questions';

    protected $fillable = [
        'assignment_id',
        'question_text',
        'question_type',
        'options',
        'correct_answer',
        'points',
        'order',
    ];

    protected $casts = [
        'assignment_id' => 'integer',
        'options' => 'array',
        'correct_answer' => 'array',
        'points' => 'decimal:2',
        'order' => 'integer',
    ];

    /**
     * أنواع الأسئلة المتاحة.
     */
    const QUESTION_TYPES = [
        'single_choice' => 'اختيار واحد',
        'multiple_choice' => 'اختيار متعدد',
        'true_false' => 'صح/خطأ',
        'short_answer' => 'إجابة قصيرة',
    ];

    /**
     * العلاقة مع الواجب
     */
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * العلاقة مع إجابات الطلاب
     */
    public function submissionAnswers()
    {
        return $this->hasMany(AssignmentSubmissionAnswer::class, 'question_id');
    }

    /**
     * التحقق من صحة الإجابة
     */
    public function checkAnswer($studentAnswer): bool
    {
        $correctAnswer = $this->correct_answer;

        if ($this->question_type === 'single_choice') {
            return $studentAnswer === $correctAnswer;
        } elseif ($this->question_type === 'multiple_choice') {
            if (!is_array($studentAnswer) || !is_array($correctAnswer)) {
                return false;
            }
            sort($studentAnswer);
            sort($correctAnswer);
            return $studentAnswer === $correctAnswer;
        } elseif ($this->question_type === 'true_false') {
            return $studentAnswer === $correctAnswer;
        } elseif ($this->question_type === 'short_answer') {
            // للإجابة القصيرة، مقارنة نصية (case-insensitive)
            return strtolower(trim($studentAnswer)) === strtolower(trim($correctAnswer));
        }

        return false;
    }

    /**
     * حساب الدرجة بناءً على الإجابة
     */
    public function calculatePoints($studentAnswer): float
    {
        if ($this->checkAnswer($studentAnswer)) {
            return (float) $this->points;
        }
        return 0.0;
    }
}
