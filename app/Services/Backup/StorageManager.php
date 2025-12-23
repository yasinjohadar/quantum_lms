<?php

namespace App\Services\Backup;

use App\Contracts\BackupStorageInterface;
use App\Models\Backup;
use App\Models\BackupStorageConfig;
use App\Services\Backup\StorageFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class StorageManager
{
    protected StorageAnalyticsService $analyticsService;

    public function __construct(StorageAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * تخزين مع Auto-failover
     */
    public function storeWithFailover(Backup $backup, string $filePath): bool
    {
        $configs = BackupStorageConfig::where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();

        $fileContent = file_get_contents($filePath);
        $fileSize = filesize($filePath);

        foreach ($configs as $config) {
            try {
                $driver = StorageFactory::create($config);
                
                if ($driver->testConnection()) {
                    $storagePath = 'backups/' . $backup->id . '/' . basename($filePath);
                    
                    if ($driver->store($storagePath, $fileContent)) {
                        // تتبع الإحصائيات
                        $this->analyticsService->trackStorageUsage($config, $fileSize);
                        $this->analyticsService->trackBandwidth($config, 'upload', $fileSize);
                        
                        // تحديث backup record
                        $backup->update([
                            'storage_driver' => $config->driver,
                            'storage_path' => $storagePath,
                        ]);

                        Log::info("Backup stored successfully to: {$config->name}");
                        return true;
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Storage failed: {$config->name} - {$e->getMessage()}");
                continue;
            }
        }

        throw new \Exception('All storage options failed');
    }

    /**
     * تخزين في أماكن متعددة (Redundancy)
     */
    public function storeToMultipleStorages(Backup $backup, string $filePath): array
    {
        $configs = BackupStorageConfig::where('is_active', true)
            ->where('redundancy', true)
            ->orderBy('priority', 'desc')
            ->get();

        if ($configs->isEmpty()) {
            return [];
        }

        $fileContent = file_get_contents($filePath);
        $fileSize = filesize($filePath);
        $successfulStorages = [];
        $failedStorages = [];

        foreach ($configs as $config) {
            try {
                $driver = StorageFactory::create($config);
                
                if ($driver->testConnection()) {
                    $storagePath = 'backups/' . $backup->id . '/' . basename($filePath);
                    
                    if ($driver->store($storagePath, $fileContent)) {
                        $this->analyticsService->trackStorageUsage($config, $fileSize);
                        $this->analyticsService->trackBandwidth($config, 'upload', $fileSize);
                        
                        $successfulStorages[] = [
                            'config' => $config,
                            'path' => $storagePath,
                        ];
                    } else {
                        $failedStorages[] = $config->name;
                    }
                } else {
                    $failedStorages[] = $config->name;
                }
            } catch (\Exception $e) {
                Log::error("Redundancy storage failed: {$config->name} - {$e->getMessage()}");
                $failedStorages[] = $config->name;
            }
        }

        return [
            'successful' => $successfulStorages,
            'failed' => $failedStorages,
        ];
    }

    /**
     * استرجاع من التخزين
     */
    public function retrieve(Backup $backup): string
    {
        $config = BackupStorageConfig::where('driver', $backup->storage_driver)
            ->where('is_active', true)
            ->first();

        if (!$config) {
            throw new \Exception("Storage config not found for driver: {$backup->storage_driver}");
        }

        $driver = StorageFactory::create($config);
        $content = $driver->retrieve($backup->storage_path);
        
        // تتبع النطاق الترددي
        $this->analyticsService->trackBandwidth($config, 'download', strlen($content));

        return $content;
    }

    /**
     * حذف من التخزين
     */
    public function delete(Backup $backup): bool
    {
        $config = BackupStorageConfig::where('driver', $backup->storage_driver)
            ->where('is_active', true)
            ->first();

        if (!$config) {
            return false;
        }

        $driver = StorageFactory::create($config);
        return $driver->delete($backup->storage_path);
    }

    /**
     * Health Check لجميع أماكن التخزين
     */
    public function healthCheck(): Collection
    {
        $configs = BackupStorageConfig::where('is_active', true)->get();
        
        return $configs->map(function ($config) {
            try {
                $driver = StorageFactory::create($config);
                $isHealthy = $driver->testConnection();
                
                return [
                    'config' => $config,
                    'healthy' => $isHealthy,
                    'available_space' => $driver->getAvailableSpace(),
                ];
            } catch (\Exception $e) {
                return [
                    'config' => $config,
                    'healthy' => false,
                    'error' => $e->getMessage(),
                ];
            }
        });
    }

    /**
     * Load Balancing - اختيار أفضل مكان تخزين
     */
    public function selectBestStorage(): ?BackupStorageConfig
    {
        $configs = BackupStorageConfig::where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();

        foreach ($configs as $config) {
            try {
                $driver = StorageFactory::create($config);
                if ($driver->testConnection()) {
                    return $config;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }
}

