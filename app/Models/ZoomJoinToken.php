<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ZoomJoinToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'live_session_id',
        'token_hash',
        'expires_at',
        'used_at',
        'use_count',
        'max_uses',
        'user_agent_hash',
        'ip_prefix',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'use_count' => 'integer',
        'max_uses' => 'integer',
    ];

    /**
     * Relation to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation to Live Session
     */
    public function liveSession()
    {
        return $this->belongsTo(LiveSession::class);
    }

    /**
     * Scopes
     */
    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now())
            ->where(function ($q) {
                $q->whereNull('used_at')
                  ->orWhereColumn('use_count', '<', 'max_uses');
            });
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeUnused($query)
    {
        return $query->whereNull('used_at');
    }

    /**
     * Check if token is valid
     */
    public function isValid(): bool
    {
        if ($this->expires_at->isPast()) {
            return false;
        }

        if ($this->use_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * Check if token can be used
     */
    public function canUse(): bool
    {
        return $this->isValid();
    }

    /**
     * Mark token as used
     */
    public function markAsUsed(): self
    {
        $this->update([
            'used_at' => $this->used_at ?? now(),
            'use_count' => $this->use_count + 1,
        ]);

        return $this;
    }

    /**
     * Increment use count
     */
    public function incrementUseCount(): self
    {
        $this->increment('use_count');
        return $this;
    }
}
