<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'template_id',
        'frequency', // daily, weekly, monthly
        'recipients', // JSON - قائمة المستلمين
        'last_run_at',
        'next_run_at',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'recipients' => 'array',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'is_active' => 'boolean',
        'created_by' => 'integer',
    ];

    /**
     * ترددات الجدولة
     */
    public const FREQUENCIES = [
        'daily' => 'يومي',
        'weekly' => 'أسبوعي',
        'monthly' => 'شهري',
    ];

    /**
     * العلاقة مع القالب
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ReportTemplate::class, 'template_id');
    }

    /**
     * العلاقة مع منشئ الجدولة
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDue($query)
    {
        return $query->where('next_run_at', '<=', now())
                    ->where('is_active', true);
    }

    public function getFrequencyNameAttribute(): string
    {
        return self::FREQUENCIES[$this->frequency] ?? $this->frequency;
    }
}

