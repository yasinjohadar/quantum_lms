<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'summarizable_type',
        'summarizable_id',
        'summary_text',
        'summary_type',
        'ai_model_id',
        'tokens_used',
        'cost',
        'created_by',
    ];

    protected $casts = [
        'tokens_used' => 'integer',
        'cost' => 'decimal:6',
    ];

    /**
     * العلاقة المتعددة الأشكال
     */
    public function summarizable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * العلاقة مع AI Model
     */
    public function aiModel(): BelongsTo
    {
        return $this->belongsTo(AIModel::class, 'ai_model_id');
    }

    /**
     * العلاقة مع المستخدم
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * أنواع التلخيص
     */
    public const SUMMARY_TYPES = [
        'short' => 'تلخيص قصير',
        'long' => 'تلخيص طويل',
        'bullet_points' => 'نقاط رئيسية',
    ];
}



