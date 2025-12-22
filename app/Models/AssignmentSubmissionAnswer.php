<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentSubmissionAnswer extends Model
{
    use HasFactory;

    protected $table = 'assignment_submission_answers';

    protected $fillable = [
        'submission_id',
        'question_id',
        'answer',
        'is_correct',
        'points_earned',
        'auto_graded_at',
    ];

    protected $casts = [
        'submission_id' => 'integer',
        'question_id' => 'integer',
        'answer' => 'array',
        'is_correct' => 'boolean',
        'points_earned' => 'decimal:2',
        'auto_graded_at' => 'datetime',
    ];

    /**
     * العلاقة مع الإرسال
     */
    public function submission()
    {
        return $this->belongsTo(AssignmentSubmission::class, 'submission_id');
    }

    /**
     * العلاقة مع السؤال
     */
    public function question()
    {
        return $this->belongsTo(AssignmentQuestion::class, 'question_id');
    }

    /**
     * تصحيح تلقائي للإجابة
     */
    public function autoGrade(): void
    {
        if (!$this->question) {
            return;
        }

        $studentAnswer = $this->answer;
        $isCorrect = $this->question->checkAnswer($studentAnswer);
        $pointsEarned = $this->question->calculatePoints($studentAnswer);

        $this->is_correct = $isCorrect;
        $this->points_earned = $pointsEarned;
        $this->auto_graded_at = now();
        $this->save();
    }
}
