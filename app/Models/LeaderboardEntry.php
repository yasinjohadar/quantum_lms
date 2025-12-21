<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaderboardEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'leaderboard_id',
        'user_id',
        'rank',
        'score',
        'metadata',
    ];

    protected $casts = [
        'rank' => 'integer',
        'score' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * العلاقات
     */
    public function leaderboard(): BelongsTo
    {
        return $this->belongsTo(Leaderboard::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopeForLeaderboard($query, $leaderboardId)
    {
        return $query->where('leaderboard_id', $leaderboardId);
    }

    public function scopeTop($query, $limit = 10)
    {
        return $query->orderBy('rank')->limit($limit);
    }
}

