<?php

namespace App\Services\Storage;

use App\Models\AppStorageConfig;
use App\Models\StorageDiskMapping;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\Filesystem\Filesystem;

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
     * رفع ملف إلى disk محدد
     */
    public function uploadToDisk(string $diskName, UploadedFile $file, string $path): array
    {
        try {
            $disk = $this->getDisk($diskName);
            $stream = fopen($file->getRealPath(), 'r+');
            
            $result = $disk->put($path, $stream, [
                'visibility' => 'public',
                'ContentType' => $file->getMimeType(),
            ]);
            
            if (is_resource($stream)) {
                fclose($stream);
            }
            
            if ($result) {
                return [
                    'success' => true,
                    'path' => $path,
                    'url' => $disk->url($path),
                    'storage_type' => $this->getStorageType($diskName),
                ];
            }
            
            return ['success' => false, 'error' => 'Upload returned false'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * رفع إلى اللوكال
     */
    public function uploadToLocal(UploadedFile $file, string $path): array
    {
        try {
            $disk = Storage::disk('public');
            $stream = fopen($file->getRealPath(), 'r+');
            
            $result = $disk->put($path, $stream, ['visibility' => 'public']);
            
            if (is_resource($stream)) {
                fclose($stream);
            }
            
            if ($result) {
                return [
                    'success' => true,
                    'path' => $path,
                    'url' => $disk->url($path),
                    'disk' => 'public',
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
        
        // حذف من الـ disk المحدد أو المحلول
        $targetDisk = $diskName ?? $this->resolveDiskForPath($path);
        try {
            $disk = $this->getDisk($targetDisk);
            if ($disk->exists($path)) {
                $deleted = $disk->delete($path) && $deleted;
            }
        } catch (\Exception $e) {
            Log::warning("Failed to delete from cloud: {$e->getMessage()}");
        }
        
        // حذف من اللوكال أيضاً
        try {
            $localDisk = Storage::disk('public');
            if ($localDisk->exists($path)) {
                $localDisk->delete($path);
            }
        } catch (\Exception $e) {
            Log::warning("Failed to delete from local: {$e->getMessage()}");
        }
        
        return $deleted;
    }

    /**
     * الحصول على URL للملف (يفضل السحابة)
     */
    public function url(string $path, ?string $diskName = null): string
    {
        $targetDisk = $diskName ?? $this->resolveDiskForPath($path);
        
        // محاولة السحابة أولاً
        try {
            $disk = $this->getDisk($targetDisk);
            if ($disk->exists($path)) {
                return $disk->url($path);
            }
        } catch (\Exception $e) {
            // Fallback للوكال
        }
        
        // Fallback للوكال
        try {
            return Storage::disk('public')->url($path);
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
        
        try {
            $disk = $this->getDisk($targetDisk);
            if ($disk->exists($path)) {
                return true;
            }
        } catch (\Exception $e) {
            //
        }
        
        return Storage::disk('public')->exists($path);
    }

    /**
     * نسخ ملف من لوكال إلى سحابة
     */
    public function syncToCloud(string $localPath, string $targetDisk): array
    {
        try {
            $localDisk = Storage::disk('public');
            
            if (!$localDisk->exists($localPath)) {
                return ['success' => false, 'error' => 'Local file not found'];
            }
            
            $content = $localDisk->get($localPath);
            $cloudDisk = $this->getDisk($targetDisk);
            
            $result = $cloudDisk->put($localPath, $content, ['visibility' => 'public']);
            
            if ($result) {
                return [
                    'success' => true,
                    'path' => $localPath,
                    'url' => $cloudDisk->url($localPath),
                ];
            }
            
            return ['success' => false, 'error' => 'Cloud upload failed'];
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
            $cloudDisk = $this->getDisk($sourceDisk);
            
            if (!$cloudDisk->exists($cloudPath)) {
                return ['success' => false, 'error' => 'Cloud file not found'];
            }
            
            $content = $cloudDisk->get($cloudPath);
            $localDisk = Storage::disk('public');
            
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
        
        try {
            $disk = $this->getDisk($targetDisk);
            $result = $disk->put($path, $content, [
                'visibility' => 'public',
                'ContentType' => $mimeType,
            ]);
            
            if ($result) {
                return [
                    'success' => true,
                    'path' => $path,
                    'url' => $disk->url($path),
                    'storage_type' => $this->getStorageType($targetDisk),
                ];
            }
        } catch (\Exception $e) {
            Log::warning("Cloud content upload failed: {$e->getMessage()}");
        }
        
        // Fallback للوكال
        try {
            $localDisk = Storage::disk('public');
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
     * تحديد الـ disk المناسب بناءً على المسار
     */
    private function resolveDiskForPath(string $path): string
    {
        foreach (self::LOCAL_PATHS as $prefix => $diskName) {
            if (str_starts_with($path, $prefix)) {
                return $diskName;
            }
        }
        
        return 'images'; // Default
    }

    /**
     * الحصول على disk (سحابي أو لوكال)
     */
    private function getDisk(string $diskName): Filesystem
    {
        $mapping = StorageDiskMapping::where('disk_name', $diskName)
            ->where('is_active', true)
            ->with('primaryStorage')
            ->first();
        
        if (!$mapping || !$mapping->primaryStorage || !$mapping->primaryStorage->is_active) {
            throw new \Exception("No active cloud storage configured for disk: {$diskName}");
        }
        
        return AppStorageFactory::create($mapping->primaryStorage);
    }

    /**
     * توليد اسم ملف فريد
     */
    private function generateFileName(UploadedFile $file): string
    {
        return uniqid('file_', true) . '.' . $file->getClientOriginalExtension();
    }

    /**
     * تحديد نوع التخزين
     */
    private function getStorageType(string $diskName): string
    {
        $mapping = StorageDiskMapping::where('disk_name', $diskName)
            ->where('is_active', true)
            ->with('primaryStorage')
            ->first();
        
        return $mapping?->primaryStorage?->driver ?? 'unknown';
    }

    /**
     * تسجيل ملف للمزامنة لاحقاً
     */
    private function queueForSync(string $path, string $targetDisk): void
    {
        try {
            StorageSyncJob::dispatch($path, $targetDisk)->onQueue('storage-sync');
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
        $localDisk = Storage::disk('public');
        
        foreach (self::LOCAL_PATHS as $prefix => $diskName) {
            $mapping = StorageDiskMapping::where('disk_name', $diskName)
                ->where('is_active', true)
                ->exists();
            
            if (!$mapping) continue;
            
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
