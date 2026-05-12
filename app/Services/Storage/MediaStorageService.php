<?php

namespace App\Services\Storage;

use App\Enums\StorageDriverMode;
use App\Models\MediaFile;
use App\Jobs\StorageSyncJob;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * MediaStorageService
 * 
 * الطبقة الموحدة لإدارة رفع الملفات
 * تدعم Cloud-First مع Fallback تلقائي للوكال
 * 
 * Usage:
 *   $result = MediaStorageService::uploadImage($file, 'users/photos');
 *   $url = MediaStorageService::url($result['path']);
 *   MediaStorageService::delete($result['path']);
 */
class MediaStorageService
{
    /**
     * رفع صورة
     */
    public static function uploadImage(UploadedFile $file, string $directory, ?string $customName = null, bool $private = false): array
    {
        return self::upload($file, $directory, 'image', $customName, $private);
    }

    /**
     * رفع فيديو
     */
    public static function uploadVideo(UploadedFile $file, string $directory, ?string $customName = null, bool $private = false): array
    {
        return self::upload($file, $directory, 'video', $customName, $private);
    }

    /**
     * رفع مستند
     */
    public static function uploadDocument(UploadedFile $file, string $directory, ?string $customName = null, bool $private = false): array
    {
        return self::upload($file, $directory, 'document', $customName, $private);
    }

    /**
     * رفع ملف خاص (private visibility)
     */
    public static function uploadPrivateFile(UploadedFile $file, string $directory, ?string $customName = null): array
    {
        return self::upload($file, $directory, null, $customName, true);
    }

