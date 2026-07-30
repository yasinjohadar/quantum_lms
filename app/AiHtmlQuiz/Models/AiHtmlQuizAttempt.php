<?php

namespace App\AiHtmlQuiz\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiHtmlQuizAttempt extends Model
{
    protected $table = 'ai_html_quiz_attempts';

    protected $fillable = [
        'ai_html_quiz_id',
        'user_id',
        'score',
        'total',
        'percentage',
        'duration',
        'started_at',
        'finished_at',
        'answers_json',
        'result_json',
    ];

    protected function casts(): array
    {
        return [
            'answers_json' => 'array',
            'result_json' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'percentage' => 'float',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(AiHtmlQuiz::class, 'ai_html_quiz_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
