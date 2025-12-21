<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DailyTask extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'type',
        'points_reward',
        'criteria',
        'is_active',
        'order',
    ];

    protected $casts = [
        'points_reward' => 'integer',
        'criteria' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * أنواع المهام
     */
    public const TYPES = [
        'attendance' => 'حضور',
        'lesson_completion' => 'إكمال درس',
        'quiz' => 'اختبار',
        'question' => 'سؤال',
    ];

    /**
     * العلاقات
     */
    public function userTasks(): MorphMany
    {
        return $this->morphMany(UserTask::class, 'taskable');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
