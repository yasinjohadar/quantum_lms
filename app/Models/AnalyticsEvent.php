<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * العلاقة مع المستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * العلاقة مع المادة
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * العلاقة مع الدرس
     */
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * العلاقة مع الاختبار
     */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * العلاقة مع السؤال
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Scopes
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeForEventType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeInPeriod($query, $start, $end = null)
    {
        $query->where('created_at', '>=', $start);
        
        if ($end) {
            $query->where('created_at', '<=', $end);
        }
        
        return $query;
    }
}

