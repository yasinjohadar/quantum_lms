<?php

namespace App\Services\Storage;

use App\Models\AppStorageConfig;
use App\Models\StorageDiskMapping;
use App\Services\Storage\AppStorageFactory;
use App\Services\Storage\AppStorageAnalyticsService;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\Filesystem;

class AppStorageManager
{
    protected AppStorageAnalyticsService $analyticsService;
    protected AuditLogService $auditLogService;

    public function __construct(AppStorageAnalyticsService $analyticsService, AuditLogService $auditLogService)
    {
        $this->analyticsService = $analyticsService;
        $this->auditLogService = $auditLogService;
    }

    /**
     * الحصول على disk
     */
    public function getDisk(string $diskName): Filesystem
    {
        $mapping = StorageDiskMapping::where('disk_name', $diskName)
            ->where('is_active', true)
            ->first();

        if (!$mapping || !$mapping->primaryStorage) {
            // Fallback to default public disk
            return Storage::disk('public');
        }

        try {
            return AppStorageFactory::create($mapping->primaryStorage);
        } catch (\Exception $e) {
            Log::error("Failed to create disk {$diskName}: " . $e->getMessage());
            return Storage::disk('public');
        }
    }

    /**
     * تخزين ملف
     */
    public function store(string $disk, string $path, $content, ?string $fileType = null): bool
    {
        try {
            $storage = $this->getDisk($disk);
            $result = $storage->put($path, $content);
            
            if ($result) {
                $this->trackStorage($disk, $path, $content, $fileType, 'upload');
            }
            
            return $result !== false;
        } catch (\Exception $e) {
            Log::error("Storage store failed for disk {$disk}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * تخزين مع Auto-failover
     */
    public function storeWithFailover(string $disk, string $path, $content, ?string $fileType = null): bool
    {
        $mapping = StorageDiskMapping::where('disk_name', $disk)
            ->where('is_active', true)
            ->first();

        if (!$mapping) {
            return $this->store($disk, $path, $content, $fileType);
        }

        // محاولة التخزين الأساسي
        try {
            $primaryStorage = AppStorageFactory::create($mapping->primaryStorage);
            if ($primaryStorage->put($path, $content)) {
                $this->trackStorage($disk, $path, $content, $fileType, 'upload', $mapping->primaryStorage);
                return true;
            }
        } catch (\Exception $e) {
            Log::warning("Primary storage failed for disk {$disk}: " . $e->getMessage());

            // تسجيل فشل التخزين الأساسي في سجل التدقيق
            $this->auditLogService->log(
                null,
                'storage_primary_failed',
                'فشل التخزين الأساسي',
                [
                    'disk' => $disk,
                    'storage' => $mapping->primaryStorage?->name,
                    'error' => $e->getMessage(),
                ]
            );
        }

        // محاولة Fallback storages
        $fallbackStorages = $mapping->getFallbackStorages();
        foreach ($fallbackStorages as $fallbackStorage) {
            try {
                $storage = AppStorageFactory::create($fallbackStorage);
                if ($storage->put($path, $content)) {
                    $this->trackStorage($disk, $path, $content, $fileType, 'upload', $fallbackStorage);
                    Log::info("Used fallback storage for disk {$disk}: {$fallbackStorage->name}");

                    // تنبيه في AuditLog عند استخدام تخزين احتياطي
                    $this->auditLogService->log(
                        null,
                        'storage_failover_used',
                        'استخدام تخزين احتياطي',
                        [
                            'disk' => $disk,
                            'fallback_storage' => $fallbackStorage->name,
                        ]
                    );

                    return true;
                }
            } catch (\Exception $e) {
                Log::warning("Fallback storage failed: {$fallbackStorage->name} - " . $e->getMessage());
                continue;
            }
        }

        throw new \Exception("All storage options failed for disk: {$disk}");
    }

    /**
     * تخزين في أماكن متعددة (Redundancy)
     */
    public function storeToMultiple(string $disk, string $path, $content, ?string $fileType = null): array
    {
        $mapping = StorageDiskMapping::where('disk_name', $disk)
            ->where('is_active', true)
            ->first();

        if (!$mapping) {
            return [];
        }

        $storages = collect([$mapping->primaryStorage])
            ->merge($mapping->getFallbackStorages())
            ->filter(function($storage) {
                return $storage->redundancy && $storage->is_active;
            });

        $successful = [];
        $failed = [];

        foreach ($storages as $storage) {
            try {
                $storageDisk = AppStorageFactory::create($storage);
                if ($storageDisk->put($path, $content)) {
                    $this->trackStorage($disk, $path, $content, $fileType, 'upload', $storage);
                    $successful[] = $storage->name;
                } else {
                    $failed[] = $storage->name;
                }
            } catch (\Exception $e) {
                Log::error("Redundancy storage failed: {$storage->name} - " . $e->getMessage());
                $failed[] = $storage->name;
            }
        }

        return [
            'successful' => $successful,
            'failed' => $failed,
        ];
    }

    /**
     * استرجاع ملف
     */
    public function retrieve(string $disk, string $path): string
    {
        try {
            $storage = $this->getDisk($disk);
            $content = $storage->get($path);
            
            $this->trackStorage($disk, $path, $content, null, 'download');
            
            return $content;
        } catch (\Exception $e) {
            Log::error("Storage retrieve failed for disk {$disk}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * حذف ملف
     */
    public function delete(string $disk, string $path): bool
    {
        try {
            $storage = $this->getDisk($disk);
            return $storage->delete($path);
        } catch (\Exception $e) {
            Log::error("Storage delete failed for disk {$disk}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * الحصول على URL
     */
    public function url(string $disk, string $path): string
    {
        try {
            $storage = $this->getDisk($disk);
            return $storage->url($path);
        } catch (\Exception $e) {
            Log::error("Storage URL failed for disk {$disk}: " . $e->getMessage());
            return '';
        }
    }

    /**
     * تتبع التخزين
     */
    private function trackStorage(string $disk, string $path, $content, ?string $fileType, string $operation, ?AppStorageConfig $storage = null): void
    {
        if (!$storage) {
            $mapping = StorageDiskMapping::where('disk_name', $disk)->first();
            if ($mapping) {
                $storage = $mapping->primaryStorage;
            }
        }

        if ($storage) {
            $bytes = is_string($content) ? strlen($content) : (is_resource($content) ? 0 : filesize($content));
            
            if ($operation === 'upload') {
                $this->analyticsService->trackStorageUsage($storage, $bytes, $fileType);
                $this->analyticsService->trackBandwidth($storage, 'upload', $bytes, $fileType);
            } elseif ($operation === 'download') {
                $this->analyticsService->trackBandwidth($storage, 'download', $bytes, $fileType);
            }
        }
    }
}

