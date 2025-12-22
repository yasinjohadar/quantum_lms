<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentGrade extends Model
{
    use HasFactory;

    protected $table = 'assignment_grades';

    protected $fillable = [
        'submission_id',
        'criteria',
        'manual_score',
        'comments',
        'graded_by',
        'graded_at',
    ];

    protected $casts = [
        'submission_id' => 'integer',
        'criteria' => 'array',
        'manual_score' => 'decimal:2',
        'graded_by' => 'integer',
        'graded_at' => 'datetime',
    ];

    /**
     * العلاقة مع الإرسال
     */
    public function submission()
    {
        return $this->belongsTo(AssignmentSubmission::class, 'submission_id');
    }

    /**
     * العلاقة مع المعلم الذي صحح الواجب
     */
    public function grader()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * الحصول على معايير التصحيح كمصفوفة
     */
    public function getCriteriaArray(): array
    {
        return $this->criteria ?? [];
    }
}
