<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reward_id',
        'claimed_at',
        'status',
        'metadata',
    ];

    protected $casts = [
        'claimed_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * حالات المكافأة
     */
    public const STATUSES = [
        'pending' => 'قيد الانتظار',
        'approved' => 'موافق عليه',
        'rejected' => 'مرفوض',
        'delivered' => 'تم التسليم',
    ];

    /**
     * العلاقات
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
    }

    /**
     * Scopes
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Accessors
     */
    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}

