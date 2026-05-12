<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

class Media extends Model
{
    use HasFactory;

    protected $table = 'media';

    protected $fillable = [
        'uuid',
        'disk',
        'path',
        'provider',
        'visibility',
        'mime_type',
        'extension',
        'size',
        'checksum',
        'uploaded_by',
        'storage_region',
        'is_synced',
        'sync_status',
        'reference_count',
        'metadata',
        'deleted_at',
        'restored_at',
    ];

    protected $casts = [
        'size' => 'integer',
        'is_synced' => 'boolean',
        'reference_count' => 'integer',
        'metadata' => 'array',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Media $media) {
            if (empty($media->uuid)) {
                $media->uuid = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(MediaVariant::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(MediaUsage::class);
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(MediaConversion::class);
    }

    public function usedBy(): MorphToMany
    {
        return $this->morphedByMany(Model::class, 'model', 'media_usages')
            ->withPivot('collection', 'field', 'usage_context')
            ->withTimestamps();
    }

    // Scopes
    public function scopeSynced($query)
    {
        return $query->where('is_synced', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_synced', false);
    }

    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    public function scopePrivate($query)
    {
        return $query->where('visibility', 'private');
    }

    public function scopeOrphaned($query)
    {
        return $query->where('reference_count', 0);
    }

    public function scopeDeleted($query)
    {
        return $query->whereNotNull('deleted_at');
    }

    // Helpers
    public function incrementReference(): void
    {
        $this->increment('reference_count');
    }

    public function decrementReference(): void
    {
        if ($this->reference_count > 0) {
            $this->decrement('reference_count');
        }
    }

    public function markSynced(): void
    {
        $this->update([
            'is_synced' => true,
            'sync_status' => 'completed',
        ]);
    }

    public function markFailed(string $error): void
    {
        $this->update([
            'sync_status' => 'failed',
            'metadata' => array_merge($this->metadata ?? [], ['last_error' => $error]),
        ]);
    }

    public function softDelete(): void
    {
        $this->update(['deleted_at' => now()]);
    }

    public function restore(): void
    {
        $this->update([
            'deleted_at' => null,
            'restored_at' => now(),
        ]);
    }

    public function isOrphaned(): bool
    {
        return $this->reference_count <= 0;
    }

    public function isDeleted(): bool
    {
        return $this->deleted_at !== null;
    }

    public function sizeFormatted(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 1) . ' ' . $units[$i];
    }

    public function getVariant(string $name): ?MediaVariant
    {
        return $this->variants()->where('name', $name)->first();
    }

    public function hasVariant(string $name): bool
    {
        return $this->variants()->where('name', $name)->where('is_generated', true)->exists();
    }
}
