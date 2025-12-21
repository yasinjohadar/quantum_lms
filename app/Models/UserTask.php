<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'taskable_type',
        'taskable_id',
        'status',
        'progress',
        'completed_at',
        'claimed_at',
    ];

    protected $casts = [
        'progress' => 'integer',
        'completed_at' => 'datetime',
        'claimed_at' => 'datetime',
    ];

    /**
     * حالات المهمة
     */
    public const STATUSES = [
        'pending' => 'قيد الانتظار',
        'completed' => 'مكتملة',
        'expired' => 'منتهية',
    ];

    /**
     * العلاقات
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function taskable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Accessors
     */
    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }
}
