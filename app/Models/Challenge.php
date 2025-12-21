<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Challenge extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'type',
        'start_date',
        'end_date',
        'criteria',
        'rewards',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'criteria' => 'array',
        'rewards' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * أنواع التحديات
     */
    public const TYPES = [
        'weekly' => 'أسبوعي',
        'monthly' => 'شهري',
        'custom' => 'مخصص',
    ];

    /**
     * العلاقات
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_challenges')
                    ->withPivot('progress', 'completed_at', 'reward_claimed')
                    ->withTimestamps();
    }

    public function userChallenges(): HasMany
    {
        return $this->hasMany(UserChallenge::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeCurrent($query)
    {
        return $query->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
    }

    /**
     * Accessors
     */
    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->is_active && 
               $this->start_date <= now() && 
               $this->end_date >= now();
    }
}

