<?php

namespace App\Jobs;

use App\Models\MediaFile;
use App\Models\StorageSyncBatch;
use App\Services\Storage\CloudFirstStorageRouter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * StorageSyncJob
 * 
 * وظيفة مزامنة الملفات من اللوكال إلى السحابة
 * تدعم: retries, exponential backoff, dead letter tracking
 */
class StorageSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries;
    public int $backoff;

    public function __construct(
        public string $filePath,
        public string $targetDisk,
        public ?int $batchId = null,
        public bool $deleteLocalAfterSuccess = false,
    ) {
        $this->tries = config('storage.sync_retries', 3);
        $this->backoff = config('storage.sync_backoff_seconds', 30);
    }

    /**
     * Exponential backoff
     */
    public function backoff(): array
    {
        $base = $this->backoff;
        return [
            $base,
            $base * 2,
            $base * 4,
            $base * 8,
            $base * 16,
        ];
    }

    public function handle(CloudFirstStorageRouter $router): void
    {
        $localDisk = Storage::disk(config('storage.fallback_disk', 'public'));
        
        if (!$localDisk->exists($this->filePath)) {
            Log::warning('StorageSyncJob: File not found, skipping', [
                'path' => $this->filePath,
                'attempt' => $this->attempts(),
            ]);
            $this->delete();
            return;
        }

        $startTime = microtime(true);
        $result = $router->syncToCloud($this->filePath, $this->targetDisk);
        $elapsed = round((microtime(true) - $startTime) * 1000);

        if ($result['success']) {
            Log::info('StorageSyncJob: Synced successfully', [
                'path' => $this->filePath,
                'disk' => $this->targetDisk,
                'time_ms' => $elapsed,
                'attempt' => $this->attempts(),
            ]);

            if ($this->deleteLocalAfterSuccess && $localDisk->exists($this->filePath)) {
                try {
                    $localDisk->delete($this->filePath);
                    Log::info('StorageSyncJob: Deleted local copy after cloud sync', [
                        'path' => $this->filePath,
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('StorageSyncJob: Failed to delete local file after sync', [
                        'path' => $this->filePath,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // تحديث MediaFile
            MediaFile::where('path', $this->filePath)
                ->whereNull('deleted_at')
                ->update([
                    'is_synced' => true,
                    'synced_at' => now(),
                    'storage_provider' => $this->getProviderName(),
                ]);

            if ($this->batchId) {
                StorageSyncBatch::incrementSuccess($this->batchId);
            }
        } else {
            Log::error('StorageSyncJob: Sync failed', [
                'path' => $this->filePath,
                'disk' => $this->targetDisk,
                'error' => $result['error'],
                'attempt' => $this->attempts(),
                'remaining' => $this->tries - $this->attempts(),
            ]);

            if ($this->batchId) {
                StorageSyncBatch::incrementFailure($this->batchId, $result['error']);
            }

            // إذا استنفذ المحاولات، أرسل إلى dead letter
            if ($this->attempts() >= $this->tries) {
                $this->moveToDeadLetter($result['error']);
                $this->fail(new \Exception("StorageSyncJob permanently failed: {$result['error']}"));
            }
        }
    }

    /**
     * نقل إلى dead letter queue
     */
    private function moveToDeadLetter(string $error): void
    {
        try {
            \Illuminate\Support\Facades\DB::table('storage_sync_dead_letters')->insert([
                'file_path' => $this->filePath,
                'target_disk' => $this->targetDisk,
                'batch_id' => $this->batchId,
                'error' => $error,
                'attempts' => $this->attempts(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record dead letter', [
                'path' => $this->filePath,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::critical('StorageSyncJob permanently failed', [
            'path' => $this->filePath,
            'disk' => $this->targetDisk,
            'error' => $exception->getMessage(),
        ]);

        if ($this->batchId) {
            StorageSyncBatch::incrementFailure($this->batchId, $exception->getMessage());
        }
    }

    private function getProviderName(): string
    {
        $mapping = \App\Models\StorageDiskMapping::where('disk_name', $this->targetDisk)
            ->where('is_active', true)
            ->with('primaryStorage')
            ->first();
        
        return $mapping?->primaryStorage?->driver ?? 'unknown';
    }
}
