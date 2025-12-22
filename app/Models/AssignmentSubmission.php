<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssignmentSubmission extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'assignment_submissions';

    protected $fillable = [
        'assignment_id',
        'student_id',
        'attempt_number',
        'submitted_at',
        'is_late',
        'status',
        'total_score',
        'grade_percentage',
        'graded_at',
        'graded_by',
        'feedback',
    ];

    protected $casts = [
        'assignment_id' => 'integer',
        'student_id' => 'integer',
        'attempt_number' => 'integer',
        'submitted_at' => 'datetime',
        'is_late' => 'boolean',
        'total_score' => 'decimal:2',
        'grade_percentage' => 'decimal:2',
        'graded_at' => 'datetime',
        'graded_by' => 'integer',
    ];

    /**
     * حالات الإرسال المتاحة.
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_GRADED = 'graded';
    const STATUS_RETURNED = 'returned';

    const STATUSES = [
        self::STATUS_DRAFT => 'مسودة',
        self::STATUS_SUBMITTED => 'تم الإرسال',
        self::STATUS_GRADED => 'تم التصحيح',
        self::STATUS_RETURNED => 'تم الإرجاع',
    ];

    /**
     * العلاقة مع الواجب
     */
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * العلاقة مع الطالب
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * العلاقة مع ملفات الإرسال
     */
    public function files()
    {
        return $this->hasMany(AssignmentSubmissionFile::class, 'submission_id')->orderBy('order');
    }

    /**
     * العلاقة مع إجابات الطلاب
     */
    public function answers()
    {
        return $this->hasMany(AssignmentSubmissionAnswer::class, 'submission_id');
    }

    /**
     * العلاقة مع درجات التصحيح اليدوي
     */
    public function grades()
    {
        return $this->hasMany(AssignmentGrade::class, 'submission_id');
    }

    /**
     * العلاقة مع المعلم الذي صحح الواجب
     */
    public function grader()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * حساب الدرجة الإجمالية
     */
    public function calculateTotalScore(): float
    {
        $totalScore = 0.0;

        // جمع درجات الأسئلة التلقائية
        $autoScore = $this->answers()->sum('points_earned');
        $totalScore += $autoScore;

        // جمع الدرجات اليدوية
        $manualScore = $this->grades()->sum('manual_score');
        $totalScore += $manualScore;

        return (float) $totalScore;
    }

    /**
     * التحقق من التأخير
     */
    public function isLate(): bool
    {
        if (!$this->assignment->due_date || !$this->submitted_at) {
            return false;
        }
        return $this->submitted_at->isAfter($this->assignment->due_date);
    }

    /**
     * التحقق من إمكانية إعادة الإرسال
     */
    public function canResubmit(): bool
    {
        if (!$this->assignment) {
            return false;
        }

        // التحقق من عدد المحاولات
        $currentAttempts = AssignmentSubmission::where('assignment_id', $this->assignment_id)
            ->where('student_id', $this->student_id)
            ->count();

        if ($currentAttempts >= $this->assignment->max_attempts) {
            return false;
        }

        // يمكن إعادة الإرسال إذا كان الواجب في حالة returned أو graded
        return in_array($this->status, [self::STATUS_RETURNED, self::STATUS_GRADED]);
    }

    /**
     * الحصول على تسمية الحالة
     */
    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Scope للإرسالات حسب الحالة
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope للإرسالات المتأخرة
     */
    public function scopeLate($query)
    {
        return $query->where('is_late', true);
    }

    /**
     * Scope لإرسالات طالب معين
     */
    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }
}
