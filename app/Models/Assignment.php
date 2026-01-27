<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Assignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'assignments';

    protected $fillable = [
        'title',
        'description',
        'instructions',
        'assignable_type',
        'assignable_id',
        'created_by',
        'max_score',
        'due_date',
        'allow_late_submission',
        'late_penalty_percentage',
        'max_attempts',
        'allowed_file_types',
        'max_file_size',
        'max_files_per_submission',
        'grading_type',
        'is_published',
        'published_at',
        'review_status',
        'review_notes',
        'reviewed_by',
        'reviewed_at',
        'submitted_for_review_at',
    ];

    protected $casts = [
        'max_score' => 'decimal:2',
        'due_date' => 'datetime',
        'allow_late_submission' => 'boolean',
        'late_penalty_percentage' => 'decimal:2',
        'max_attempts' => 'integer',
        'allowed_file_types' => 'array',
        'max_file_size' => 'integer',
        'max_files_per_submission' => 'integer',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'reviewed_by' => 'integer',
        'reviewed_at' => 'datetime',
        'submitted_for_review_at' => 'datetime',
    ];

    /**
     * أنواع التصحيح المتاحة.
     */
    const GRADING_TYPES = [
        'manual' => 'تصحيح يدوي',
        'auto' => 'تصحيح تلقائي',
        'mixed' => 'مزيج',
    ];

    /**
     * حالات المراجعة.
     */
    const REVIEW_STATUS_DRAFT = 'draft';
    const REVIEW_STATUS_PENDING = 'pending_review';
    const REVIEW_STATUS_APPROVED = 'approved';
    const REVIEW_STATUS_REJECTED = 'rejected';

    public const REVIEW_STATUSES = [
        self::REVIEW_STATUS_DRAFT => 'مسودة',
        self::REVIEW_STATUS_PENDING => 'قيد المراجعة',
        self::REVIEW_STATUS_APPROVED => 'معتمد',
        self::REVIEW_STATUS_REJECTED => 'مرفوض',
    ];

    /**
     * العلاقة مع العنصر المرتبط (Subject/Unit/Lesson) - Polymorphic
     */
    public function assignable()
    {
        return $this->morphTo();
    }

    /**
     * العلاقة مع المعلم الذي أنشأ الواجب
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * العلاقة مع المشرف الذي راجع الواجب.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * العلاقة مع ملاحظات المراجعة.
     */
    public function reviewComments(): MorphMany
    {
        return $this->morphMany(ReviewComment::class, 'reviewable')->orderBy('created_at', 'asc');
    }

    /**
     * العلاقة مع أسئلة الواجب
     */
    public function questions()
    {
        return $this->hasMany(AssignmentQuestion::class)->orderBy('order');
    }

    /**
     * العلاقة مع إرسالات الطلاب
     */
    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    /**
     * Scope للواجبات المنشورة
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope للواجبات النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('is_published', true)
                    ->where(function($q) {
                        $q->whereNull('due_date')
                          ->orWhere('due_date', '>=', now());
                    });
    }

    /**
     * Scope للواجبات القادمة
     */
    public function scopeUpcoming($query)
    {
        return $query->where('is_published', true)
                    ->where('due_date', '>', now());
    }

    /**
     * Scope للواجبات المتأخرة
     */
    public function scopeOverdue($query)
    {
        return $query->where('is_published', true)
                    ->where('due_date', '<', now());
    }

    /**
     * التحقق من انتهاء موعد التسليم
     */
    public function isOverdue(): bool
    {
        if (!$this->due_date) {
            return false;
        }
        return $this->due_date->isPast();
    }

    /**
     * التحقق من إمكانية إرسال الواجب
     */
    public function canSubmit(): bool
    {
        if (!$this->is_published) {
            return false;
        }

        if ($this->isOverdue() && !$this->allow_late_submission) {
            return false;
        }

        return true;
    }

    /**
     * الحصول على أنواع الملفات المسموحة كمصفوفة
     */
    public function getAllowedFileTypesArray(): array
    {
        return $this->allowed_file_types ?? [];
    }

    /**
     * التحقق من نوع ملف مسموح
     */
    public function isFileTypeAllowed(string $fileType): bool
    {
        $allowedTypes = $this->getAllowedFileTypesArray();
        if (empty($allowedTypes)) {
            return true; // إذا لم يتم تحديد أنواع، السماح بجميع الأنواع
        }
        return in_array(strtolower($fileType), array_map('strtolower', $allowedTypes));
    }

    /**
     * Scope للواجبات قيد المراجعة.
     */
    public function scopePendingReview($query)
    {
        return $query->where('review_status', self::REVIEW_STATUS_PENDING);
    }

    /**
     * Scope للواجبات الموافق عليها.
     */
    public function scopeApproved($query)
    {
        return $query->where('review_status', self::REVIEW_STATUS_APPROVED);
    }

    /**
     * Scope للواجبات المرفوضة.
     */
    public function scopeRejected($query)
    {
        return $query->where('review_status', self::REVIEW_STATUS_REJECTED);
    }

    /**
     * Scope للواجبات المخصصة لمشرف معين.
     * يعرض فقط الواجبات من المواد/الصفوف المخصصة للمشرف.
     */
    public function scopeForSupervisor($query, $supervisorId)
    {
        $supervisor = \App\Models\User::find($supervisorId);
        
        if (!$supervisor || !$supervisor->hasRole('supervisor')) {
            return $query->whereRaw('1 = 0'); // Always false
        }

        // الحصول على المواد والصفوف المخصصة للمشرف
        $classIds = $supervisor->assignedClassesAsSupervisor()->pluck('classes.id');
        $subjectIds = $supervisor->assignedSubjectsAsSupervisor()->pluck('subjects.id');

        return $query->whereHasMorph('assignable', [Subject::class, Unit::class, Lesson::class], function($query, $type) use ($classIds, $subjectIds) {
            if ($type === Subject::class) {
                if ($classIds->isNotEmpty()) {
                    $query->whereIn('class_id', $classIds);
                }
                if ($subjectIds->isNotEmpty()) {
                    if ($classIds->isNotEmpty()) {
                        $query->orWhereIn('id', $subjectIds);
                    } else {
                        $query->whereIn('id', $subjectIds);
                    }
                }
            } elseif ($type === Unit::class) {
                $query->whereHas('section.subject', function($subjectQuery) use ($classIds, $subjectIds) {
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
                });
            } elseif ($type === Lesson::class) {
                $query->whereHas('unit.section.subject', function($subjectQuery) use ($classIds, $subjectIds) {
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
                });
            }
            // إذا لم يكن هناك أي تخصيصات، إرجاع query فارغ
            if ($classIds->isEmpty() && $subjectIds->isEmpty()) {
                $query->whereRaw('1 = 0'); // Always false condition
            }
        });
    }

    /**
     * Helper methods للتحقق من حالة المراجعة
     */
    public function isPendingReview(): bool
    {
        return $this->review_status === self::REVIEW_STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->review_status === self::REVIEW_STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->review_status === self::REVIEW_STATUS_REJECTED;
    }

    public function isDraft(): bool
    {
        return $this->review_status === self::REVIEW_STATUS_DRAFT;
    }

    public function getReviewStatusNameAttribute(): string
    {
        return self::REVIEW_STATUSES[$this->review_status] ?? $this->review_status;
    }

    public function getReviewStatusColorAttribute(): string
    {
        return match($this->review_status) {
            self::REVIEW_STATUS_DRAFT => 'secondary',
            self::REVIEW_STATUS_PENDING => 'warning',
            self::REVIEW_STATUS_APPROVED => 'success',
            self::REVIEW_STATUS_REJECTED => 'danger',
            default => 'dark',
        };
    }
}
