<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Lesson extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lessons';

    protected $fillable = [
        'unit_id',
        'section_id',
        'sync_group_id',
        'is_sync_canonical',
        'cloned_from_lesson_id',
        'title',
        'description',
        'video_type',
        'video_url',
        'video_id',
        'thumbnail',
        'duration',
        'book_page_from',
        'book_page_to',
        'order',
        'is_active',
        'is_free',
        'is_preview',
        'review_status',
        'review_notes',
        'reviewed_by',
        'reviewed_at',
        'submitted_for_review_at',
    ];

    protected $casts = [
        'unit_id' => 'integer',
        'section_id' => 'integer',
        'is_sync_canonical' => 'boolean',
        'cloned_from_lesson_id' => 'integer',
        'duration' => 'integer',
        'book_page_from' => 'integer',
        'book_page_to' => 'integer',
        'order' => 'integer',
        'is_active' => 'boolean',
        'is_free' => 'boolean',
        'is_preview' => 'boolean',
        'reviewed_by' => 'integer',
        'reviewed_at' => 'datetime',
        'submitted_for_review_at' => 'datetime',
    ];

    /**
     * أنواع الفيديو المتاحة.
     */
    const VIDEO_TYPES = [
        'upload' => 'رفع مباشر',
        'youtube' => 'يوتيوب',
        'vimeo' => 'فيميو',
        'external' => 'رابط خارجي',
    ];

    /**
     * حالات المراجعة.
     */
    const REVIEW_STATUS_DRAFT = 'draft';
    const REVIEW_STATUS_PENDING = 'pending_review';
    const REVIEW_STATUS_APPROVED = 'approved';
    const REVIEW_STATUS_REJECTED = 'rejected';

    /** تسميات حالات المراجعة للعرض. */
    public const REVIEW_STATUSES = [
        self::REVIEW_STATUS_DRAFT => 'مسودة',
        self::REVIEW_STATUS_PENDING => 'قيد المراجعة',
        self::REVIEW_STATUS_APPROVED => 'معتمد',
        self::REVIEW_STATUS_REJECTED => 'مرفوض',
    ];

    /**
     * العلاقة مع الوحدة الأصلية (التي أنشئ فيها الدرس).
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * العلاقة مع القسم عند الربط المباشر بدون وحدة.
     */
    public function section()
    {
        return $this->belongsTo(SubjectSection::class, 'section_id');
    }

    public function clonedFromLesson()
    {
        return $this->belongsTo(Lesson::class, 'cloned_from_lesson_id');
    }

    public function isSyncMirror(): bool
    {
        return $this->cloned_from_lesson_id !== null;
    }

    /**
     * معرفات الوحدات التي تحتوي نسخة مرتبطة من هذا الدرس (anchor).
     */
    public function linkedUnitIdsViaSync(): array
    {
        return static::query()
            ->where('cloned_from_lesson_id', $this->id)
            ->whereNotNull('unit_id')
            ->pluck('unit_id')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * الوحدات التي تحتوي نسخة مرتبطة من هذا الدرس (anchor).
     */
    public function linkedUnitsViaSync()
    {
        $unitIds = $this->linkedUnitIdsViaSync();

        if ($unitIds === []) {
            return collect();
        }

        return Unit::with('section.subject.schoolClass.stage')->whereIn('id', $unitIds)->get();
    }

    /**
     * الوحدات الإضافية المرتبطة بالدرس عبر lesson_units (legacy — ظهور الدرس في وحدات أخرى).
     */
    public function linkedUnits(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'lesson_units')->withTimestamps();
    }

    /**
     * جميع الوحدات التي يظهر فيها الدرس (الوحدة الأصلية + الوحدات المرتبطة)، بدون تكرار.
     */
    public function allUnits()
    {
        $primary = $this->unit;
        $linked = $this->linkedUnits;
        if (!$primary) {
            return $linked;
        }
        $linkedIds = $linked->pluck('id')->toArray();
        if (in_array($primary->id, $linkedIds, true)) {
            return $linked;
        }
        return $linked->prepend($primary);
    }

    /**
     * العلاقة مع المشرف الذي راجع الدرس.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * العلاقة مع المرفقات.
     */
    public function attachments()
    {
        return $this->hasMany(LessonAttachment::class)->orderBy('order');
    }

    /**
     * العلاقة مع الاختبارات المرتبطة بهذا الدرس.
     * هذه الاختبارات هي من نوع \"اختبار الدرس\" وليست الاختبارات العامة للوحدة.
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'lesson_id')->orderBy('order');
    }

    /**
     * العلاقة مع محاولات الأسئلة.
     */
    public function questionAttempts()
    {
        return $this->hasMany(QuestionAttempt::class);
    }

    /**
     * العلاقة مع إكمالات الدروس.
     */
    public function completions()
    {
        return $this->hasMany(LessonCompletion::class);
    }

    /**
     * العلاقة مع ملاحظات المراجعة.
     */
    public function reviewComments(): MorphMany
    {
        return $this->morphMany(ReviewComment::class, 'reviewable')->orderBy('created_at', 'asc');
    }

    /**
     * الحصول على مدة الفيديو بصيغة مقروءة.
     */
    public function getFormattedDurationAttribute()
    {
        if (!$this->duration) {
            return null;
        }

        $hours = floor($this->duration / 3600);
        $minutes = floor(($this->duration % 3600) / 60);
        $seconds = $this->duration % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * الحصول على رابط الفيديو للتشغيل.
     */
    public function getEmbedUrlAttribute()
    {
        // اكتشاف تلقائي لروابط YouTube و Vimeo حتى لو تم اختيار "رابط خارجي"
        $youtubeId = self::extractYoutubeId($this->video_url);
        $vimeoId = self::extractVimeoId($this->video_url);

        // لو الرابط هو YouTube
        if ($youtubeId) {
            return "https://www.youtube.com/embed/{$youtubeId}";
        }

        // لو الرابط هو Vimeo (مع الحفاظ على ?h= للفيديوهات الخاصة/غير المدرجة)
        if ($vimeoId) {
            $embed = "https://player.vimeo.com/video/{$vimeoId}";
            $query = [];
            if (!empty($this->video_url)) {
                $parsed = parse_url($this->video_url);
                if (!empty($parsed['query'])) {
                    parse_str($parsed['query'], $query);
                }
            }
            if (!empty($query['h'])) {
                $embed .= '?'.http_build_query(['h' => $query['h']]);
            }

            return $embed;
        }

        // للملفات المرفوعة
        if ($this->video_type === 'upload' && $this->video_url) {
            return media_public_url($this->video_url);
        }

        // رابط خارجي عادي
        return $this->video_url;
    }

    /**
     * الحصول على نوع الفيديو الفعلي (للعرض).
     */
    public function getActualVideoTypeAttribute()
    {
        if (self::extractYoutubeId($this->video_url)) {
            return 'youtube';
        }
        if (self::extractVimeoId($this->video_url)) {
            return 'vimeo';
        }
        return $this->video_type;
    }

    /**
     * استخراج معرف الفيديو من رابط YouTube.
     */
    public static function extractYoutubeId($url)
    {
        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/';
        preg_match($pattern, $url, $matches);
        return $matches[1] ?? null;
    }

    /**
     * استخراج معرف الفيديو من رابط Vimeo.
     */
    public static function extractVimeoId($url)
    {
        if (!$url || !is_string($url)) {
            return null;
        }

        // Supports most Vimeo URL formats, e.g.:
        // vimeo.com/123, vimeo.com/channels/name/123, player.vimeo.com/video/123
        $pattern = '/(?:player\.)?vimeo\.com\/(?:.*\/)?(\d+)(?:$|[?&#])/i';
        if (preg_match($pattern, trim($url), $matches)) {
            return $matches[1] ?? null;
        }

        return null;
    }

    /**
     * Scope للدروس النشطة.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope للدروس المجانية.
     */
    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }

    /**
     * Scope للترتيب.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function getReviewStatusNameAttribute(): string
    {
        return self::REVIEW_STATUSES[$this->review_status] ?? $this->review_status;
    }

    public function getReviewStatusColorAttribute(): string
    {
        return match ($this->review_status) {
            self::REVIEW_STATUS_DRAFT => 'secondary',
            self::REVIEW_STATUS_PENDING => 'warning',
            self::REVIEW_STATUS_APPROVED => 'success',
            self::REVIEW_STATUS_REJECTED => 'danger',
            default => 'dark',
        };
    }

    /**
     * Scope للدروس قيد المراجعة.
     */
    public function scopePendingReview($query)
    {
        return $query->where('review_status', self::REVIEW_STATUS_PENDING);
    }

    /**
     * Scope للدروس الموافق عليها.
     */
    public function scopeApproved($query)
    {
        return $query->where('review_status', self::REVIEW_STATUS_APPROVED);
    }

    /**
     * Scope للدروس المرفوضة.
     */
    public function scopeRejected($query)
    {
        return $query->where('review_status', self::REVIEW_STATUS_REJECTED);
    }

    /**
     * Scope للدروس المخصصة لمشرف معين.
     * يعرض فقط الدروس من المواد/الصفوف المخصصة للمشرف.
     */
    public function scopeForSupervisor($query, $supervisorId)
    {
        $supervisor = \App\Models\User::find($supervisorId);
        
        if (!$supervisor || !$supervisor->hasSupervisorStaffIdentity()) {
            return $query->whereRaw('1 = 0'); // Always false
        }

        // الحصول على المواد والصفوف المخصصة للمشرف
        $classIds = $supervisor->assignedClassesAsSupervisor()->pluck('classes.id');
        $subjectIds = $supervisor->assignedSubjectsAsSupervisor()->pluck('subjects.id');

        return $query->where(function ($q) use ($classIds, $subjectIds) {
            $applySubjectFilter = function ($subjectQuery) use ($classIds, $subjectIds) {
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
            };

            $q->whereHas('unit.section.subject', $applySubjectFilter)
                ->orWhereHas('section.subject', $applySubjectFilter);
        });
    }
}
