<?php

namespace App\InteractiveLearning\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LearningExperienceAttachable extends Model
{
    protected $fillable = [
        'learning_experience_id',
        'attachable_type',
        'attachable_id',
        'order',
    ];

    public function experience(): BelongsTo
    {
        return $this->belongsTo(LearningExperience::class, 'learning_experience_id');
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
