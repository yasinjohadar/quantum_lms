<?php

namespace App\InteractiveLearning\Models;

use App\Models\Lesson;
use App\Models\Subject;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    ];

    protected function casts(): array
    {
        return [
            'schema_json' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
