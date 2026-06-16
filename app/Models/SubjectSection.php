<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class SubjectSection extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_LESSONS = 'lessons';
    public const TYPE_QUIZZES = 'quizzes';

    public const TYPE_LABELS = [
        self::TYPE_LESSONS => 'دروس',
        self::TYPE_QUIZZES => 'اختبارات',
    ];

    protected $table = 'subject_sections';

    protected $fillable = [
        'subject_id',
        'sync_group_id',
        'is_sync_canonical',
        'cloned_from_section_id',
        'parent_id',
        'title',
        'description',
        'type',
        'order',
        'is_active',
    ];

    protected $casts = [
        'subject_id' => 'integer',
        'parent_id' => 'integer',
        'order' => 'integer',
        'is_active' => 'boolean',
        'is_sync_canonical' => 'boolean',
        'cloned_from_section_id' => 'integer',
    ];

    /**
     * العلاقة مع المادة.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * المواد الإضافية المرتبطة بهذا القسم عبر section_subjects (legacy).
     */
    public function linkedSubjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'section_subjects', 'section_id', 'subject_id')->withTimestamps();
    }

    public function clonedFromSection()
    {
        return $this->belongsTo(SubjectSection::class, 'cloned_from_section_id');
    }

    public function syncMirrors()
    {
        if (! $this->sync_group_id) {
            return collect();
        }

        return static::query()
            ->where('sync_group_id', $this->sync_group_id)
            ->where('id', '!=', $this->id)
            ->get();
    }

    public function isSyncMirror(): bool
    {
        return $this->cloned_from_section_id !== null;
    }

    /**
     * المواد التي تحتوي نسخة مرتبطة من هذا القسم (anchor).
     */
    public function linkedSubjectsViaSync(): \Illuminate\Support\Collection
    {
        $subjectIds = static::query()
            ->where('cloned_from_section_id', $this->id)
            ->pluck('subject_id')
            ->unique()
            ->values();

        if ($subjectIds->isEmpty()) {
            return collect();
        }

        return Subject::with('schoolClass.stage')->whereIn('id', $subjectIds)->get();
    }

    public function linkedSubjectIdsViaSync(): array
    {
        return static::query()
            ->where('cloned_from_section_id', $this->id)
            ->pluck('subject_id')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * جمع القسم وجميع أحفاده.
     *
     * @return Collection<int, SubjectSection>
     */
    public function collectSubtree(): Collection
    {
        $sections = collect([$this]);
        foreach ($this->children()->with('children')->get() as $child) {
            $sections = $sections->merge($child->collectSubtree());
        }

        return $sections;
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
     * دروس القسم المباشرة (بدون وحدة).
     */
    public function directLessons()
    {
        return $this->hasMany(Lesson::class, 'section_id')
            ->whereNull('unit_id')
            ->orderBy('order');
    }

    /**
     * إجمالي الدروس المعروضة في القسم (مباشرة + داخل كل الوحدات المعروضة) بدون تكرار.
     *
     * @param  Collection<int, SubjectSection>|null  $sectionsPool  أقسام المادة (لحل وحدات مرآة من أقسام أخرى) دون استعلامات إضافية
     */
    public function countAllLessonsForDisplay(?Collection $sectionsPool = null): int
    {
        return $this->collectAllLessonIdsForDisplay($sectionsPool)->count();
    }

    /**
     * مجموع مدة الدروس المعروضة في القسم بالثواني.
     *
     * @param  Collection<int, SubjectSection>|null  $sectionsPool
     */
    public function totalLessonsDurationSecondsForDisplay(?Collection $sectionsPool = null): int
    {
        $ids = $this->collectAllLessonIdsForDisplay($sectionsPool);
        $pool = $this->lessonsPoolForDurationSum($sectionsPool);

        if ($pool !== null && $pool->count() >= $ids->count()) {
            return \App\Support\LessonDurationFormatter::sumSecondsFromLessons(
                $pool->whereIn('id', $ids)
            );
        }

        return \App\Support\LessonDurationFormatter::sumDurationForLessonIds($ids);
    }

    /**
     * معرّفات الدروس المعروضة في القسم بدون تكرار.
     *
     * @param  Collection<int, SubjectSection>|null  $sectionsPool
     * @return Collection<int, int>
     */
    public function collectAllLessonIdsForDisplay(?Collection $sectionsPool = null): Collection
    {
        $ids = collect();

        if ($this->relationLoaded('directLessons')) {
            $ids = $ids->merge($this->directLessons->pluck('id'));
        } else {
            $ids = $ids->merge($this->directLessons()->pluck('id'));
        }

        foreach ($this->rootUnitsForDisplay() as $rootUnit) {
            $unitsPool = $this->unitsPoolForRootUnit($rootUnit, $sectionsPool);

            foreach ($this->flattenUnitTree($rootUnit, $unitsPool) as $unit) {
                $ids = $ids->merge($this->lessonIdsFromUnit($unit));
            }
        }

        return $ids->unique()->filter()->values();
    }

    /**
     * @return Collection<int, Lesson>|null
     */
    protected function lessonsPoolForDurationSum(?Collection $sectionsPool): ?Collection
    {
        $pool = collect();

        if ($this->relationLoaded('directLessons')) {
            $pool = $pool->merge($this->directLessons);
        }

        foreach ($this->rootUnitsForDisplay() as $rootUnit) {
            $unitsPool = $this->unitsPoolForRootUnit($rootUnit, $sectionsPool);

            foreach ($this->flattenUnitTree($rootUnit, $unitsPool) as $unit) {
                if (! $unit->relationLoaded('lessons')) {
                    return null;
                }

                $pool = $pool->merge($unit->lessons);

                if ($unit->relationLoaded('linkedLessons')) {
                    $pool = $pool->merge($unit->linkedLessons);
                }
            }
        }

        return $pool->isNotEmpty() ? $pool->unique('id')->values() : null;
    }

    /**
     * @return Collection<int, Unit>
     */
    protected function flattenUnitTree(Unit $root, Collection $unitsPool): Collection
    {
        $result = collect([$root]);

        foreach ($unitsPool->where('parent_id', $root->id) as $child) {
            $result = $result->merge($this->flattenUnitTree($child, $unitsPool));
        }

        return $result;
    }

    /**
     * @return Collection<int, Unit>
     */
    protected function unitsPoolForRootUnit(Unit $unit, ?Collection $sectionsPool): Collection
    {
        if ((int) $unit->section_id === (int) $this->id) {
            if ($this->relationLoaded('units')) {
                return $this->units;
            }

            return $this->units()->get();
        }

        $homeSection = null;

        if ($sectionsPool) {
            $homeSection = $sectionsPool->firstWhere('id', $unit->section_id);
        }

        if (! $homeSection && $unit->relationLoaded('section')) {
            $homeSection = $unit->section;
        }

        if (! $homeSection) {
            $homeSection = static::query()
                ->with(['units.lessons', 'units.linkedLessons'])
                ->find($unit->section_id);
        }

        if ($homeSection && $homeSection->relationLoaded('units')) {
            return $homeSection->units;
        }

        return $homeSection
            ? $homeSection->units()->with(['lessons', 'linkedLessons'])->get()
            : collect();
    }

    /**
     * @return Collection<int, int|string>
     */
    protected function lessonIdsFromUnit(Unit $unit): Collection
    {
        if ($unit->relationLoaded('lessons')) {
            $primary = $unit->lessons;
        } else {
            $primary = $unit->lessons()->get();
        }

        $primaryIds = $primary->pluck('id');

        if ($unit->relationLoaded('linkedLessons')) {
            $linkedIds = $unit->linkedLessons->whereNotIn('id', $primaryIds)->pluck('id');
        } else {
            $linkedIds = $unit->linkedLessons()
                ->whereNotIn('lessons.id', $primaryIds->all())
                ->pluck('lessons.id');
        }

        return $primaryIds->merge($linkedIds);
    }

    /**
     * وحدات من أقسام أخرى تظهر في هذا القسم عبر section_unit.
     */
    public function mirroredUnits(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'section_unit', 'subject_section_id', 'unit_id')
            ->withPivot('order')
            ->withTimestamps()
            ->orderByPivot('order');
    }

    /**
     * جذور الوحدات للعرض في القسم: وحدات المنزل (parent_id فارغ) ثم الوحدات المرآة حسب pivot.order.
     *
     * @return Collection<int, Unit>
     */
    public function rootUnitsForDisplay(bool $onlyActive = false): Collection
    {
        if ($this->relationLoaded('units')) {
            $primary = $this->units
                ->when($onlyActive, fn ($c) => $c->where('is_active', true))
                ->whereNull('parent_id')
                ->sortBy('order')
                ->values();
        } else {
            $primary = $this->units()
                ->when($onlyActive, fn ($q) => $q->active())
                ->whereNull('parent_id')
                ->orderBy('order')
                ->orderBy('title')
                ->get();
        }

        if ($this->relationLoaded('mirroredUnits')) {
            $mirrored = $this->mirroredUnits
                ->when($onlyActive, fn ($c) => $c->where('is_active', true))
                ->sortBy(fn ($u) => (int) ($u->pivot->order ?? 0))
                ->values();
        } else {
            $mirrored = $this->mirroredUnits()
                ->when($onlyActive, fn ($q) => $q->active())
                ->orderBy('section_unit.order')
                ->orderBy('units.title')
                ->get();
        }

        $primaryIds = $primary->pluck('id');
        $mirrored = $mirrored->reject(fn ($u) => $primaryIds->contains($u->id))->values();

        return $primary->concat($mirrored);
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

