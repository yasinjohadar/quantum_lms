<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherWeekTarget extends Model
{
    use HasFactory;

    protected $table = 'teacher_week_targets';

    protected $fillable = [
        'teacher_id',
        'academic_week_id',
        'required_lessons_target',
    ];

    protected $casts = [
        'required_lessons_target' => 'integer',
    ];

    /**
     * المعلم
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * الأسبوع الدراسي
     */
    public function academicWeek(): BelongsTo
    {
        return $this->belongsTo(AcademicWeek::class, 'academic_week_id');
    }
}
