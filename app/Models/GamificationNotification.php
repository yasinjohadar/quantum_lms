<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamificationNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'is_read',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'is_read' => 'boolean',
    ];

    /**
     * أنواع الإشعارات
     */
    public const TYPES = [
        'badge_earned' => 'كسب شارة',
        'achievement_unlocked' => 'فتح إنجاز',
        'level_up' => 'ترقية مستوى',
        'challenge_completed' => 'إكمال تحدٍ',
        'reward_claimed' => 'استبدال مكافأة',
        'leaderboard_update' => 'تحديث لوحة المتصدرين',
        'challenge_reminder' => 'تذكير بتحدي',
        'custom_notification' => 'إشعار مخصص',
    ];

    /**
     * العلاقات
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Methods
     */
    public function markAsRead(): void
    {
        $this->is_read = true;
        $this->read_at = now();
        $this->save();
    }

    /**
     * Accessors
     */
    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}

