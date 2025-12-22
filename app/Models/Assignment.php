<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
