<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class CalendarEvent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'event_type',
        'start_date',
        'end_date',
        'is_all_day',
        'location',
        'color',
        'created_by',
        'subject_id',
        'class_id',
        'is_public',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_all_day' => 'boolean',
        'is_public' => 'boolean',
    ];

    /**
     * أنواع الأحداث
     */
    public const EVENT_TYPES = [
        'general' => 'عام',
        'meeting' => 'اجتماع',
        'holiday' => 'عطلة',
        'exam' => 'امتحان',
        'other' => 'أخرى',
    ];

    /**
     * العلاقة مع منشئ الحدث
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * العلاقة مع المادة
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * العلاقة مع الصف
     */
    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * العلاقة مع التذكيرات
     */
    public function reminders()
    {
        return $this->hasMany(EventReminder::class, 'event_id')
                    ->where('event_type', 'calendar_event');
    }

    /**
     * نطاق الأحداث القادمة
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now());
    }

    /**
     * نطاق الأحداث حسب النطاق الزمني
     */
    public function scopeByDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->where(function($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function($q2) use ($startDate, $endDate) {
                  $q2->where('start_date', '<=', $startDate)
                     ->where('end_date', '>=', $endDate);
              });
        });
    }

    /**
     * نطاق الأحداث العامة
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * نطاق الأحداث لمادة معينة
     */
    public function scopeForSubject($query, int $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * نطاق الأحداث لصف معين
     */
    public function scopeForClass($query, int $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * التحقق من أن الحدث طوال اليوم
     */
    public function isAllDay(): bool
    {
        return $this->is_all_day;
    }

    /**
     * الحصول على مدة الحدث
     */
    public function getDuration(): ?int
    {
        if (!$this->end_date) {
            return null;
        }

        return $this->start_date->diffInMinutes($this->end_date);
    }

    /**
     * الحصول على لون الحدث
     */
    public function getColor(): string
    {
        if ($this->color) {
            return $this->color;
        }

        // ألوان افتراضية حسب النوع
        return match($this->event_type) {
            'meeting' => '#3b82f6',
            'holiday' => '#ef4444',
            'exam' => '#f59e0b',
            'other' => '#6b7280',
            default => '#10b981',
        };
    }

    /**
     * التحقق من أن الحدث قادم
     */
    public function isUpcoming(): bool
    {
        return $this->start_date->isFuture();
    }

    /**
     * التحقق من أن الحدث جاري
     */
    public function isOngoing(): bool
    {
        $now = now();
        return $this->start_date->lte($now) && 
               ($this->end_date === null || $this->end_date->gte($now));
    }

    /**
     * التحقق من أن الحدث انتهى
     */
    public function isPast(): bool
    {
        if ($this->end_date) {
            return $this->end_date->isPast();
        }
        return $this->start_date->isPast();
    }
}