    /**
     * رفع عام (الطريقة الأساسية)
     */
    public static function upload(UploadedFile $file, string $directory, ?string $category = null, ?string $customName = null, bool $private = false): array
    {
        $startTime = microtime(true);
        $visibility = $private ? 'private' : 'public';
        $category = $category ?? self::guessCategory($file);
        $directory = trim(str_replace('\\', '/', $directory), '/');
        $fileName = $customName ?? self::generateFileName($file);
        $fileName = basename(str_replace('\\', '/', $fileName));
        if ($fileName === '' || $fileName === '.' || $fileName === '..') {
            $fileName = self::generateFileName($file);
        }
        $path = $directory === '' ? $fileName : $directory.'/'.$fileName;
        $checksum = null;

        // التحقق من الحجم
        self::validateSize($file);

        // التحقق من النوع
        self::validateMime($file, $category);

        // Deduplication check — لا نعيد مساراً قديماً إن كان الملف غير موجود فعلياً (تجنّب 404 ومسار يُحفظ بلا ملف)
        $existingDuplicate = self::checkDuplicate($file);
        if ($existingDuplicate !== null && self::exists($existingDuplicate->path)) {
            Log::info('Storage: Duplicate file detected', [
                'checksum' => $existingDuplicate->checksum,
                'existing_path' => $existingDuplicate->path,
                'new_path' => $path,
            ]);

            return [
                'success' => true,
                'path' => $existingDuplicate->path,
                'url' => self::url($existingDuplicate->path),
                'disk' => $existingDuplicate->disk,
                'storage_provider' => $existingDuplicate->storage_provider,
                'is_duplicate' => true,
                'original_id' => $existingDuplicate->id,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'upload_time_ms' => round((microtime(true) - $startTime) * 1000),
            ];
        }
        if ($existingDuplicate !== null) {
            Log::warning('Storage: Duplicate checksum found but file missing on storage; uploading as new file', [
                'stale_path' => $existingDuplicate->path,
                'intended_path' => $path,
            ]);
        }

        $mode = StorageDriverModeResolver::current();
        $result = null;

        switch ($mode) {
            case StorageDriverMode::LOCAL_ONLY:
                $result = self::attemptLocalUpload($file, $path, $visibility, $category);
                break;

            case StorageDriverMode::CLOUD_ONLY:
                $result = self::attemptCloudUpload($file, $path, $visibility, $category);
                if (!$result || empty($result['success'])) {
                    throw self::uploadFailureException($result, 'فشل الرفع السحابي ووضع التخزين لا يسمح باللوكال.');
                }
                break;

            case StorageDriverMode::CLOUD_FIRST:
                $result = self::attemptCloudUpload($file, $path, $visibility, $category);
                if (!$result || empty($result['success'])) {
                    $result = self::attemptLocalUpload($file, $path, $visibility, $category);
                    if (!empty($result['success'])) {
                        self::dispatchStorageSyncJob($path, $category);
                    }
                }
                break;

            case StorageDriverMode::LOCAL_FIRST:
                $result = self::attemptLocalUpload($file, $path, $visibility, $category);
                if (!empty($result['success'])) {
                    self::dispatchStorageSyncJob($path, $category);
                }
                break;

            case StorageDriverMode::DUAL_WRITE:
                $local = self::attemptLocalUpload($file, $path, $visibility, $category);
                if (!$local || empty($local['success'])) {
                    throw self::uploadFailureException($local, 'فشل الرفع المحلي (الوضع المزدوج يتطلب نجاح اللوكال).');
                }
                $cloud = self::attemptCloudUpload($file, $path, $visibility, $category);
                if ($cloud && !empty($cloud['success'])) {
                    $result = array_merge($local, [
                        'url' => $cloud['url'],
                        'disk' => $cloud['disk'],
                        'storage_provider' => $cloud['storage_provider'],
                        'needs_sync' => false,
                    ]);
                } else {
                    $result = $local;
                    self::dispatchStorageSyncJob($path, $category);
                }
                break;

            default:
                $result = self::attemptCloudUpload($file, $path, $visibility, $category);
                if (!$result || empty($result['success'])) {
                    $result = self::attemptLocalUpload($file, $path, $visibility, $category);
                    if (!empty($result['success'])) {
                        self::dispatchStorageSyncJob($path, $category);
                    }
                }
                break;
        }

        if (!$result || empty($result['success'])) {
            $msg = (is_array($result) && isset($result['error'])) ? (string) $result['error'] : 'تعذّر رفع الملف (السحابة واللوكال).';
            throw self::uploadFailureException($result, $msg);
        }

        // Calculate checksum
        $checksum = self::calculateChecksum($file);

        // Record in MediaFile
        $mediaFile = self::recordMediaFile([
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => $result['disk'] ?? 'public',
            'storage_provider' => $result['storage_provider'] ?? 'local',
            'visibility' => $visibility,
            'category' => $category,
            'checksum' => $checksum,
            'uploaded_by' => Auth::id(),
            'is_synced' => ($result['storage_provider'] ?? 'local') !== 'local',
            'upload_time_ms' => round((microtime(true) - $startTime) * 1000),
        ]);

        // Log the upload
        if (config('storage.log_uploads', true)) {
            Log::channel(config('storage.log_channel', 'daily'))->info('Storage: File uploaded', [
                'path' => $path,
                'disk' => $result['disk'] ?? 'public',
                'provider' => $result['storage_provider'] ?? 'local',
                'size' => $file->getSize(),
                'category' => $category,
                'time_ms' => $result['upload_time_ms'] ?? 0,
            ]);
        }

        return array_merge($result, [
            'media_file_id' => $mediaFile?->id,
            'checksum' => $checksum,
        ]);
    }

    /**
     * محاولة الرفع للسحابة
     */
    private static function attemptCloudUpload(UploadedFile $file, string $path, string $visibility, string $category): ?array
    {
        try {
            $diskName = self::resolveDiskName($path, $category);
            $router = app(CloudFirstStorageRouter::class);
            
            $result = $router->uploadToDisk($diskName, $file, $path);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'path' => $path,
                    'url' => $result['url'],
                    'disk' => $diskName,
                    'storage_provider' => self::getProviderName($diskName),
                    'upload_time_ms' => 0,
                ];
            }

