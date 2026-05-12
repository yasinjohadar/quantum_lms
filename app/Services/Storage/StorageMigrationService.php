<?php

namespace App\Services\Storage;

use App\Jobs\StorageSyncJob;
use App\Models\StorageSyncBatch;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * StorageMigrationService
 * 
 * خدمة ترحيل الملفات من التخزين المحلي إلى السحابة
 * تدعم الترحيل الدفعي (batch) مع تتبع التقدم
 */
class StorageMigrationService
{
    /**
     * المسارات المعروفة
     */
    private const KNOWN_PATHS = [
        'users/photos' => 'avatars',
        'quizzes' => 'images',
        'questions/images' => 'images',
        'question_options' => 'images',
        'subjects/images' => 'images',
        'subjects/og_images' => 'images',
        'classes/images' => 'images',
        'classes/og_images' => 'images',
        'lessons/videos' => 'videos',
        'lessons/thumbnails' => 'images',
        'lessons/attachments' => 'attachments',
        'library/items' => 'library',
        'library/thumbnails' => 'library',
        'hero-slides' => 'images',
    ];

    /**
     * تحليل الملفات المحلية التي تحتاج ترحيل
     */
    public function analyzeLocalFiles(?string $specificDisk = null): array
    {
        $analysis = [];
        $localDisk = Storage::disk('public');
        $totalSize = 0;
        $totalFiles = 0;

        foreach (self::KNOWN_PATHS as $prefix => $diskName) {
            if ($specificDisk && $diskName !== $specificDisk) {
                continue;
            }

            // التحقق من وجود mapping سحابي
            $hasCloudStorage = \App\Models\StorageDiskMapping::where('disk_name', $diskName)
                ->where('is_active', true)
                ->exists();

            if (!$hasCloudStorage) {
                continue;
            }

            $files = [];
            $pathSize = 0;
            $pathCount = 0;

            try {
                $allFiles = $localDisk->allFiles($prefix);
                
                foreach ($allFiles as $file) {
                    $size = $localDisk->size($file);
                    $files[] = [
                        'path' => $file,
                        'size' => $size,
                        'size_formatted' => $this->formatBytes($size),
                        'last_modified' => $localDisk->lastModified($file),
                    ];
                    $pathSize += $size;
                    $pathCount++;
                }
            } catch (\Exception $e) {
                Log::warning("Failed to analyze path {$prefix}: {$e->getMessage()}");
            }

            if ($pathCount > 0) {
                $analysis[$diskName] = [
                    'path_prefix' => $prefix,
                    'files' => $files,
                    'total_files' => $pathCount,
                    'total_size' => $pathSize,
                    'total_size_formatted' => $this->formatBytes($pathSize),
                ];
                $totalSize += $pathSize;
                $totalFiles += $pathCount;
            }
        }

        return [
            'disks' => $analysis,
            'total_files' => $totalFiles,
            'total_size' => $totalSize,
            'total_size_formatted' => $this->formatBytes($totalSize),
        ];
    }

    /**
     * بدء ترحيل دفعة ملفات
     */
    public function startMigration(string $diskName, int $batchSize = 50, bool $async = true): StorageSyncBatch
    {
        $prefix = $this->getPathPrefixForDisk($diskName);
        if (!$prefix) {
            throw new \Exception("Unknown disk: {$diskName}");
        }

        $localDisk = Storage::disk('public');
        $files = $localDisk->allFiles($prefix);
        $totalFiles = count($files);

        if ($totalFiles === 0) {
            throw new \Exception("No files found in {$prefix}");
        }

        $batch = StorageSyncBatch::createBatch(
            "Migrate {$diskName} to cloud",
            $diskName,
            $totalFiles,
            Auth::id()
        );

        // تقسيم الملفات إلى دفعات
        $chunks = array_chunk($files, $batchSize);

        foreach ($chunks as $chunk) {
            foreach ($chunk as $file) {
                if ($async) {
                    StorageSyncJob::dispatch($file, $diskName, $batch->id)
                        ->onQueue('storage-sync')
                        ->backoff(10);
                } else {
                    // متزامن
                    $router = app(CloudFirstStorageRouter::class);
                    $result = $router->syncToCloud($file, $diskName);
                    
                    if ($result['success']) {
                        StorageSyncBatch::incrementSuccess($batch->id);
                    } else {
                        StorageSyncBatch::incrementFailure($batch->id, $result['error']);
                    }
                }
            }
        }

        return $batch;
    }

