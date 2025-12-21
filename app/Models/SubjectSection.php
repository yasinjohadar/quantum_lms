<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubjectSection extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'subject_sections';

    protected $fillable = [
        'subject_id',
        'title',
        'description',
        'order',
        'is_active',
    ];

    protected $casts = [
        'subject_id' => 'integer',
        'order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * العلاقة مع المادة.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * العلاقة مع الوحدات.
     */
    public function units()
    {
        return $this->hasMany(Unit::class, 'section_id')->orderBy('order');
    }

    /**
     * Accessors - إجمالي الدروس في القسم
     */
    public function getTotalLessonsAttribute(): int
    {
        return $this->units()
            ->with(['lessons' => function($query) {
                $query->where('is_active', true);
            }])
            ->get()
            ->sum(function($unit) {
                return $unit->lessons->count();
            });
    }

    /**
     * Accessors - إجمالي الاختبارات في القسم
     */
    public function getTotalQuizzesAttribute(): int
    {
        $unitIds = $this->units()->pluck('id')->toArray();
        
        return \App\Models\Quiz::whereIn('unit_id', $unitIds)
            ->where('is_active', true)
            ->where('is_published', true)
            ->count();
    }

    /**
     * Accessors - إجمالي الأسئلة في القسم
     */
    public function getTotalQuestionsAttribute(): int
    {
        $unitIds = $this->units()->pluck('id')->toArray();
        
        return \App\Models\Question::whereHas('units', function($query) use ($unitIds) {
                $query->whereIn('units.id', $unitIds);
            })
            ->where('is_active', true)
            ->count();
    }
}


