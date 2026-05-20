<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * أنواع الأسئلة المتاحة
     */
    public const TYPES = [
        'single_choice' => 'اختيار واحد',
        'multiple_choice' => 'اختيار متعدد',
        'true_false' => 'صح / خطأ',
        'short_answer' => 'إجابة قصيرة',
        'essay' => 'مقالي',
        'matching' => 'مطابقة',
        'ordering' => 'ترتيب',
        'fill_blanks' => 'ملء الفراغات',
        'numerical' => 'رقمي',
        'drag_drop' => 'سحب وإفلات',
    ];

    /**
     * مستويات الصعوبة
     */
    public const DIFFICULTIES = [
        'easy' => 'سهل',
        'medium' => 'متوسط',
        'hard' => 'صعب',
    ];

    /**
     * أيقونات أنواع الأسئلة
     */
    public const TYPE_ICONS = [
        'single_choice' => 'bi-ui-radios',
        'multiple_choice' => 'bi-ui-checks',
        'true_false' => 'bi-toggle-on',
        'short_answer' => 'bi-input-cursor-text',
        'essay' => 'bi-file-text',
        'matching' => 'bi-arrow-left-right',
        'ordering' => 'bi-list-ol',
        'fill_blanks' => 'bi-input-cursor',
        'numerical' => 'bi-123',
        'drag_drop' => 'bi-hand-index',
    ];

    /**
     * ألوان أنواع الأسئلة
     */
    public const TYPE_COLORS = [
        'single_choice' => 'primary',
        'multiple_choice' => 'info',
        'true_false' => 'success',
        'short_answer' => 'warning',
        'essay' => 'secondary',
        'matching' => 'danger',
        'ordering' => 'dark',
        'fill_blanks' => 'primary',
        'numerical' => 'info',
        'drag_drop' => 'warning',
    ];

    protected $fillable = [
        'type',
        'title',
        'content',
        'explanation',
        'image',
        'difficulty',
        'default_points',
        'case_sensitive',
        'tolerance',
        'blank_answers',
        'is_active',
        'created_by',
        'subject_id',
        'category',
        'tags',
    ];

    protected $casts = [
        'default_points' => 'decimal:2',
        'tolerance' => 'decimal:4',
        'case_sensitive' => 'boolean',
        'is_active' => 'boolean',
        'blank_answers' => 'array',
        'tags' => 'array',
    ];

    /**
     * رابط مطلق لعرض صورة مخزّنة في HTML (TinyMCE) بعد الحفظ.
     */
    public static function absoluteImageUrlForDisplay(string $src): string
    {
        $src = trim($src);
        if ($src === '') {
            return '';
        }

        if (str_starts_with(strtolower($src), 'blob:')) {
            return '';
        }

        if (str_starts_with(strtolower($src), 'data:')) {
            return $src;
        }

        if (preg_match('#^https?://#i', $src)) {
            $appHost = parse_url((string) config('app.url', ''), PHP_URL_HOST);
            $srcHost = parse_url($src, PHP_URL_HOST);
            if ($appHost && $srcHost && strcasecmp((string) $srcHost, (string) $appHost) === 0) {
                if (preg_match('#^https?://[^/]+/storage/(.+)$#i', $src, $m)) {
                    return url('/storage/'.$m[1]);
                }
            }

            return $src;
        }

        if (str_starts_with($src, '//')) {
            return (request()->isSecure() ? 'https:' : 'http:').$src;
        }

        if (str_starts_with($src, '/storage/')) {
            return url($src);
        }

        if (str_starts_with($src, '/')) {
            return url($src);
        }

        $fromMedia = media_public_url(ltrim($src, '/'));
        if ($fromMedia !== '') {
            if (preg_match('#^https?://#i', $fromMedia)) {
                return $fromMedia;
            }
            if (str_starts_with($fromMedia, '/')) {
                return url($fromMedia);
            }

            return url('/'.ltrim($fromMedia, '/'));
        }

        return url('/storage/'.ltrim($src, '/'));
    }

    /**
     * يعيد كتابة src لكل &lt;img&gt; داخل HTML إلى روابط مطلقة وتزيل blob: الفاشل.
     */
    public static function normalizeHtmlEmbeddedImageUrls(?string $html): ?string
    {
        if ($html === null || $html === '') {
            return $html;
        }

        return (string) preg_replace_callback(
            '#<img\b([^>]*?)\bsrc\s*=\s*("|\')([^"\']*)\2([^>]*)>#is',
            function (array $m) {
                $before = $m[1];
                $q = $m[2];
                $src = $m[3];
                $after = $m[4];
                $normalized = self::absoluteImageUrlForDisplay($src);
                if ($normalized === '') {
                    return '';
                }

                return '<img'.$before.' src='.$q.$normalized.$q.$after.'>';
            },
            $html
        );
    }

    /**
     * العلاقات
     */
    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
    }

    public function correctOptions(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->where('is_correct', true)->orderBy('order');
    }

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'question_units')->withTimestamps();
    }

    /**
     * صفوف المنهج المرتبطة (صف، مادة، وحدة) بدون تكرار.
     *
     * @return Collection<int, array{class: ?string, subject: ?string, unit: string}>
     */
    public function curriculumLocations(): Collection
    {
        return $this->units->map(function (Unit $unit) {
            $subject = $unit->section?->subject;

            return [
                'class' => $subject?->schoolClass?->name,
                'subject' => $subject?->name,
                'unit' => $unit->title,
            ];
        })->unique(function (array $row) {
            return ($row['class'] ?? '').'|'.($row['subject'] ?? '').'|'.($row['unit'] ?? '');
        })->values();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function quizzes(): BelongsToMany
    {
        return $this->belongsToMany(Quiz::class, 'quiz_questions')
            ->withPivot(['order', 'points', 'is_required', 'shuffle_options'])
            ->withTimestamps();
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }

    public function questionAttempts(): HasMany
    {
        return $this->hasMany(QuestionAttempt::class);
    }

    public function questionAnswers(): HasMany
    {
        return $this->hasMany(QuestionAnswer::class);
    }

    /**
     * العلاقة مع حلول AI
     */
    public function aiSolutions()
    {
        return $this->hasMany(AIQuestionSolution::class, 'question_id');
    }

    /**
     * الحصول على نص السؤال
     */
    public function getQuestionText(): string
    {
        return $this->content ?? $this->title ?? '';
    }

    /**
     * الحصول على الإجابة الصحيحة
     */
    public function getCorrectAnswer(): string
    {
        $correctOptions = $this->correctOptions;
        if ($correctOptions->count() > 0) {
            return $correctOptions->pluck('content')->implode(', ');
        }

        return '';
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOfDifficulty($query, $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    public function scopeInCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeInUnits($query, array $unitIds)
    {
        return $query->whereHas('units', function ($q) use ($unitIds) {
            $q->whereIn('units.id', $unitIds);
        });
    }

    /**
     * أسئلة مرتبطة بمادة: subject_id مباشر أو وحدات ضمن المادة.
     */
    public function scopeForSubject($query, Subject|int $subject)
    {
        $subjectId = $subject instanceof Subject ? $subject->id : (int) $subject;

        return $query->where(function ($q) use ($subjectId) {
            $q->where('subject_id', $subjectId)
                ->orWhereHas('units.section', function ($sq) use ($subjectId) {
                    $sq->where('subject_id', $subjectId);
                });
        });
    }

    public function scopeGeneral($query)
    {
        return $query->whereDoesntHave('units');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('content', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%");
        });
    }

    /**
     * Accessors
     */
    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getDifficultyNameAttribute(): string
    {
        return self::DIFFICULTIES[$this->difficulty] ?? $this->difficulty;
    }

    public function getTypeIconAttribute(): string
    {
        return self::TYPE_ICONS[$this->type] ?? 'bi-question-circle';
    }

    public function getTypeColorAttribute(): string
    {
        return self::TYPE_COLORS[$this->type] ?? 'secondary';
    }

    public function getDifficultyColorAttribute(): string
    {
        return match ($this->difficulty) {
            'easy' => 'success',
            'medium' => 'warning',
            'hard' => 'danger',
            default => 'secondary',
        };
    }

    public function getIsGeneralAttribute(): bool
    {
        return $this->units()->count() === 0;
    }

    /**
     * هل يحتاج السؤال تصحيح يدوي
     */
    public function getNeedsManualGradingAttribute(): bool
    {
        return in_array($this->type, ['essay', 'short_answer']);
    }

    /**
     * هل السؤال له خيارات
     */
    public function getHasOptionsAttribute(): bool
    {
        return in_array($this->type, ['single_choice', 'multiple_choice', 'true_false', 'matching', 'ordering', 'drag_drop']);
    }
}
