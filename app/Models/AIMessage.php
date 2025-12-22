<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIMessage extends Model
{
    use HasFactory;

    protected $table = 'ai_messages';

    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'tokens_used',
        'cost',
        'response_time',
        'metadata',
    ];

    protected $casts = [
        'tokens_used' => 'integer',
        'cost' => 'decimal:6',
        'response_time' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * الأدوار
     */
    public const ROLES = [
        'user' => 'المستخدم',
        'assistant' => 'المساعد',
        'system' => 'النظام',
    ];

    /**
     * العلاقة مع المحادثة
     */
    public function conversation()
    {
        return $this->belongsTo(AIConversation::class, 'conversation_id');
    }

    /**
     * الحصول على المحتوى المنسق
     */
    public function getFormattedContent(): string
    {
        return $this->content;
    }

    /**
     * الحصول على التكلفة
     */
    public function getCost(): float
    {
        return $this->cost ?? 0;
    }
}
