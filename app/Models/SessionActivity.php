<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SessionActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_session_id',
        'activity_type',
        'activity_details',
        'page_url',
        'occurred_at',
    ];

    protected $casts = [
        'activity_details' => 'array',
        'occurred_at' => 'datetime',
    ];

    /**
     * العلاقة مع الجلسة
     */
    public function userSession()
    {
        return $this->belongsTo(UserSession::class, 'user_session_id');
    }

    /**
     * نطاق نوع النشاط
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * نطاق الأنشطة في فترة زمنية
     */
    public function scopeInTimeRange($query, $start, $end)
    {
        return $query->whereBetween('occurred_at', [$start, $end]);
    }

    /**
     * نطاق الأنشطة في جلسة معينة
     */
    public function scopeForSession($query, $sessionId)
    {
        return $query->where('user_session_id', $sessionId);
    }

    /**
     * نطاق الترتيب حسب الوقت
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('occurred_at', 'desc');
    }

    /**
     * نطاق الترتيب حسب الوقت (تصاعدي)
     */
    public function scopeOldest($query)
    {
        return $query->orderBy('occurred_at', 'asc');
    }

    /**
     * Accessor لاسم النشاط بالعربية
     */
    public function getActivityNameAttribute()
    {
        $names = [
            'session_start' => 'بداية الجلسة',
            'session_end' => 'نهاية الجلسة',
            'page_view' => 'عرض صفحة',
            'action' => 'إجراء',
            'disconnect' => 'انقطاع',
            'reconnect' => 'إعادة اتصال',
            'idle_start' => 'بداية الخمول',
            'idle_end' => 'نهاية الخمول',
            'focus_lost' => 'فقدان التركيز',
            'focus_gained' => 'استعادة التركيز',
        ];

        return $names[$this->activity_type] ?? $this->activity_type;
    }

    /**
     * Accessor لأيقونة النشاط
     */
    public function getActivityIconAttribute()
    {
        $icons = [
            'session_start' => 'bi-play-circle-fill text-success',
            'session_end' => 'bi-stop-circle-fill text-danger',
            'page_view' => 'bi-eye-fill text-primary',
            'action' => 'bi-cursor-fill text-info',
            'disconnect' => 'bi-wifi-off text-warning',
            'reconnect' => 'bi-wifi text-success',
            'idle_start' => 'bi-pause-circle-fill text-secondary',
            'idle_end' => 'bi-play-circle text-success',
            'focus_lost' => 'bi-x-circle-fill text-warning',
            'focus_gained' => 'bi-check-circle-fill text-success',
        ];

        return $icons[$this->activity_type] ?? 'bi-circle-fill';
    }

    /**
     * Accessor لـ badge النشاط
     */
    public function getActivityBadgeAttribute()
    {
        $badges = [
            'session_start' => '<span class="badge bg-success-transparent text-success">بداية الجلسة</span>',
            'session_end' => '<span class="badge bg-danger-transparent text-danger">نهاية الجلسة</span>',
            'page_view' => '<span class="badge bg-primary-transparent text-primary">عرض صفحة</span>',
            'action' => '<span class="badge bg-info-transparent text-info">إجراء</span>',
            'disconnect' => '<span class="badge bg-warning-transparent text-warning">انقطاع</span>',
            'reconnect' => '<span class="badge bg-success-transparent text-success">إعادة اتصال</span>',
            'idle_start' => '<span class="badge bg-secondary-transparent text-secondary">بداية الخمول</span>',
            'idle_end' => '<span class="badge bg-success-transparent text-success">نهاية الخمول</span>',
            'focus_lost' => '<span class="badge bg-warning-transparent text-warning">فقدان التركيز</span>',
            'focus_gained' => '<span class="badge bg-success-transparent text-success">استعادة التركيز</span>',
        ];

        return $badges[$this->activity_type] ?? '<span class="badge bg-secondary">غير معروف</span>';
    }
}

