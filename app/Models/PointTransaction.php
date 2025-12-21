<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PointTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'points',
        'source_type',
        'source_id',
        'metadata',
    ];

    protected $casts = [
        'points' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * أنواع النقاط
     */
    public const TYPES = [
        'attendance' => 'حضور',
        'lesson_completion' => 'إكمال درس',
        'quiz' => 'اختبار',
        'question' => 'سؤال',
        'achievement' => 'إنجاز',
        'challenge' => 'تحدي',
        'reward' => 'مكافأة',
        'manual' => 'يدوي',
    ];

    /**
     * العلاقات
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scopes
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Accessors
     */
    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}

