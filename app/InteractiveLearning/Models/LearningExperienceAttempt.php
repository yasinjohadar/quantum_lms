<?php

namespace App\InteractiveLearning\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningExperienceAttempt extends Model
{
    protected $fillable = [
        'learning_experience_id',
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

    public function experience(): BelongsTo
    {
        return $this->belongsTo(LearningExperience::class, 'learning_experience_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
