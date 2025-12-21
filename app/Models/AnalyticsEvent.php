<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'user_id',
        'subject_id',
        'lesson_id',
        'quiz_id',
        'question_id',
        'metadata', // JSON - بيانات إضافية
    ];

    protected $casts = [
        'metadata' => 'array',
        'user_id' => 'integer',
        'subject_id' => 'integer',
        'lesson_id' => 'integer',
        'quiz_id' => 'integer',
        'question_id' => 'integer',
    ];

    /**
     * أنواع الأحداث
     */
    public const EVENT_TYPES = [
        'view_lesson' => 'عرض درس',
        'complete_lesson' => 'إكمال درس',
        'start_quiz' => 'بدء اختبار',
        'complete_quiz' => 'إكمال اختبار',
        'answer_question' => 'إجابة سؤال',
        'enroll_subject' => 'الانضمام لكورس',
        'login' => 'تسجيل الدخول',
        'logout' => 'تسجيل الخروج',
        'download_material' => 'تحميل مادة',
        'view_report' => 'عرض تقرير',
    ];

    /**
     * العلاقات
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Scopes
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('event_type', $type);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function getEventTypeNameAttribute(): string
    {
        return self::EVENT_TYPES[$this->event_type] ?? $this->event_type;
    }
}

