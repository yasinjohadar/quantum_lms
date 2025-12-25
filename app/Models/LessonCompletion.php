<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lesson_id',
        'status',
        'marked_at',
    ];

    protected $casts = [
        'marked_at' => 'datetime',
    ];

    /**
     * حالات الإكمال المتاحة
     */
    const STATUSES = [
        'attended' => 'حضور',
        'completed' => 'مكتمل',
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
     * نطاق الدروس المكتملة
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * نطاق الدروس التي تم حضورها
     */
    public function scopeAttended($query)
    {
        return $query->where('status', 'attended');
    }

    /**
     * نطاق الفلترة حسب المستخدم
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * نطاق الفلترة حسب الدرس
     */
    public function scopeForLesson($query, $lessonId)
    {
        return $query->where('lesson_id', $lessonId);
    }
}