    /**
     * ترحيل جميع المسارات
     */
    public function migrateAll(int $batchSize = 50, bool $async = true): array
    {
        $results = [];

        foreach (self::KNOWN_PATHS as $prefix => $diskName) {
            $hasCloudStorage = \App\Models\StorageDiskMapping::where('disk_name', $diskName)
                ->where('is_active', true)
                ->exists();

            if (!$hasCloudStorage) {
                continue;
            }

            try {
                $batch = $this->startMigration($diskName, $batchSize, $async);
                $results[$diskName] = [
                    'success' => true,
                    'batch_id' => $batch->id,
                    'total_files' => $batch->total_files,
                ];
            } catch (\Exception $e) {
                $results[$diskName] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * الحصول على حالة الدفعة
     */
    public function getBatchStatus(int $batchId): ?array
    {
        $batch = StorageSyncBatch::find($batchId);
        if (!$batch) return null;

        return [
            'id' => $batch->id,
            'name' => $batch->name,
            'disk_name' => $batch->disk_name,
            'status' => $batch->status,
            'total_files' => $batch->total_files,
            'processed_files' => $batch->processed_files,
            'successful_files' => $batch->successful_files,
            'failed_files' => $batch->failed_files,
            'progress_percentage' => $batch->progress_percentage,
            'is_complete' => $batch->is_complete,
            'started_at' => $batch->started_at?->toDateTimeString(),
            'completed_at' => $batch->completed_at?->toDateTimeString(),
            'errors' => array_slice($batch->errors ?? [], -10),
        ];
    }

    /**
     * الحصول على جميع الدفعات
     */
    public function getBatches(int $perPage = 20): array
    {
        $batches = StorageSyncBatch::with('starter')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return [
            'items' => $batches->items(),
            'total' => $batches->total(),
            'current_page' => $batches->currentPage(),
            'last_page' => $batches->lastPage(),
        ];
    }

    /**
     * إلغاء دفعة
     */
    public function cancelBatch(int $batchId): bool
    {
        $batch = StorageSyncBatch::find($batchId);
        if (!$batch) return false;

        $batch->markCancelled();
        return true;
    }

    /**
     * حذف الملفات المحلية بعد الترحيل الناجح
     */
    public function cleanupLocalAfterMigration(string $diskName): array
    {
        $prefix = $this->getPathPrefixForDisk($diskName);
        if (!$prefix) {
            return ['success' => false, 'error' => 'Unknown disk'];
        }

        $localDisk = Storage::disk('public');
        $router = app(CloudFirstStorageRouter::class);
        $deleted = 0;
        $errors = [];

        $files = $localDisk->allFiles($prefix);

        foreach ($files as $file) {
            try {
                if ($router->exists($file, $diskName)) {
                    $localDisk->delete($file);
                    $deleted++;
                }
            } catch (\Exception $e) {
                $errors[] = ['path' => $file, 'error' => $e->getMessage()];
            }
        }

        return [
            'success' => true,
            'deleted' => $deleted,
            'errors' => $errors,
        ];
    }

    /**
     * التحقق من اكتمال الترحيل
     */
    public function verifyMigration(string $diskName): array
    {
        $prefix = $this->getPathPrefixForDisk($diskName);
        if (!$prefix) {
            return ['success' => false, 'error' => 'Unknown disk'];
        }

        $localDisk = Storage::disk('public');
        $router = app(CloudFirstStorageRouter::class);
        
        $localFiles = $localDisk->allFiles($prefix);
        $totalLocal = count($localFiles);
        $synced = 0;
        $missing = [];

        foreach ($localFiles as $file) {
            if ($router->exists($file, $diskName)) {
                $synced++;
            } else {
                $missing[] = $file;
            }
        }

        return [
            'success' => true,
            'total_local' => $totalLocal,
            'synced_to_cloud' => $synced,
            'missing_from_cloud' => count($missing),
            'missing_files' => array_slice($missing, 0, 50),
            'sync_percentage' => $totalLocal > 0 ? round(($synced / $totalLocal) * 100, 1) : 0,
        ];
    }

    private function getPathPrefixForDisk(string $diskName): ?string
    {
        foreach (self::KNOWN_PATHS as $prefix => $name) {
            if ($name === $diskName) {
                return $prefix;
            }
        }
        return null;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
