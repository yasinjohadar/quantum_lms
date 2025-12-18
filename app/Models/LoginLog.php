<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LoginLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'browser_version',
        'platform',
        'platform_version',
        'country',
        'city',
        'is_successful',
        'failure_reason',
        'login_at',
        'logout_at',
        'session_duration_seconds',
        'session_id',
        'meta',
    ];

    protected $casts = [
        'is_successful' => 'boolean',
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
        'meta' => 'array',
        'session_duration_seconds' => 'integer',
    ];

    /**
     * العلاقة مع المستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * نطاق محاولات الدخول الناجحة
     */
    public function scopeSuccessful($query)
    {
        return $query->where('is_successful', true);
    }

    /**
     * نطاق محاولات الدخول الفاشلة
     */
    public function scopeFailed($query)
    {
        return $query->where('is_successful', false);
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
            ->orWhere('user_agent', 'like', '%' . $search . '%')
            ->orWhere('country', 'like', '%' . $search . '%')
            ->orWhere('city', 'like', '%' . $search . '%');
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
     * نطاق الفلترة حسب التاريخ
     */
    public function scopeDateRange($query, $startDate, $endDate = null)
    {
        $query->whereDate('login_at', '>=', $startDate);
        
        if ($endDate) {
            $query->whereDate('login_at', '<=', $endDate);
        }
        
        return $query;
    }

    /**
     * Accessor لمدة الجلسة بصيغة مقروءة
     */
    public function getSessionDurationAttribute()
    {
        if (!$this->session_duration_seconds) {
            return null;
        }

        $hours = floor($this->session_duration_seconds / 3600);
        $minutes = floor(($this->session_duration_seconds % 3600) / 60);
        $seconds = $this->session_duration_seconds % 60;

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
        return $this->is_successful && $this->logout_at === null;
    }
}
