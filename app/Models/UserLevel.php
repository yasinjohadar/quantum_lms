<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'level_id',
        'current_points',
        'total_points_earned',
        'reached_at',
    ];

    protected $casts = [
        'current_points' => 'integer',
        'total_points_earned' => 'integer',
        'reached_at' => 'datetime',
    ];

    /**
     * العلاقات
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }
}

