<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Achievement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'type',
        'criteria',
        'points_reward',
        'badge_id',
        'is_active',
        'order',
    ];

    protected $casts = [
        'criteria' => 'array',
        'points_reward' => 'integer',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * أنواع الإنجازات
     */
    public const TYPES = [
        'attendance' => 'حضور',
        'quiz' => 'اختبار',
        'course' => 'كورس',
        'special' => 'خاص',
        'streak' => 'سلسلة',
    ];

    /**
     * العلاقات
     */
    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_achievements')
                    ->withPivot('progress', 'completed_at', 'metadata')
                    ->withTimestamps();
    }

    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
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

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Accessors
     */
    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}

