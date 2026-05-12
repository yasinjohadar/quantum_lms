<?php

namespace App\Services\Storage;

use App\Jobs\StorageSyncJob;
use App\Models\AppStorageConfig;
use App\Models\StorageDiskMapping;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * CloudFirstStorageRouter
 *
 * نظام توجيه التخزين السحابي الأول
 * يوجه جميع الملفات إلى السحابة عند توفرها، مع fallback تلقائي للوكال
 * يدعم رفع الملفات مباشرة من UploadedFile إلى السحابة
 */
class CloudFirstStorageRouter
{
    /**
     * المسارات المحلية المعروفة
     */
    private const LOCAL_PATHS = [
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

    public function __construct(
        protected AppStorageAnalyticsService $analytics,
    ) {}

    private function localFallbackDisk(): string
    {
        $name = config('storage.fallback_disk', 'public');

        return is_string($name) && $name !== '' ? $name : 'public';
    }

    /**
     * رفع ملف إلى السحابة (أو اللوكال عند عدم التوفر)
     */
    public function upload(UploadedFile $file, string $directory, ?string $diskName = null, ?string $fileName = null): array
    {
        $fileName = $fileName ?? $this->generateFileName($file);
        $path = rtrim($directory, '/') . '/' . $fileName;

        // تحديد الـ disk المناسب
        $targetDisk = $diskName ?? $this->resolveDiskForPath($path);

        // محاولة الرفع إلى السحابة أولاً
        $result = $this->uploadToDisk($targetDisk, $file, $path);

        if ($result['success']) {
            return [
                'success' => true,
                'path' => $path,
                'url' => $result['url'],
                'disk' => $targetDisk,
                'storage_type' => $result['storage_type'],
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ];
        }

        // Fallback إلى اللوكال
        Log::warning("Cloud upload failed, falling back to local. Disk: {$targetDisk}, Error: {$result['error']}");

        $localResult = $this->uploadToLocal($file, $path);

        if ($localResult['success']) {
            // تسجيل الملف للمزامنة لاحقاً
            $this->queueForSync($path, $targetDisk);
        }

        return $localResult;
    }

    /**
     * رفع ملف إلى disk محدد (أساسي ثم احتياطي حسب storage_disk_mappings)
     */
    public function uploadToDisk(string $diskName, UploadedFile $file, string $path): array
    {
        $mapping = $this->resolveActiveMapping($diskName);
        if (!$mapping) {
            return ['success' => false, 'error' => "No active cloud storage configured for disk: {$diskName}"];
        }

        $lastError = 'No storage backends available';

        foreach ($this->storagesFromMapping($mapping) as $storageConfig) {
            if (!$storageConfig || !$storageConfig->is_active) {
                continue;
            }

            $realPath = $file->getRealPath();
            if ($realPath === false || $realPath === '') {
                $lastError = 'Cannot resolve temporary file path';
                continue;
            }
            $stream = @fopen($realPath, 'rb');
            if ($stream === false) {
                $lastError = 'Unable to open upload stream';
                continue;
            }

            try {
                $disk = AppStorageFactory::create($storageConfig);
                // لا نمرّر ACL: كثير من الـ buckets (Object Ownership / Block ACL) يرفضونها ويفشل الرفع بالكامل
                $putOptions = ['visibility' => 'public', 'ContentType' => $file->getMimeType()];
                $result = $disk->put($path, $stream, $putOptions);

                if ($result) {
                    if (! $disk->exists($path)) {
                        $lastError = 'Upload reported success but object is missing';
                    } else {
                        $this->recordUploadAnalytics($storageConfig, $path, $file->getSize(), $file->getMimeType());

                        return [
                            'success' => true,
                            'path' => $path,
                            'url' => $disk->url($path),
                            'storage_type' => $storageConfig->driver,
                        ];
                    }
                } else {
                    $lastError = 'Upload returned false';
                }
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                Log::warning("Cloud upload attempt failed on {$storageConfig->name}: {$e->getMessage()}");
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }

        return ['success' => false, 'error' => $lastError];
    }

    /**
     * رفع إلى اللوكال
     */
    public function uploadToLocal(UploadedFile $file, string $path): array
    {
        try {
            $disk = Storage::disk($this->localFallbackDisk());
            $realPath = $file->getRealPath();
            if ($realPath === false || $realPath === '') {
                return ['success' => false, 'error' => 'Cannot resolve temporary file path'];
            }
            $stream = @fopen($realPath, 'rb');
            if ($stream === false) {
                return ['success' => false, 'error' => 'Unable to open upload stream for local storage'];
            }

            try {
                $result = $disk->put($path, $stream, ['visibility' => 'public']);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }

            if ($result) {
                if (! $disk->exists($path)) {
                    return ['success' => false, 'error' => 'Local put reported success but file is missing'];
                }

                return [
                    'success' => true,
                    'path' => $path,
                    'url' => $disk->url($path),
                    'disk' => $this->localFallbackDisk(),
                    'storage_type' => 'local',
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'needs_sync' => true,
                ];
            }

            return ['success' => false, 'error' => 'Local upload failed'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * حذف ملف من جميع الأماكن
     */
    public function delete(string $path, ?string $diskName = null): bool
    {
        $deleted = true;

        $targetDisk = $diskName ?? $this->resolveDiskForPath($path);
        $mapping = $this->resolveActiveMapping($targetDisk);

        if ($mapping) {
            foreach ($this->storagesFromMapping($mapping) as $storageConfig) {
                if (!$storageConfig || !$storageConfig->is_active) {
                    continue;
                }
                try {
                    $disk = AppStorageFactory::create($storageConfig);
                    if ($disk->exists($path)) {
                        $deleted = $disk->delete($path) && $deleted;
                    }
                } catch (\Throwable $e) {
                    Log::warning("Failed to delete from cloud backend {$storageConfig->name}: {$e->getMessage()}");
                }
            }
        }

        try {
            $localDisk = Storage::disk($this->localFallbackDisk());
            if ($localDisk->exists($path)) {
                $localDisk->delete($path);
            }
        } catch (\Exception $e) {
            Log::warning("Failed to delete from local: {$e->getMessage()}");
        }

        return $deleted;
    }

    /**
     * الحصول على URL للملف (يفضل السحابة ثم اللوكال).
     * للـ S3-compatible: عند تفعيل storage.media_use_presigned_urls يُستخدم رابط موقّت
     * لأن الرابط العام بدون توقيع يعيد 403 إذا كان الـ bucket خاصاً.
     */
    public function url(string $path, ?string $diskName = null): string
    {
        $targetDisk = $diskName ?? $this->resolveDiskForPath($path);
        $mapping = $this->resolveActiveMapping($targetDisk);

        if ($mapping) {
            foreach ($this->storagesFromMapping($mapping) as $storageConfig) {
                if (!$storageConfig || !$storageConfig->is_active) {
                    continue;
                }
                try {
                    $disk = AppStorageFactory::create($storageConfig);
                    if (!$disk->exists($path)) {
                        continue;
                    }

                    if ($storageConfig->cdn_url) {
                        return rtrim($storageConfig->cdn_url, '/') . '/' . ltrim($path, '/');
                    }

                    $usePresigned = (bool) config('storage.media_use_presigned_urls', true);
                    $s3Like = in_array($storageConfig->driver, ['s3', 'digitalocean', 'wasabi', 'backblaze', 'cloudflare_r2'], true);

                    if ($usePresigned && $s3Like) {
                        try {
                            return $disk->temporaryUrl($path, now()->addDays(7));
                        } catch (\Throwable $e) {
                            Log::debug('CloudFirstStorageRouter: temporaryUrl failed, falling back to public URL', [
                                'path' => $path,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }

                    return $disk->url($path);
                } catch (\Throwable) {
                    //
                }
            }
        }

        try {
            return Storage::disk($this->localFallbackDisk())->url($path);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * التحقق من وجود الملف
     */
    public function exists(string $path, ?string $diskName = null): bool
    {
        $targetDisk = $diskName ?? $this->resolveDiskForPath($path);
        $mapping = $this->resolveActiveMapping($targetDisk);

        if ($mapping) {
            foreach ($this->storagesFromMapping($mapping) as $storageConfig) {
                if (!$storageConfig || !$storageConfig->is_active) {
                    continue;
                }
                try {
                    $disk = AppStorageFactory::create($storageConfig);
                    if ($disk->exists($path)) {
                        return true;
                    }
                } catch (\Throwable) {
                    //
                }
            }
        }

        return Storage::disk($this->localFallbackDisk())->exists($path);
    }

    /**
     * هل الملف موجود على أحد تخزينات السحابة المرتبطة بالقرص المنطقي فقط (بدون فحص اللوكال).
     * يُستخدم في ترحيل التخزين والتحقق من اكتمال الرفع للسحابة.
     */
    public function existsOnMappedCloud(string $path, ?string $diskName = null): bool
    {
        $targetDisk = $diskName ?? $this->resolveDiskForPath($path);
        $mapping = $this->resolveActiveMapping($targetDisk);

        if (! $mapping) {
            return false;
        }

        foreach ($this->storagesFromMapping($mapping) as $storageConfig) {
            if (! $storageConfig || ! $storageConfig->is_active) {
                continue;
            }
            try {
                $disk = AppStorageFactory::create($storageConfig);
                if ($disk->exists($path)) {
                    return true;
                }
            } catch (\Throwable) {
                //
            }
        }

        return false;
    }

    /**
     * نسخ ملف من لوكال إلى سحابة
     */
    public function syncToCloud(string $localPath, string $targetDisk): array
    {
        try {
            $localDisk = Storage::disk($this->localFallbackDisk());

            if (!$localDisk->exists($localPath)) {
                return ['success' => false, 'error' => 'Local file not found'];
            }

            $content = $localDisk->get($localPath);
            $put = $this->putStringOnMappedDisk($targetDisk, $localPath, $content, 'application/octet-stream');

            if ($put['success']) {
                return [
                    'success' => true,
                    'path' => $localPath,
                    'url' => $put['url'] ?? '',
                ];
            }

            return ['success' => false, 'error' => $put['error'] ?? 'Cloud upload failed'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * نسخ ملف من سحابة إلى لوكال (للـ fallback)
     */
    public function syncToLocal(string $cloudPath, string $sourceDisk): array
    {
        try {
            $mapping = $this->resolveActiveMapping($sourceDisk);
            $content = null;

            if ($mapping) {
                foreach ($this->storagesFromMapping($mapping) as $storageConfig) {
                    if (!$storageConfig || !$storageConfig->is_active) {
                        continue;
                    }
                    try {
                        $cloudDisk = AppStorageFactory::create($storageConfig);
                        if ($cloudDisk->exists($cloudPath)) {
                            $content = $cloudDisk->get($cloudPath);
                            break;
                        }
                    } catch (\Throwable) {
                        continue;
                    }
                }
            }

            if ($content === null) {
                return ['success' => false, 'error' => 'Cloud file not found'];
            }

            $localDisk = Storage::disk($this->localFallbackDisk());

            $result = $localDisk->put($cloudPath, $content, ['visibility' => 'public']);

            if ($result) {
                return ['success' => true, 'path' => $cloudPath];
            }

            return ['success' => false, 'error' => 'Local copy failed'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * رفع محتوى مباشر (string/stream)
     */
    public function uploadContent(string $path, $content, string $mimeType = 'application/octet-stream', ?string $diskName = null): array
    {
        $targetDisk = $diskName ?? $this->resolveDiskForPath($path);

        $put = $this->putStringOnMappedDisk($targetDisk, $path, $content, $mimeType);
        if ($put['success']) {
            return [
                'success' => true,
                'path' => $path,
                'url' => $put['url'] ?? '',
                'storage_type' => $put['storage_type'] ?? $this->getStorageType($targetDisk),
            ];
        }

        Log::warning("Cloud content upload failed: {$put['error']}");

        try {
            $localDisk = Storage::disk($this->localFallbackDisk());
            $result = $localDisk->put($path, $content, ['visibility' => 'public']);

            if ($result) {
                $this->queueForSync($path, $targetDisk);

                return [
                    'success' => true,
                    'path' => $path,
                    'url' => $localDisk->url($path),
                    'storage_type' => 'local',
                    'needs_sync' => true,
                ];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }

        return ['success' => false, 'error' => 'All storage options failed'];
    }

    /**
     * @return array{success: bool, url?: string, storage_type?: string, error?: string}
     */
    private function putStringOnMappedDisk(string $logicalDiskName, string $path, string $content, string $mimeType): array
    {
        $mapping = $this->resolveActiveMapping($logicalDiskName);
        if (!$mapping) {
            return ['success' => false, 'error' => "No active cloud storage configured for disk: {$logicalDiskName}"];
        }

        $lastError = 'No storage backends available';

        foreach ($this->storagesFromMapping($mapping) as $storageConfig) {
            if (!$storageConfig || !$storageConfig->is_active) {
                continue;
            }
            try {
                $disk = AppStorageFactory::create($storageConfig);
                $result = $disk->put($path, $content, [
                    'visibility' => 'public',
                    'ContentType' => $mimeType,
                ]);

                if ($result) {
                    $this->recordUploadAnalytics($storageConfig, $path, strlen($content), $mimeType);

                    return [
                        'success' => true,
                        'url' => $disk->url($path),
                        'storage_type' => $storageConfig->driver,
                    ];
                }

                $lastError = 'Upload returned false';
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                Log::warning("Cloud content upload attempt failed on {$storageConfig->name}: {$e->getMessage()}");
            }
        }

        return ['success' => false, 'error' => $lastError];
    }

    /**
     * مطابقة أطول بادئة معروفة (public) → اسم القرص المنطقي
     */
    public static function logicalDiskForPath(string $path): ?string
    {
        $entries = self::LOCAL_PATHS;
        uksort(
            $entries,
            static fn (string $a, string $b): int => strlen($b) <=> strlen($a)
        );
        foreach ($entries as $prefix => $diskName) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return $diskName;
            }
        }

        return null;
    }

    /**
     * اسم القرص المنطقي للمسار (مع fallback من storage.disk_map ثم images)
     */
    public static function resolveLogicalDiskName(string $path, ?string $category = null): string
    {
        if (($known = self::logicalDiskForPath($path)) !== null) {
            return $known;
        }

        $map = config('storage.disk_map', []);
        uksort(
            $map,
            static fn (string $a, string $b): int => strlen($b) <=> strlen($a)
        );
        foreach ($map as $prefix => $disk) {
            if (str_contains($path, $prefix)) {
                return $disk;
            }
        }

        if ($category !== null) {
            return $map[$category] ?? 'images';
        }

        return 'images';
    }

    /**
     * تحديد الـ disk المناسب بناءً على المسار
     */
    private function resolveDiskForPath(string $path): string
    {
        return self::resolveLogicalDiskName($path, null);
    }

    /**
     * الحصول على disk (سحابي) — الأساسي فقط (للتوافق مع أماكن تتطلب سلوكاً صارماً)
     */
    public function getDisk(string $diskName): Filesystem
    {
        $mapping = $this->resolveActiveMapping($diskName);

        if (!$mapping || !$mapping->primaryStorage || !$mapping->primaryStorage->is_active) {
            throw new \Exception("No active cloud storage configured for disk: {$diskName}");
        }

        return AppStorageFactory::create($mapping->primaryStorage);
    }

    private function resolveActiveMapping(string $diskName): ?StorageDiskMapping
    {
        $candidates = array_values(array_unique(array_filter(
            [$diskName, config('storage.default_cloud_disk')],
            static fn ($v): bool => is_string($v) && $v !== ''
        )));

        foreach ($candidates as $name) {
            // مطابقة حرفية أولاً (الحالة الطبيعية: كل disk في سجل منفصل)
            $mapping = StorageDiskMapping::query()
                ->where('disk_name', $name)
                ->where('is_active', true)
                ->with('primaryStorage')
                ->first();
            if ($mapping) {
                return $mapping;
            }

            // احتياطي: disk_name قد يحتوي على قائمة مفصولة بفاصلة (إدخال خاطئ من المستخدم)
            $mapping = StorageDiskMapping::query()
                ->where('is_active', true)
                ->get()
                ->first(function (StorageDiskMapping $m) use ($name): bool {
                    $parts = array_map('trim', explode(',', (string) $m->disk_name));
                    return in_array($name, $parts, true);
                });
            if ($mapping) {
                // نصلح البيانات تلقائياً (نقسّم السجل الواحد إلى سجلات منفصلة)
                $this->normalizeCommaSeparatedMapping($mapping);
                // نُعيد البحث بعد التصليح
                $fixed = StorageDiskMapping::query()
                    ->where('disk_name', $name)
                    ->where('is_active', true)
                    ->with('primaryStorage')
                    ->first();
                return $fixed ?? $mapping;
            }
        }

        return null;
    }

    /**
     * هل يوجد ربط سحابي نشط لهذا القرص المنطقي (يشمل DEFAULT_CLOUD_DISK كاحتياط)
     */
    public function activeMappingForLogicalDisk(string $logicalDiskName): ?StorageDiskMapping
    {
        return $this->resolveActiveMapping($logicalDiskName);
    }

    /**
     * @return \Illuminate\Support\Collection<int, AppStorageConfig>
     */
    private function storagesFromMapping(StorageDiskMapping $mapping)
    {
        return collect([$mapping->primaryStorage])
            ->merge($mapping->getFallbackStorages())
            ->filter(fn ($s) => $s instanceof AppStorageConfig);
    }

    private function recordUploadAnalytics(AppStorageConfig $storage, string $path, int $bytes, ?string $fileType): void
    {
        try {
            $this->analytics->trackStorageUsage($storage, $bytes, $fileType);
            $this->analytics->trackBandwidth($storage, 'upload', $bytes, $fileType);
        } catch (\Throwable $e) {
            Log::warning('Storage analytics failed: ' . $e->getMessage());
        }
    }

    /**
     * توليد اسم ملف فريد
     */
    private function generateFileName(UploadedFile $file): string
    {
        return uniqid('file_', true) . '.' . $file->getClientOriginalExtension();
    }

    /**
     * تحديد نوع التخزين (حسب التخزين الأساسي للـ mapping)
     */
    private function getStorageType(string $diskName): string
    {
        $mapping = $this->resolveActiveMapping($diskName);

        return $mapping?->primaryStorage?->driver ?? 'unknown';
    }

    /**
     * إذا كان disk_name يحتوي على قائمة بالفاصلة، نقسّمها إلى سجلات منفصلة تلقائياً
     */
    private function normalizeCommaSeparatedMapping(StorageDiskMapping $mapping): void
    {
        $parts = array_values(array_unique(array_filter(
            array_map('trim', explode(',', (string) $mapping->disk_name))
        )));

        if (count($parts) <= 1) {
            return;
        }

        try {
            $fallbackIds = $mapping->fallback_storage_ids;

            // نحدّث السجل الأصلي ليصبح أول disk فقط
            $mapping->update(['disk_name' => $parts[0]]);

            foreach (array_slice($parts, 1) as $diskName) {
                if (StorageDiskMapping::where('disk_name', $diskName)->exists()) {
                    continue;
                }
                StorageDiskMapping::create([
                    'disk_name'            => $diskName,
                    'label'                => $mapping->label . ' - ' . $diskName,
                    'primary_storage_id'   => $mapping->primary_storage_id,
                    'fallback_storage_ids' => $fallbackIds,
                    'is_active'            => true,
                    'file_types'           => $mapping->file_types,
                ]);
            }

            Log::info('StorageRouter: normalized comma-separated disk mapping', [
                'original' => implode(', ', $parts),
                'created'  => count($parts),
            ]);
        } catch (\Throwable $e) {
            Log::warning('StorageRouter: failed to normalize disk mapping: ' . $e->getMessage());
        }
    }

    private function queueForSync(string $path, string $targetDisk): void
    {
        try {
            StorageSyncJob::dispatch($path, $targetDisk)->onQueue(config('storage.sync_queue', 'storage-sync'));
        } catch (\Exception $e) {
            Log::warning("Failed to queue sync job: {$e->getMessage()}");
        }
    }

    /**
     * الحصول على جميع الملفات المحلية التي تحتاج مزامنة
     */
    public function getPendingSyncFiles(): array
    {
        $pending = [];
        $localDisk = Storage::disk($this->localFallbackDisk());

        foreach (self::LOCAL_PATHS as $prefix => $diskName) {
            if (! $this->resolveActiveMapping($diskName)) {
                continue;
            }

            $files = $localDisk->allFiles($prefix);
            foreach ($files as $file) {
                $pending[] = [
                    'path' => $file,
                    'target_disk' => $diskName,
                    'size' => $localDisk->size($file),
                ];
            }
        }

        return $pending;
    }

    /**
     * مزامنة جميع الملفات المحلية إلى السحابة
     */
    public function syncAllToCloud(?callable $progressCallback = null): array
    {
        $results = ['success' => 0, 'failed' => 0, 'skipped' => 0, 'errors' => []];
        $pending = $this->getPendingSyncFiles();
        $total = count($pending);

        foreach ($pending as $index => $file) {
            if ($progressCallback) {
                $progressCallback($index, $total, $file['path']);
            }

            $result = $this->syncToCloud($file['path'], $file['target_disk']);

            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'path' => $file['path'],
                    'error' => $result['error'],
                ];
            }
        }

        return $results;
    }
}
