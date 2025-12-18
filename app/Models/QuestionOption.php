<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'content',
        'image',
        'is_correct',
        'points',
        'match_target',
        'correct_order',
        'order',
        'feedback',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'points' => 'decimal:2',
        'correct_order' => 'integer',
        'order' => 'integer',
    ];

    /**
     * العلاقات
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}

