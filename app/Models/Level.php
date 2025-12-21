<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'level_number',
        'points_required',
        'icon',
        'color',
        'benefits',
        'order',
    ];

    protected $casts = [
        'level_number' => 'integer',
        'points_required' => 'integer',
        'benefits' => 'array',
        'order' => 'integer',
    ];

    /**
     * العلاقات
     */
    public function users(): HasMany
    {
        return $this->hasMany(UserLevel::class);
    }

    /**
     * Scopes
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('level_number');
    }

    public function scopeByLevelNumber($query, $levelNumber)
    {
        return $query->where('level_number', $levelNumber);
    }
}

