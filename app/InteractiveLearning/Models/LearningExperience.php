<?php

namespace App\InteractiveLearning\Models;

use App\Models\Lesson;
use App\Models\ReviewComment;
use App\Models\Subject;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LearningExperience extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_REVIEW = 'review';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_REVIEW,
        self::STATUS_PUBLISHED,
        self::STATUS_ARCHIVED,
    ];

    protected $fillable = [
        'title',
        'description',
        'status',
        'schema_json',
        'schema_version',
        'engine_version',
        'created_by',
        'subject_id',
        'unit_id',
        'lesson_id',
        'passing_score',
        'max_attempts',
        'review_notes',
        'reviewed_by',
        'reviewed_at',
        'submitted_for_review_at',
    ];

    protected function casts(): array
    {
        return [
            'schema_json' => 'array',
            'passing_score' => 'float',
            'max_attempts' => 'integer',
            'reviewed_at' => 'datetime',
            'submitted_for_review_at' => 'datetime',
        ];
    }

    /**
     * التجارب المنشورة فقط — الفلتر كان مكرّراً يدوياً في أكثر من سبعة مواضع،
     * وأي موضع يُنسى يعني تسريب مسوّدة للطالب.
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopePendingReview($query)
    {
        return $query->where('status', self::STATUS_REVIEW);
    }

    /**
     * لا يوجد عمود حالة "مرفوض" منفصل — انظر LearningExperience::isRejected().
     */
    public function scopeRejectedReview($query)
    {
        return $query->where('status', self::STATUS_DRAFT)
            ->whereNotNull('review_notes')
            ->whereNotNull('reviewed_at');
    }

    /**
     * ما يجوز للطالب رؤيته: منشورة ومربوطة بإحدى مواده.
     * التجربة بلا subject_id لا تظهر لأي طالب إطلاقاً.
     *
     * @param  array<int, int>  $subjectIds
     */
    public function scopeForStudentSubjects($query, array $subjectIds)
    {
        return $query->published()
            ->whereNotNull('subject_id')
            ->whereIn('subject_id', $subjectIds);
    }

    /**
     * ما يجوز لمشرف مراجعته: مقيّد بالصفوف/المواد المخصصة له.
     * مطابقة لـ Quiz::scopeForSupervisor.
     */
    public function scopeForSupervisor($query, $supervisorId)
    {
        $supervisor = User::find($supervisorId);

        if (! $supervisor || ! $supervisor->hasSupervisorStaffIdentity()) {
            return $query->whereRaw('1 = 0');
        }

        $classIds = $supervisor->assignedClassesAsSupervisor()->pluck('classes.id');
        $subjectIds = $supervisor->assignedSubjectsAsSupervisor()->pluck('subjects.id');

        return $query->whereHas('subject', function ($subjectQuery) use ($classIds, $subjectIds) {
            if ($classIds->isNotEmpty()) {
                $subjectQuery->whereIn('class_id', $classIds);
            }
            if ($subjectIds->isNotEmpty()) {
                if ($classIds->isNotEmpty()) {
                    $subjectQuery->orWhereIn('id', $subjectIds);
                } else {
                    $subjectQuery->whereIn('id', $subjectIds);
                }
            }
            if ($classIds->isEmpty() && $subjectIds->isEmpty()) {
                $subjectQuery->whereRaw('1 = 0');
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function reviewComments(): MorphMany
    {
        return $this->morphMany(ReviewComment::class, 'reviewable')->orderBy('created_at', 'asc');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(LearningExperienceAttempt::class);
    }

    public function attachables(): HasMany
    {
        return $this->hasMany(LearningExperienceAttachable::class);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isPendingReview(): bool
    {
        return $this->status === self::STATUS_REVIEW;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * لا يوجد عمود حالة "مرفوض" منفصل — الرفض يُمثَّل بالعودة إلى STATUS_DRAFT
     * مع تعبئة review_notes وreviewed_at، فنميّزه هنا عن مسودة لم تُراجَع قط.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_DRAFT
            && ! empty($this->review_notes)
            && $this->reviewed_at !== null;
    }

    public function questionsCount(): int
    {
        $schema = is_array($this->schema_json) ? $this->schema_json : [];

        return count($schema['questions'] ?? []);
    }

    public function canTransitionTo(string $status): bool
    {
        if (! in_array($status, self::STATUSES, true)) {
            return false;
        }

        $map = [
            self::STATUS_DRAFT => [self::STATUS_REVIEW, self::STATUS_PUBLISHED, self::STATUS_ARCHIVED],
            self::STATUS_REVIEW => [self::STATUS_DRAFT, self::STATUS_PUBLISHED, self::STATUS_ARCHIVED],
            self::STATUS_PUBLISHED => [self::STATUS_REVIEW, self::STATUS_ARCHIVED],
            self::STATUS_ARCHIVED => [self::STATUS_DRAFT],
        ];

        return in_array($status, $map[$this->status] ?? [], true);
    }
}
