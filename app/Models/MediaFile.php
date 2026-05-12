<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'path',
        'original_name',
        'mime_type',
        'size',
        'disk',
        'storage_provider',
        'visibility',
        'category',
        'checksum',
        'url',
        'uploaded_by',
        'is_synced',
        'synced_at',
        'upload_time_ms',
        'metadata',
    ];

    protected $casts = [
        'size' => 'integer',
        'is_synced' => 'boolean',
        'synced_at' => 'datetime',
        'upload_time_ms' => 'integer',
        'metadata' => 'array',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getSizeFormattedAttribute(): string
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

    public function getUrlAttribute(): string
    {
        if ($this->visibility === 'private') {
            return \App\Services\Storage\MediaStorageService::temporaryUrl(
                $this->path,
                now()->addHours(1),
                $this->disk
            ) ?? '';
        }

        return \App\Services\Storage\MediaStorageService::url($this->path, $this->disk);
    }

    public function markSynced(): void
    {
        $this->update([
            'is_synced' => true,
            'synced_at' => now(),
        ]);
    }

    public static function findByChecksum(string $checksum): ?self
    {
        return self::where('checksum', $checksum)
            ->whereNull('deleted_at')
            ->first();
    }

    public static function getTotalSizeForUser(int $userId): int
    {
        return self::where('uploaded_by', $userId)
            ->whereNull('deleted_at')
            ->sum('size');
    }

    public static function getStatsByCategory(): array
    {
        return self::whereNull('deleted_at')
            ->selectRaw('category, count(*) as count, sum(size) as total_size')
            ->groupBy('category')
            ->get()
            ->mapWithKeys(function ($row) {
                return [$row->category => [
                    'count' => $row->count,
                    'total_size' => $row->total_size,
                    'total_size_formatted' => self::formatBytes($row->total_size),
                ]];
            })
            ->toArray();
    }

    private static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 1) . ' ' . $units[$i];
    }
}
