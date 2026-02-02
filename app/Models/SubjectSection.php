<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubjectSection extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'subject_sections';

    protected $fillable = [
        'subject_id',
        'parent_id',
        'title',
        'description',
        'order',
        'is_active',
    ];

    protected $casts = [
        'subject_id' => 'integer',
        'parent_id' => 'integer',
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
     * المواد الإضافية المرتبطة بهذا القسم عبر section_subjects (ظهور القسم في مواد أخرى).
     */
    public function linkedSubjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'section_subjects', 'section_id', 'subject_id')->withTimestamps();
    }

    /**
     * العلاقة مع القسم الأب.
     */
    public function parent()
    {
        return $this->belongsTo(SubjectSection::class, 'parent_id');
    }

    /**
     * العلاقة مع الأقسام الأبناء.
     */
    public function children()
    {
        return $this->hasMany(SubjectSection::class, 'parent_id')->orderBy('order')->orderBy('title');
    }

    /**
     * جمع معرفات كل الأحفاد فقط (أبناء + أحفادهم، بدون القسم الحالي) لمنع الحلقات عند تغيير الأب.
     */
    public function getDescendantIds(): \Illuminate\Support\Collection
    {
        $ids = collect();
        foreach ($this->children as $child) {
            $ids->push($child->id);
            $ids = $ids->merge($child->getDescendantIds());
        }
        return $ids;
    }

    /**
     * مسار القسم (للعرض في القوائم): الأجداد ثم العنوان.
     */
    public function getPathTitleAttribute(): string
    {
        $parts = [];
        $current = $this->parent;
        while ($current) {
            array_unshift($parts, $current->title);
            $current = $current->parent;
        }
        $parts[] = $this->title;
        return implode(' › ', $parts);
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

