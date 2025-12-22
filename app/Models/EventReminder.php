<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EventReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'event_id',
        'user_id',
        'reminder_type',
        'reminder_times',
        'custom_minutes',
        'is_sent',
        'sent_at',
    ];

    protected $casts = [
        'reminder_times' => 'array',
        'custom_minutes' => 'integer',
        'is_sent' => 'boolean',
        'sent_at' => 'datetime',
    ];

    /**
     * أنواع الأحداث
     */
    public const EVENT_TYPES = [
        'calendar_event' => 'حدث تقويم',
        'quiz' => 'اختبار',
        'assignment' => 'واجب',
    ];

    /**
     * العلاقة مع المستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * الحصول على الحدث (Polymorphic)
     */
    public function getEvent()
    {
        return match($this->event_type) {
            'calendar_event' => CalendarEvent::find($this->event_id),
            'quiz' => Quiz::find($this->event_id),
            'assignment' => Assignment::find($this->event_id),
            default => null,
        };
    }

    /**
     * نطاق التذكيرات المعلقة
     */
    public function scopePending($query)
    {
        return $query->where('is_sent', false);
    }

    /**
     * نطاق التذكيرات غير المرسلة
     */
    public function scopeUnsent($query)
    {
        return $query->where('is_sent', false);
    }

    /**
     * نطاق التذكيرات المستحقة
     */
    public function scopeDue($query)
    {
        return $query->where('is_sent', false)
                    ->where(function($q) {
                        $q->whereHas('event', function($eventQuery) {
                            // سيتم التحقق من الوقت في Service
                        });
                    });
    }

    /**
     * التحقق من أن التذكير يجب إرساله
     */
    public function shouldSend(): bool
    {
        if ($this->is_sent) {
            return false;
        }

        $eventDate = $this->getEventDate();
        if (!$eventDate) {
            return false;
        }

        if ($this->reminder_type === 'single') {
            if ($this->custom_minutes) {
                $reminderTime = $eventDate->copy()->subMinutes($this->custom_minutes);
                return now()->gte($reminderTime) && now()->lt($eventDate);
            }
        } elseif ($this->reminder_type === 'multiple') {
            if ($this->reminder_times && is_array($this->reminder_times)) {
                foreach ($this->reminder_times as $hoursBefore) {
                    $reminderTime = $eventDate->copy()->subHours($hoursBefore);
                    // التحقق من أن الوقت الحالي بين وقت التذكير والحدث
                    if (now()->gte($reminderTime) && now()->lt($eventDate)) {
                        // التحقق من أن هذا التذكير لم يُرسل بعد
                        $sentReminders = $this->getSentRemindersForTime($hoursBefore);
                        if (!$sentReminders->contains($hoursBefore)) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * تحديد التذكير كمرسل
     */
    public function markAsSent(): void
    {
        $this->update([
            'is_sent' => true,
            'sent_at' => now(),
        ]);
    }

}
