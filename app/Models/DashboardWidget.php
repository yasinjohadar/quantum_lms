<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardWidget extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type', // stats, chart, list, calendar, notification
        'position', // JSON - {row, col, width, height}
        'config', // JSON - إعدادات الودجت
        'user_id', // null للودجت العامة، أو user_id للمخصص
        'is_active',
    ];

    protected $casts = [
        'position' => 'array',
        'config' => 'array',
        'user_id' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * أنواع الودجت
     */
    public const TYPES = [
        'stats' => 'إحصائيات',
        'chart' => 'رسم بياني',
        'list' => 'قائمة',
        'calendar' => 'تقويم',
        'notification' => 'إشعارات',
    ];

    /**
     * العلاقة مع المستخدم (للويدجت المخصصة)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeGlobal($query)
    {
        return $query->whereNull('user_id');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->whereNull('user_id')
              ->orWhere('user_id', $userId);
        });
    }

    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}

