<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonCompletion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'lesson_id',
        'status',
        'marked_at',
        'time_spent',
        'notes',
    ];

    protected $casts = [
        'marked_at' => 'datetime',
        'time_spent' => 'integer',
    ];

    /**
     * حالات الدرس
     */
    public const STATUSES = [
        'attended' => 'تم الحضور',
        'completed' => 'تم الإكمال',
    ];

    /**
     * العلاقة مع المستخدم
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * العلاقة مع الدرس
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Scopes
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForLesson($query, $lessonId)
    {
        return $query->where('lesson_id', $lessonId);
    }

    public function scopeAttended($query)
    {
        return $query->where('status', 'attended');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Accessors
     */
    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}

