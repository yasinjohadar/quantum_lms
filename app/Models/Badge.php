<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'color',
        'points_required',
        'criteria',
        'is_active',
        'is_automatic',
        'order',
    ];

    protected $casts = [
        'points_required' => 'integer',
        'criteria' => 'array',
        'is_active' => 'boolean',
        'is_automatic' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * العلاقات
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
                    ->withPivot('earned_at', 'metadata')
                    ->withTimestamps();
    }

    public function userBadges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAutomatic($query)
    {
        return $query->where('is_automatic', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}

