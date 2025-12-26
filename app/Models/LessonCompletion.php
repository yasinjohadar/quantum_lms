<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LessonCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lesson_id',
        'status',
        'progress_percentage',
        'time_spent',
        'last_position',
        'marked_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'lesson_id' => 'integer',
        'progress_percentage' => 'decimal:2',
        'time_spent' => 'integer',
        'last_position' => 'integer',
        'marked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * حالات الإكمال
     */
    public const STATUSES = [
        'attended' => 'حضر',
        'completed' => 'أكمل',
    ];

    /**
     * العلاقة مع المستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * العلاقة مع الدرس
     */
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Scopes
     */
    public function scopeAttended($query)
    {
        return $query->where('status', 'attended');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForLesson($query, $lessonId)
    {
        return $query->where('lesson_id', $lessonId);
    }
}

