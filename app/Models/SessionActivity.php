<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * نطاق الفلترة حسب نوع النشاط
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * نطاق الفلترة حسب النطاق الزمني
     */
    public function scopeInTimeRange($query, $startDate, $endDate = null)
    {
        $query->where('occurred_at', '>=', $startDate);
        
        if ($endDate) {
            $query->where('occurred_at', '<=', $endDate);
        }
        
        return $query;
    }
}


