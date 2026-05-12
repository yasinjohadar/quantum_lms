<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'media_id',
        'name',
        'disk',
        'path',
        'mime_type',
        'size',
        'conversion_params',
        'is_generated',
        'generated_at',
    ];

    protected $casts = [
        'size' => 'integer',
        'conversion_params' => 'array',
        'is_generated' => 'boolean',
        'generated_at' => 'datetime',
    ];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function markGenerated(): void
    {
        $this->update([
            'is_generated' => true,
            'generated_at' => now(),
        ]);
    }

    public function sizeFormatted(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 1) . ' ' . $units[$i];
    }
}
