<?php

namespace App\AiHtmlQuiz\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiHtmlQuiz extends Model
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

    public const SCHEMA_VERSION = 'html-quiz-1.0';

    protected $table = 'ai_html_quizzes';

    protected $fillable = [
        'title',
        'description',
        'status',
        'prompt_meta',
        'bundle_html',
        'bundle_css',
        'bundle_js',
        'answer_key_json',
        'schema_version',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'prompt_meta' => 'array',
            'answer_key_json' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(AiHtmlQuizAttempt::class);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function hasBundle(): bool
    {
        return trim((string) $this->bundle_html) !== ''
            || trim((string) $this->bundle_css) !== ''
            || trim((string) $this->bundle_js) !== '';
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
