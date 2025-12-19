<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
        return match($this->difficulty) {
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

