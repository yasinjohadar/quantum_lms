<?php

namespace App\Services\Storage;

use App\Jobs\StorageSyncJob;
use App\Models\StorageSyncBatch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * StorageMigrationService
 *
 * خدمة ترحيل الملفات من التخزين المحلي إلى السحابة
 * تدعم الترحيل الدفعي (batch) مع تتبع التقدم
 */
class StorageMigrationService
{
    /**
     * المسارات المعروفة (يجب أن تبقى متوافقة مع CloudFirstStorageRouter::LOCAL_PATHS)
     */
    private const KNOWN_PATHS = [
        'users/photos' => 'avatars',
        'quizzes' => 'images',
        'questions/images' => 'images',
        'question_options' => 'images',
        'subjects/images' => 'images',
        'subjects/og_images' => 'images',
        'stages/images' => 'images',
        'stages/og_images' => 'images',
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
        $localDisk = $this->localPublicDisk();
        $totalSize = 0;
        $totalFiles = 0;

        foreach (self::KNOWN_PATHS as $prefix => $diskName) {
            if ($specificDisk && $diskName !== $specificDisk) {
                continue;
            }

            $hasCloudStorage = app(CloudFirstStorageRouter::class)
                ->activeMappingForLogicalDisk($diskName) !== null;

            if (!$hasCloudStorage) {
                continue;
            }

            if (! isset($analysis[$diskName])) {
                $analysis[$diskName] = [
                    'prefixes' => [],
                    'files' => [],
                    'total_files' => 0,
                    'total_size' => 0,
                    'total_size_formatted' => '0 B',
                ];
            }

            try {
                $allFiles = $localDisk->allFiles($prefix);
                foreach ($allFiles as $file) {
                    $size = $localDisk->size($file);
                    $analysis[$diskName]['files'][] = [
                        'path' => $file,
                        'size' => $size,
                        'size_formatted' => $this->formatBytes($size),
                        'last_modified' => $localDisk->lastModified($file),
                        'prefix' => $prefix,
                    ];
                    $analysis[$diskName]['total_files']++;
                    $analysis[$diskName]['total_size'] += $size;
                    $totalSize += $size;
                    $totalFiles++;
                }
                if (count($allFiles) > 0 && ! in_array($prefix, $analysis[$diskName]['prefixes'], true)) {
                    $analysis[$diskName]['prefixes'][] = $prefix;
                }
            } catch (\Exception $e) {
                Log::warning("Failed to analyze path {$prefix}: {$e->getMessage()}");
            }

            $analysis[$diskName]['total_size_formatted'] = $this->formatBytes($analysis[$diskName]['total_size']);
            $analysis[$diskName]['path_prefix'] = implode(', ', $analysis[$diskName]['prefixes']);
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
     *
     * @param  bool  $deleteLocalAfterEachSync  عند true: بعد كل رفع ناجح للسحابة يُحذف الملف من اللوكال (عبر StorageSyncJob)
     */
    public function startMigration(string $diskName, int $batchSize = 50, bool $async = true, bool $deleteLocalAfterEachSync = false): StorageSyncBatch
    {
        $prefixes = $this->getPathPrefixesForDisk($diskName);
        if ($prefixes === []) {
            throw new \Exception("Unknown disk: {$diskName}");
        }

        $localDisk = $this->localPublicDisk();
        $files = [];

        foreach ($prefixes as $prefix) {
            foreach ($localDisk->allFiles($prefix) as $file) {
                $files[] = $file;
            }
        }

        $files = array_values(array_unique($files));
        $totalFiles = count($files);

        if ($totalFiles === 0) {
            throw new \Exception('No local files found for disk '.$diskName);
        }

        $batch = StorageSyncBatch::createBatch(
            "Migrate {$diskName} to cloud",
            $diskName,
            $totalFiles,
            Auth::id()
        );

        $chunks = array_chunk($files, $batchSize);
        $queue = config('storage.sync_queue', 'storage-sync');

        foreach ($chunks as $chunk) {
            foreach ($chunk as $file) {
                if ($async) {
                    StorageSyncJob::dispatch($file, $diskName, $batch->id, $deleteLocalAfterEachSync)
                        ->onQueue($queue)
                        ->backoff(10);
                } else {
                    $router = app(CloudFirstStorageRouter::class);
                    $result = $router->syncToCloud($file, $diskName);

                    if ($result['success']) {
                        StorageSyncBatch::incrementSuccess($batch->id);
                        if ($deleteLocalAfterEachSync && $localDisk->exists($file)) {
                            $localDisk->delete($file);
                        }
                    } else {
                        StorageSyncBatch::incrementFailure($batch->id, $result['error'] ?? 'unknown');
                    }
                }
            }
        }

        return $batch;
    }

    /**
     * ترحيل جميع المسارات
     *
     * @return array<string, array<string, mixed>>
     */
    public function migrateAll(int $batchSize = 50, bool $async = true, bool $deleteLocalAfterEachSync = false): array
    {
        $results = [];

        foreach (self::KNOWN_PATHS as $prefix => $diskName) {
            $hasCloudStorage = app(CloudFirstStorageRouter::class)
                ->activeMappingForLogicalDisk($diskName) !== null;

            if (!$hasCloudStorage) {
                continue;
            }

            if (isset($results[$diskName])) {
                continue;
            }

            try {
                $batch = $this->startMigration($diskName, $batchSize, $async, $deleteLocalAfterEachSync);
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
        if (!$batch) {
            return null;
        }

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
        if (!$batch) {
            return false;
        }

        $batch->markCancelled();

        return true;
    }

    /**
     * حذف الملفات المحلية بعد الترحيل الناجح (يُحذف فقط إن وُجد نسخة على السحابة)
     */
    public function cleanupLocalAfterMigration(string $diskName): array
    {
        $prefixes = $this->getPathPrefixesForDisk($diskName);
        if ($prefixes === []) {
            return ['success' => false, 'error' => 'Unknown disk'];
        }

        $localDisk = $this->localPublicDisk();
        $router = app(CloudFirstStorageRouter::class);
        $deleted = 0;
        $errors = [];

        foreach ($prefixes as $prefix) {
            foreach ($localDisk->allFiles($prefix) as $file) {
                try {
                    if ($router->existsOnMappedCloud($file, $diskName)) {
                        $localDisk->delete($file);
                        $deleted++;
                    }
                } catch (\Exception $e) {
                    $errors[] = ['path' => $file, 'error' => $e->getMessage()];
                }
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
        $prefixes = $this->getPathPrefixesForDisk($diskName);
        if ($prefixes === []) {
            return ['success' => false, 'error' => 'Unknown disk'];
        }

        $localDisk = $this->localPublicDisk();
        $router = app(CloudFirstStorageRouter::class);

        $localFiles = [];
        foreach ($prefixes as $prefix) {
            foreach ($localDisk->allFiles($prefix) as $file) {
                $localFiles[] = $file;
            }
        }
        $localFiles = array_values(array_unique($localFiles));

        $totalLocal = count($localFiles);
        $synced = 0;
        $missing = [];

        foreach ($localFiles as $file) {
            if ($router->existsOnMappedCloud($file, $diskName)) {
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

    /**
     * @return list<string>
     */
    private function getPathPrefixesForDisk(string $diskName): array
    {
        $prefixes = [];
        foreach (self::KNOWN_PATHS as $prefix => $name) {
            if ($name === $diskName) {
                $prefixes[] = $prefix;
            }
        }

        return $prefixes;
    }

    /**
     * قرص الملفات العامة المحلية (نفس storage.fallback_disk المستخدم في الراوتر والرفع)
     */
    private function localPublicDisk(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        return Storage::disk(config('storage.fallback_disk', 'public'));
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
