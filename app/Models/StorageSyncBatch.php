<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class StorageSyncBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'disk_name',
        'total_files',
        'processed_files',
        'successful_files',
        'failed_files',
        'status',
        'errors',
        'started_at',
        'completed_at',
        'started_by',
    ];

    protected $casts = [
        'total_files' => 'integer',
        'processed_files' => 'integer',
        'successful_files' => 'integer',
        'failed_files' => 'integer',
        'errors' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    public function starter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->total_files === 0) return 0;
        return round(($this->processed_files / $this->total_files) * 100, 1);
    }

    public function getIsCompleteAttribute(): bool
    {
        return $this->processed_files >= $this->total_files;
    }

    public static function createBatch(string $name, string $diskName, int $totalFiles, ?int $startedBy = null): self
    {
        return self::create([
            'name' => $name,
            'disk_name' => $diskName,
            'total_files' => $totalFiles,
            'processed_files' => 0,
            'successful_files' => 0,
            'failed_files' => 0,
            'status' => self::STATUS_RUNNING,
            'errors' => [],
            'started_at' => now(),
            'started_by' => $startedBy,
        ]);
    }

    public static function incrementSuccess(int $batchId): void
    {
        self::where('id', $batchId)->update([
            'processed_files' => \DB::raw('processed_files + 1'),
            'successful_files' => \DB::raw('successful_files + 1'),
        ]);
        
        self::checkCompletion($batchId);
    }

    public static function incrementFailure(int $batchId, string $error): void
    {
        $batch = self::find($batchId);
        if (!$batch) return;

        $batch->update([
            'processed_files' => $batch->processed_files + 1,
            'failed_files' => $batch->failed_files + 1,
        ]);
        
        $errors = $batch->errors ?? [];
        $errors[] = ['error' => $error, 'time' => now()->toIso8601String()];
        if (count($errors) > 100) {
            $errors = array_slice($errors, -100);
        }
        $batch->update(['errors' => $errors]);

        self::checkCompletion($batchId);
    }

    private static function checkCompletion(int $batchId): void
    {
        $batch = self::find($batchId);
        if (!$batch || !$batch->is_complete) return;

        $batch->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    public function markCancelled(): void
    {
        $this->status = self::STATUS_CANCELLED;
        $this->completed_at = now();
        $this->save();
    }
}
