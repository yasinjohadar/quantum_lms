<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use HasFactory;

    protected $table = 'academic_years';

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * الأسابيع الدراسية لهذه السنة
     */
    public function academicWeeks(): HasMany
    {
        return $this->hasMany(AcademicWeek::class, 'academic_year_id');
    }

    /**
     * نطاق: السنة النشطة الحالية فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
