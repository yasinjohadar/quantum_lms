<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_uuid',
        'session_name',
        'session_description',
        'started_at',
        'ended_at',
        'duration_seconds',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'browser_version',
        'platform',
        'platform_version',
        'screen_resolution',
        'connection_type',
        'bandwidth_mbps',
        'status',
        'notes',
        'meta',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_seconds' => 'integer',
        'bandwidth_mbps' => 'decimal:2',
        'meta' => 'array',
    ];

    /**
     * العلاقة مع المستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * العلاقة مع أنشطة الجلسة
     */
    public function activities()
    {
        return $this->hasMany(SessionActivity::class, 'user_session_id');
    }

    /**
     * نطاق الجلسات النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * نطاق الجلسات المكتملة
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * نطاق الجلسات المنفصلة
     */
    public function scopeDisconnected($query)
    {
        return $query->where('status', 'disconnected');
    }

    /**
     * نطاق الجلسات المنتهية بالوقت
     */
    public function scopeTimeout($query)
    {
        return $query->where('status', 'timeout');
    }

    /**
     * نطاق البحث
     */
    public function scopeSearch($query, $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->whereHas('user', function ($userQuery) use ($search) {
                $userQuery->where('name', 'like', '%' . $search . '%')
                          ->orWhere('email', 'like', '%' . $search . '%');
            })
            ->orWhere('ip_address', 'like', '%' . $search . '%')
            ->orWhere('session_name', 'like', '%' . $search . '%')
            ->orWhere('session_uuid', 'like', '%' . $search . '%')
            ->orWhere('user_agent', 'like', '%' . $search . '%');
        });
    }

    /**
     * نطاق الفلترة حسب المستخدم
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * نطاق الفلترة حسب IP
     */
    public function scopeForIp($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * نطاق الفلترة حسب الحالة
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * نطاق الفلترة حسب التاريخ
     */
    public function scopeDateRange($query, $startDate, $endDate = null)
    {
        $query->whereDate('started_at', '>=', $startDate);
        
        if ($endDate) {
            $query->whereDate('started_at', '<=', $endDate);
        }
        
        return $query;
    }

    /**
     * نطاق الترتيب حسب تاريخ البدء
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('started_at', 'desc');
    }

    /**
     * Accessor لمدة الجلسة بصيغة مقروءة
     */
    public function getDurationAttribute()
    {
        if (!$this->duration_seconds) {
            return null;
        }

        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Accessor لمعرفة ما إذا كانت الجلسة لا تزال نشطة
     */
    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    /**
     * Accessor لحالة الجلسة بصيغة badge
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'active' => '<span class="badge bg-success-transparent text-success">نشطة</span>',
            'completed' => '<span class="badge bg-primary-transparent text-primary">مكتملة</span>',
            'disconnected' => '<span class="badge bg-warning-transparent text-warning">منفصلة</span>',
            'timeout' => '<span class="badge bg-danger-transparent text-danger">منتهية</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">غير معروف</span>';
    }

    /**
     * حساب مدة الجلسة تلقائياً
     */
    public function calculateDuration()
    {
        if ($this->started_at && $this->ended_at) {
            $this->duration_seconds = $this->ended_at->diffInSeconds($this->started_at);
            $this->save();
        }
    }
}