            Log::warning('Storage: Cloud upload did not succeed (using local if allowed by mode)', [
                'path' => $path,
                'logical_disk' => $diskName,
                'error' => $result['error'] ?? 'unknown',
            ]);
        } catch (\Exception $e) {
            Log::warning('Storage: Cloud upload failed, falling back to local', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * محاولة الرفع للوكال
     */
    private static function attemptLocalUpload(UploadedFile $file, string $path, string $visibility, string $category): array
    {
        try {
            $disk = Storage::disk(config('storage.fallback_disk', 'public'));
            $realPath = $file->getRealPath();
            if ($realPath === false || $realPath === '') {
                return ['success' => false, 'error' => 'Cannot resolve temporary file path'];
            }
            $stream = @fopen($realPath, 'rb');
            if ($stream === false) {
                return ['success' => false, 'error' => 'Unable to open upload stream for local storage'];
            }

            $options = ['visibility' => $visibility];
            try {
                $result = $disk->put($path, $stream, $options);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }

            if ($result) {
                if (! $disk->exists($path)) {
                    Log::error('Storage: Local put returned true but file is missing', ['path' => $path]);

                    return ['success' => false, 'error' => 'Local storage reported success but file was not written'];
                }

                return [
                    'success' => true,
                    'path' => $path,
                    'url' => $disk->url($path),
                    'disk' => config('storage.fallback_disk', 'public'),
                    'storage_provider' => 'local',
                    'upload_time_ms' => 0,
                    'needs_sync' => true,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Storage: Local upload failed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
        }

        return ['success' => false, 'error' => 'All storage options failed'];
    }

    /**
     * @param  array<string, mixed>|null  $result
     */
    private static function uploadFailureException(?array $result, string $fallbackMessage): \RuntimeException
    {
        $msg = (is_array($result) && isset($result['error']) && $result['error'] !== '')
            ? (string) $result['error']
            : $fallbackMessage;

        return new \RuntimeException($msg);
    }

    private static function dispatchStorageSyncJob(string $path, string $category): void
    {
        $diskName = self::resolveDiskName($path, $category);
        try {
            StorageSyncJob::dispatch($path, $diskName)->onQueue(config('storage.sync_queue', 'storage-sync'));
        } catch (\Exception $e) {
            Log::warning('Storage: Failed to queue sync job', ['path' => $path, 'error' => $e->getMessage()]);
        }
    }

    /**
     * حذف ملف
     */
    public static function delete(string $path): bool
    {
        $deleted = true;

        // حذف من السحابة
        try {
            $router = app(CloudFirstStorageRouter::class);
            $router->delete($path);
        } catch (\Exception $e) {
            Log::warning('Storage: Cloud delete failed', ['path' => $path, 'error' => $e->getMessage()]);
        }

        // حذف من اللوكال
        try {
            $disk = Storage::disk(config('storage.fallback_disk', 'public'));
            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        } catch (\Exception $e) {
            Log::warning('Storage: Local delete failed', ['path' => $path, 'error' => $e->getMessage()]);
            $deleted = false;
        }

        // تحديث MediaFile
        MediaFile::where('path', $path)->update(['deleted_at' => now()]);

        return $deleted;
    }

    /**
     * الحصول على URL
     */
    public static function url(string $path, ?string $disk = null): string
    {
        $path = trim($path);
        if (preg_match('#^https?://[^/]+/storage/(.+)$#i', $path, $m)) {
            $path = $m[1];
        }
        $path = ltrim(str_replace('\\', '/', $path), '/');
        if ($path === '') {
            return '';
        }

        // توجيه واحد: نفس منطق السحابة ثم اللوكال داخل الراوتر (بدون شرط exists منفصل قد يختلف عن url)
        try {
            $router = app(CloudFirstStorageRouter::class);

            return $router->url($path, $disk);
        } catch (\Exception $e) {
            Log::debug('MediaStorageService::url router failed', ['path' => $path, 'error' => $e->getMessage()]);
        }

        try {
            return Storage::disk(config('storage.fallback_disk', 'public'))->url($path);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * URL مؤقت (للملفات الخاصة)
     */
    public static function temporaryUrl(string $path, \DateTimeInterface $expiry, ?string $disk = null): ?string
    {
        $diskName = $disk ?? self::resolveDiskFromPath($path);
        
        try {
            $router = app(CloudFirstStorageRouter::class);
            $cloudDisk = $router->getDisk($diskName);
            
            if ($cloudDisk->exists($path)) {
                return $cloudDisk->temporaryUrl($path, $expiry);
            }
        } catch (\Exception $e) {
            Log::warning('Storage: Failed to generate temporary URL', ['path' => $path, 'error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * التحقق من وجود ملف
     */
    public static function exists(string $path): bool
    {
        try {
            $router = app(CloudFirstStorageRouter::class);
            if ($router->exists($path)) {
                return true;
            }
        } catch (\Exception $e) {
            //
        }

        return Storage::disk(config('storage.fallback_disk', 'public'))->exists($path);
    }

    /**
     * نسخ ملف
     */
    public static function copy(string $fromPath, string $toPath): bool
    {
        try {
            $disk = Storage::disk(config('storage.fallback_disk', 'public'));
            $content = $disk->get($fromPath);
            return $disk->put($toPath, $content, ['visibility' => 'public']);
        } catch (\Exception $e) {
            Log::error('Storage: Copy failed', ['from' => $fromPath, 'to' => $toPath, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * نقل ملف
     */
    public static function move(string $fromPath, string $toPath): bool
    {
        $result = self::copy($fromPath, $toPath);
        if ($result) {
            self::delete($fromPath);
        }
        return $result;
    }

    /**
     * الحصول على حجم الملف
     */
    public static function size(string $path): int
    {
        try {
            return Storage::disk(config('storage.fallback_disk', 'public'))->size($path);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * ========================
     * Helper Methods
     * ========================
     */

    private static function validateSize(UploadedFile $file): void
    {
        $maxSize = config('storage.max_file_size', 500 * 1024 * 1024);
        if ($file->getSize() > $maxSize) {
            throw new \Exception('حجم الملف يتجاوز الحد المسموح (' . self::formatBytes($maxSize) . ')');
        }
    }

    private static function validateMime(UploadedFile $file, string $category): void
    {
        $allowed = config("storage.allowed_mimes.{$category}");
        if ($allowed && !in_array($file->getMimeType(), $allowed)) {
            throw new \Exception('نوع الملف غير مسموح: ' . $file->getMimeType());
        }
    }

    private static function checkDuplicate(UploadedFile $file): ?MediaFile
    {
        if (!config('storage.deduplication.enabled', true)) {
            return null;
        }

        if ($file->getSize() < config('storage.deduplication.min_size_bytes', 10240)) {
            return null;
        }

        $checksum = self::calculateChecksum($file);
        if (!$checksum) {
            return null;
        }

        return MediaFile::where('checksum', $checksum)
            ->whereNull('deleted_at')
            ->first();
    }

    private static function calculateChecksum(UploadedFile $file): ?string
    {
        try {
            $real = $file->getRealPath();
            $path = ($real !== false && $real !== '') ? $real : $file->getPathname();
            if ($path === '' || ! is_readable($path)) {
                return null;
            }

            return hash_file('sha256', $path);
        } catch (\Exception $e) {
            return null;
        }
    }

    private static function generateFileName(UploadedFile $file): string
    {
        return uniqid('file_', true) . '.' . $file->getClientOriginalExtension();
    }

    private static function guessCategory(UploadedFile $file): string
    {
        $mime = $file->getMimeType();
        
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        if (str_starts_with($mime, 'audio/')) return 'audio';
        if (in_array($mime, ['application/pdf', 'application/msword', 'application/vnd.ms-excel'])) return 'document';
        
        return 'other';
    }

    private static function resolveDiskName(string $path, string $category): string
    {
        return CloudFirstStorageRouter::resolveLogicalDiskName($path, $category);
    }

    private static function resolveDiskFromPath(string $path): ?string
    {
        $mediaFile = MediaFile::where('path', $path)->whereNull('deleted_at')->first();
        return $mediaFile?->disk;
    }

    private static function getProviderName(string $diskName): string
    {
        $mapping = app(CloudFirstStorageRouter::class)->activeMappingForLogicalDisk($diskName);

        return $mapping?->primaryStorage?->driver ?? 'unknown';
    }

    private static function recordMediaFile(array $data): ?MediaFile
    {
        try {
            return MediaFile::create($data);
        } catch (\Exception $e) {
            Log::warning('Storage: Failed to record media file', ['path' => $data['path'], 'error' => $e->getMessage()]);
            return null;
        }
    }

    private static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 1) . ' ' . $units[$i];
    }
}
