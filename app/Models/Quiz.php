<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class Quiz extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * طرق احتساب الدرجة
     */
    public const GRADING_METHODS = [
        'highest' => 'أعلى درجة',
        'last' => 'آخر محاولة',
        'average' => 'متوسط المحاولات',
        'first' => 'أول محاولة',
    ];

    /**
     * خيارات مراجعة الإجابات
     */
    public const REVIEW_OPTIONS = [
        'none' => 'لا يمكن المراجعة',
        'immediately' => 'فور الانتهاء',
        'after_close' => 'بعد انتهاء فترة الاختبار',
        'always' => 'دائماً',
    ];

    protected $fillable = [
        'subject_id',
        'unit_id',
        'title',
        'description',
        'instructions',
        'image',
        'duration_minutes',
        'show_timer',
        'auto_submit',
        'max_attempts',
        'delay_between_attempts',
        'pass_percentage',
        'total_points',
        'grading_method',
        'shuffle_questions',
        'shuffle_options',
        'questions_per_page',
        'allow_back_navigation',
        'show_result_immediately',
        'show_correct_answers',
        'show_explanation',
        'show_points_per_question',
        'review_options',
        'available_from',
        'available_to',
        'is_active',
        'is_published',
        'requires_password',
        'password',
        'require_webcam',
        'prevent_copy_paste',
        'fullscreen_required',
        'order',
        'created_by',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'show_timer' => 'boolean',
        'auto_submit' => 'boolean',
        'max_attempts' => 'integer',
        'delay_between_attempts' => 'integer',
        'pass_percentage' => 'decimal:2',
        'total_points' => 'decimal:2',
        'shuffle_questions' => 'boolean',
        'shuffle_options' => 'boolean',
        'questions_per_page' => 'integer',
        'allow_back_navigation' => 'boolean',
        'show_result_immediately' => 'boolean',
        'show_correct_answers' => 'boolean',
        'show_explanation' => 'boolean',
        'show_points_per_question' => 'boolean',
        'available_from' => 'datetime',
        'available_to' => 'datetime',
        'is_active' => 'boolean',
        'is_published' => 'boolean',
        'requires_password' => 'boolean',
        'require_webcam' => 'boolean',
        'prevent_copy_paste' => 'boolean',
        'fullscreen_required' => 'boolean',
        'order' => 'integer',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * العلاقات
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'quiz_questions')
            ->withPivot(['order', 'points', 'is_required', 'shuffle_options'])
            ->withTimestamps()
            ->orderBy('quiz_questions.order');
    }

    public function quizQuestions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeAvailable($query)
    {
        $now = now();
        return $query->where(function ($q) use ($now) {
            $q->whereNull('available_from')
              ->orWhere('available_from', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('available_to')
              ->orWhere('available_to', '>=', $now);
        });
    }

    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeForUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('title');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Accessors
     */
    public function getGradingMethodNameAttribute(): string
    {
        return self::GRADING_METHODS[$this->grading_method] ?? $this->grading_method;
    }

    public function getReviewOptionsNameAttribute(): string
    {
        return self::REVIEW_OPTIONS[$this->review_options] ?? $this->review_options;
    }

    public function getQuestionsCountAttribute(): int
    {
        return $this->questions()->count();
    }

    public function getFormattedDurationAttribute(): ?string
    {
        if (!$this->duration_minutes) {
            return 'غير محدود';
        }

        $hours = intdiv($this->duration_minutes, 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours} ساعة و {$minutes} دقيقة";
        } elseif ($hours > 0) {
            return "{$hours} ساعة";
        } else {
            return "{$minutes} دقيقة";
        }
    }

    public function getIsAvailableAttribute(): bool
    {
        $now = now();
        
        if ($this->available_from && $now->lt($this->available_from)) {
            return false;
        }
        
        if ($this->available_to && $now->gt($this->available_to)) {
            return false;
        }
        
        return true;
    }

    public function getAvailabilityStatusAttribute(): string
    {
        $now = now();
        
        if ($this->available_from && $now->lt($this->available_from)) {
            return 'upcoming';
        }
        
        if ($this->available_to && $now->gt($this->available_to)) {
            return 'closed';
        }
        
        return 'open';
    }

    public function getAvailabilityStatusNameAttribute(): string
    {
        return match($this->availability_status) {
            'upcoming' => 'لم يبدأ بعد',
            'closed' => 'انتهى',
            'open' => 'متاح الآن',
            default => 'غير معروف',
        };
    }

    public function getAvailabilityStatusColorAttribute(): string
    {
        return match($this->availability_status) {
            'upcoming' => 'warning',
            'closed' => 'secondary',
            'open' => 'success',
            default => 'dark',
        };
    }

    /**
     * Methods
     */
    public function calculateTotalPoints(): void
    {
        $this->total_points = $this->questions()->sum('quiz_questions.points');
        $this->save();
    }

    public function canUserAttempt(User $user): array
    {
        // التحقق من نشاط الاختبار
        if (!$this->is_active || !$this->is_published) {
            return ['can' => false, 'reason' => 'الاختبار غير متاح حالياً'];
        }

        // التحقق من التوقيت
        if (!$this->is_available) {
            if ($this->availability_status === 'upcoming') {
                return ['can' => false, 'reason' => 'لم يبدأ الاختبار بعد'];
            }
            return ['can' => false, 'reason' => 'انتهت فترة الاختبار'];
        }

        // التحقق من عدد المحاولات
        if ($this->max_attempts > 0) {
            $userAttempts = $this->attempts()
                ->where('user_id', $user->id)
                ->whereIn('status', ['completed', 'timed_out'])
                ->count();

            if ($userAttempts >= $this->max_attempts) {
                return ['can' => false, 'reason' => 'لقد استنفذت جميع محاولاتك'];
            }
        }

        // التحقق من التأخير بين المحاولات
        if ($this->delay_between_attempts > 0) {
            $lastAttempt = $this->attempts()
                ->where('user_id', $user->id)
                ->whereIn('status', ['completed', 'timed_out'])
                ->latest('finished_at')
                ->first();

            if ($lastAttempt && $lastAttempt->finished_at) {
                $canRetryAt = $lastAttempt->finished_at->addMinutes($this->delay_between_attempts);
                if (now()->lt($canRetryAt)) {
                    $remainingMinutes = now()->diffInMinutes($canRetryAt, false);
                    return ['can' => false, 'reason' => "يجب الانتظار {$remainingMinutes} دقيقة قبل المحاولة التالية"];
                }
            }
        }

        return ['can' => true, 'reason' => null];
    }

    public function getUserAttemptsCount(User $user): int
    {
        return $this->attempts()
            ->where('user_id', $user->id)
            ->whereIn('status', ['completed', 'timed_out'])
            ->count();
    }

    public function getUserBestScore(User $user): ?float
    {
        return $this->attempts()
            ->where('user_id', $user->id)
            ->whereIn('status', ['completed', 'timed_out'])
            ->max('percentage');
    }

    public function getUserLastAttempt(User $user): ?QuizAttempt
    {
        return $this->attempts()
            ->where('user_id', $user->id)
            ->latest('started_at')
            ->first();
    }

    public function getUserFinalGrade(User $user): ?float
    {
        $attempts = $this->attempts()
            ->where('user_id', $user->id)
            ->whereIn('status', ['completed', 'timed_out'])
            ->get();

        if ($attempts->isEmpty()) {
            return null;
        }

        return match($this->grading_method) {
            'highest' => $attempts->max('percentage'),
            'last' => $attempts->last()->percentage,
            'first' => $attempts->first()->percentage,
            'average' => $attempts->avg('percentage'),
            default => $attempts->max('percentage'),
        };
    }
}

