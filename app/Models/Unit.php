<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'units';

    protected $fillable = [
        'section_id',
        'sync_group_id',
        'is_sync_canonical',
        'cloned_from_unit_id',
        'parent_id',
        'title',
        'description',
        'order',
        'is_active',
    ];

    protected $casts = [
        'section_id' => 'integer',
        'parent_id' => 'integer',
        'order' => 'integer',
        'is_active' => 'boolean',
        'is_sync_canonical' => 'boolean',
        'cloned_from_unit_id' => 'integer',
    ];

    /**
     * العلاقة مع القسم.
     */
    public function section()
    {
        return $this->belongsTo(SubjectSection::class, 'section_id');
    }

    public function clonedFromUnit()
    {
        return $this->belongsTo(Unit::class, 'cloned_from_unit_id');
    }

    public function isSyncMirror(): bool
    {
        return $this->cloned_from_unit_id !== null;
    }

    public function linkedSectionIdsViaSync(): array
    {
        return static::query()
            ->where('cloned_from_unit_id', $this->id)
            ->whereNull('parent_id')
            ->pluck('section_id')
            ->unique()
            ->values()
            ->all();
    }

    public function linkedSectionsViaSync()
    {
        $sectionIds = $this->linkedSectionIdsViaSync();

        if ($sectionIds === []) {
            return collect();
        }

        return SubjectSection::with('subject.schoolClass.stage')->whereIn('id', $sectionIds)->get();
    }

    /**
     * جمع الوحدة وجميع أحفادها.
     *
     * @return \Illuminate\Support\Collection<int, Unit>
     */
    public function collectSubtree(): \Illuminate\Support\Collection
    {
        $this->loadMissing('children');

        $units = collect([$this]);
        foreach ($this->children as $child) {
            $units = $units->merge($child->collectSubtree());
        }

        return $units;
    }

    /**
     * أقسام إضافية تظهر فيها هذه الوحدة (legacy pivot section_unit).
     */
    public function mirroredInSections(): BelongsToMany
    {
        return $this->belongsToMany(SubjectSection::class, 'section_unit', 'unit_id', 'subject_section_id')
            ->withPivot('order')
            ->withTimestamps()
            ->orderByPivot('order');
    }

    /**
     * العلاقة مع الوحدة الأب.
     */
    public function parent()
    {
        return $this->belongsTo(Unit::class, 'parent_id');
    }

    /**
     * العلاقة مع الوحدات الأبناء.
     */
    public function children()
    {
        return $this->hasMany(Unit::class, 'parent_id')->orderBy('order')->orderBy('title');
    }

    /**
     * جمع معرفات كل الأحفاد فقط (أبناء + أحفادهم، بدون الوحدة الحالية) لمنع الحلقات عند تغيير الأب.
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
     * العلاقة مع الدروس التي أنشئت أصلاً في هذه الوحدة (unit_id = هذا).
     */
    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    /**
     * الدروس المرتبطة بهذه الوحدة عبر lesson_units فقط (دروس من وحدات أخرى تظهر هنا).
     */
    public function linkedLessons(): BelongsToMany
    {
        return $this->belongsToMany(Lesson::class, 'lesson_units', 'unit_id', 'lesson_id')->withTimestamps();
    }

    /**
     * جميع الدروس المعروضة في هذه الوحدة: الدروس الأصلية + الدروس المرتبطة، بدون تكرار، مرتبة.
     */
    public function allLessons()
    {
        $primary = $this->lessons()->get();
        $primaryIds = $primary->pluck('id')->toArray();
        if ($this->relationLoaded('linkedLessons')) {
            $linked = $this->linkedLessons->whereNotIn('id', $primaryIds)->sortBy('order')->values();
        } else {
            $linked = $this->linkedLessons()->whereNotIn('lessons.id', $primaryIds)->orderBy('lessons.order')->get();
        }
        return $primary->concat($linked)->sortBy('order')->values();
    }

    /**
     * معرّفات الدروس المعروضة في هذه الوحدة بدون تكرار.
     *
     * @return \Illuminate\Support\Collection<int, int>
     */
    public function collectLessonIdsForDisplay(): \Illuminate\Support\Collection
    {
        if ($this->relationLoaded('lessons')) {
            $primary = $this->lessons;
        } else {
            $primary = $this->lessons()->get();
        }

        $primaryIds = $primary->pluck('id');

        if ($this->relationLoaded('linkedLessons')) {
            $linkedIds = $this->linkedLessons->whereNotIn('id', $primaryIds)->pluck('id');
        } else {
            $linkedIds = $this->linkedLessons()
                ->whereNotIn('lessons.id', $primaryIds->all())
                ->pluck('lessons.id');
        }

        return $primaryIds->merge($linkedIds)->unique()->filter()->values();
    }

    /**
     * مجموع مدة دروس الوحدة المعروضة بالثواني.
     */
    public function totalLessonsDurationSecondsForDisplay(): int
    {
        if ($this->relationLoaded('lessons')) {
            $primary = $this->lessons;
            $primaryIds = $primary->pluck('id');
            $lessons = $primary;

            if ($this->relationLoaded('linkedLessons')) {
                $lessons = $lessons->concat(
                    $this->linkedLessons->whereNotIn('id', $primaryIds)->values()
                );
            }

            return \App\Support\LessonDurationFormatter::sumSecondsFromLessons($lessons);
        }

        return \App\Support\LessonDurationFormatter::sumDurationForLessonIds(
            $this->collectLessonIdsForDisplay()
        );
    }

    /**
     * العلاقة مع جميع الاختبارات المرتبطة بهذه الوحدة (قد تكون عامة أو تابعة لدروس).
     */
    public function quizzes()
    {
        return $this->hasMany(Quiz::class)->orderBy('order');
    }

    /**
     * اختبارات عامة للوحدة (لا تتبع درساً محدداً).
     */
    public function unitQuizzes()
    {
        return $this->hasMany(Quiz::class)
            ->whereNull('lesson_id')
            ->orderBy('order');
    }

    /**
     * كل الاختبارات التفاعلية المرتبطة بالوحدة (قد تشمل اختبارات دروس).
     */
    public function learningExperiences()
    {
        return $this->hasMany(\App\InteractiveLearning\Models\LearningExperience::class)->latest();
    }

    /**
     * اختبارات تفاعلية عامة للوحدة (بدون درس).
     */
    public function unitLearningExperiences()
    {
        return $this->hasMany(\App\InteractiveLearning\Models\LearningExperience::class)
            ->whereNull('lesson_id')
            ->latest();
    }

    /**
     * اختبارات الوحدة التفاعلية المعروضة في صفحة المادة.
     */
    public function allUnitLearningExperiences()
    {
        if ($this->relationLoaded('learningExperiences')) {
            return $this->learningExperiences
                ->filter(fn ($experience) => is_null($experience->lesson_id))
                ->values();
        }

        return $this->unitLearningExperiences()->get();
    }

    /**
     * اختبارات تابعة لدروس هذه الوحدة (لكل درس اختبار/اختبارات خاصة).
     * مفيدة إذا احتجنا إحصائيات على مستوى الوحدة لكل اختبارات الدروس.
     */
    public function lessonQuizzes()
    {
        return $this->hasMany(Quiz::class)
            ->whereNotNull('lesson_id')
            ->orderBy('order');
    }

    /**
     * الاختبارات المرتبطة بهذه الوحدة عبر quiz_units (اختبارات من وحدات أخرى تظهر هنا).
     */
    public function linkedQuizzes(): BelongsToMany
    {
        return $this->belongsToMany(Quiz::class, 'quiz_units', 'unit_id', 'quiz_id')->withTimestamps();
    }

    /**
     * جميع اختبارات الوحدة المعروضة: الأصلية (unit_id = هذه الوحدة، بدون درس) + المرتبطة.
     */
    public function allUnitQuizzes()
    {
        // الاختبارات الأصلية للوحدة
        if ($this->relationLoaded('quizzes')) {
            $primary = $this->quizzes
                ->filter(fn ($q) => is_null($q->lesson_id))
                ->sortBy('order')
                ->values();
        } else {
            $primary = $this->quizzes()
                ->whereNull('lesson_id')
                ->with('linkedUnits.section.subject.schoolClass.stage')
                ->orderBy('order')
                ->get();
        }

        // الاختبارات المرتبطة بالوحدة عبر quiz_units
        if ($this->relationLoaded('linkedQuizzes')) {
            $linked = $this->linkedQuizzes->sortBy('order')->values();
        } else {
            $linked = $this->linkedQuizzes()
                ->with('linkedUnits.section.subject.schoolClass.stage')
                ->orderBy('order')
                ->get();
        }

        return $primary->merge($linked)->unique('id')->sortBy('order')->values();
    }

    /**
     * العلاقة مع الأسئلة
     */
    public function questions()
    {
        return $this->belongsToMany(Question::class, 'question_units')->withTimestamps();
    }

    /**
     * إجمالي مدة الدروس في هذه الوحدة بالثواني (دروس أصلية + مرتبطة، بدون تكرار).
     * الدروس بدون مدة (null) تُحسب كـ 0.
     */
    public function getTotalDurationSeconds(): int
    {
        $lessons = $this->allLessons();
        return (int) $lessons->sum(fn ($lesson) => (int) ($lesson->duration ?? 0));
    }

    /**
     * Scope للوحدات النشطة.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope للترتيب.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
