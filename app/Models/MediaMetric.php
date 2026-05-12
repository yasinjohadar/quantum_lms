<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'metric_type',
        'metric_name',
        'value',
        'unit',
        'provider',
        'data',
        'recorded_at',
    ];

    protected $casts = [
        'value' => 'float',
        'data' => 'array',
        'recorded_at' => 'datetime',
    ];
}
