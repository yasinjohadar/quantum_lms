<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicWeek extends Model
{
    use HasFactory;

    protected $table = 'academic_weeks';

    protected $fillable = [
        'academic_year_id',
        'week_number',
        'title',
        'start_date',
        'end_date',
        'required_lessons_target',
        'meta',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'required_lessons_target' => 'integer',
        'meta' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * السنة الدراسية
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /**
     * أهداف المعلمين لهذا الأسبوع
     */
    public function teacherWeekTargets(): HasMany
    {
        return $this->hasMany(TeacherWeekTarget::class, 'academic_week_id');
    }
}
