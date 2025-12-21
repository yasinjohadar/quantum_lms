<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leaderboard extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'subject_id',
        'period_start',
        'period_end',
        'criteria',
        'is_active',
    ];

    protected $casts = [
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'criteria' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * أنواع اللوحات
     */
    public const TYPES = [
        'global' => 'عامة',
        'course' => 'كورس',
        'weekly' => 'أسبوعية',
        'monthly' => 'شهرية',
    ];

    /**
     * العلاقات
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(LeaderboardEntry::class);
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
        return $query->where(function($q) {
            $q->whereNull('period_start')
              ->orWhere('period_start', '<=', now());
        })->where(function($q) {
            $q->whereNull('period_end')
              ->orWhere('period_end', '>=', now());
        });
    }

    /**
     * Accessors
     */
    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}

