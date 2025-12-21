<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Reward extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'type',
        'points_cost',
        'quantity_available',
        'quantity_claimed',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'points_cost' => 'integer',
        'quantity_available' => 'integer',
        'quantity_claimed' => 'integer',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * أنواع المكافآت
     */
    public const TYPES = [
        'certificate' => 'شهادة',
        'discount' => 'خصم',
        'badge' => 'شارة',
        'points' => 'نقاط',
        'access' => 'وصول',
    ];

    /**
     * العلاقات
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_rewards')
                    ->withPivot('claimed_at', 'status', 'metadata')
                    ->withTimestamps();
    }

    public function userRewards(): HasMany
    {
        return $this->hasMany(UserReward::class);
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

    public function scopeAvailable($query)
    {
        return $query->where(function($q) {
            $q->whereNull('quantity_available')
              ->orWhereRaw('quantity_available > quantity_claimed');
        });
    }

    /**
     * Accessors
     */
    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->is_active && 
               ($this->quantity_available === null || 
                $this->quantity_available > $this->quantity_claimed);
    }
}

