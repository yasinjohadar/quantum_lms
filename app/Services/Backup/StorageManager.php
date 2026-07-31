<?php

namespace App\Services\Backup;

use App\Models\AppStorageConfig;
use App\Models\Backup;
use App\Models\BackupStorageConfig;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class StorageManager
{
    public const SUPPORTED_DRIVERS = [
        'local', 's3', 'ftp', 'sftp', 'azure', 'digitalocean', 'wasabi', 'backblaze', 'cloudflare_r2',
    ];

    protected StorageAnalyticsService $analyticsService;

    public function __construct(StorageAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    protected function activeAppConfigsQuery()
    {
        return AppStorageConfig::where('is_active', true)
            ->whereIn('driver', self::SUPPORTED_DRIVERS)
            ->orderBy('priority', 'desc');
    }

    /**
     * تخزين مع تفضيل المكان المحدد ثم Auto-failover على أماكن التخزين العامة.
     */
    public function storeWithFailover(Backup $backup, string $filePath): bool
    {
        $configs = $this->resolveTargetAppConfigs($backup);

        if ($configs->isEmpty()) {
            throw new \Exception('لا توجد أماكن تخزين عامة نشطة. أضف مكان تخزين من إعدادات التخزين العامة.');
        }

        $fileContent = file_get_contents($filePath);
        $fileSize = filesize($filePath);

        foreach ($configs as $config) {
            try {
                $driver = StorageFactory::createFromAppConfig($config);

                if ($driver->testConnection()) {
                    $storagePath = 'backups/' . $backup->id . '/' . basename($filePath);

                    if ($driver->store($storagePath, $fileContent)) {
                        $backup->update([
                            'storage_config_id' => $config->id,
                            'storage_driver' => $config->driver,
                            'storage_path' => $storagePath,
                        ]);

                        Log::info("Backup stored successfully to app storage: {$config->name}");
                        return true;
                    }
                }
            } catch (\Exception $e) {
                Log::warning("App storage failed for backup: {$config->name} - {$e->getMessage()}");
                continue;
            }
        }

        throw new \Exception('فشلت جميع أماكن التخزين العامة المتاحة');
    }

    /**
     * تخزين في أماكن متعددة (Redundancy) من التخزين العام فقط.
     */
    public function storeToMultipleStorages(Backup $backup, string $filePath): array
    {
        $configs = $this->activeAppConfigsQuery()
            ->where('redundancy', true)
            ->get();

        if ($configs->isEmpty()) {
            return [];
        }

        $fileContent = file_get_contents($filePath);
        $successfulStorages = [];
        $failedStorages = [];

        foreach ($configs as $config) {
            try {
                $driver = StorageFactory::createFromAppConfig($config);

                if ($driver->testConnection()) {
                    $storagePath = 'backups/' . $backup->id . '/' . basename($filePath);

                    if ($driver->store($storagePath, $fileContent)) {
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
                Log::error("Redundancy app storage failed: {$config->name} - {$e->getMessage()}");
                $failedStorages[] = $config->name;
            }
        }

        return [
            'successful' => $successfulStorages,
            'failed' => $failedStorages,
        ];
    }

    public function retrieve(Backup $backup): string
    {
        $driver = $this->resolveDriverForBackup($backup);
        return $driver->retrieve($backup->storage_path);
    }

    public function delete(Backup $backup): bool
    {
        if (!$backup->storage_path) {
            return true;
        }

        try {
            $driver = $this->resolveDriverForBackup($backup);
            return $driver->delete($backup->storage_path);
        } catch (\Exception $e) {
            Log::warning('Failed to delete backup from storage: ' . $e->getMessage(), [
                'backup_id' => $backup->id,
            ]);
            return false;
        }
    }

    public function healthCheck(): Collection
    {
        $configs = $this->activeAppConfigsQuery()->get();

        return $configs->map(function (AppStorageConfig $config) {
            try {
                $driver = StorageFactory::createFromAppConfig($config);
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

    public function selectBestStorage(): ?AppStorageConfig
    {
        foreach ($this->activeAppConfigsQuery()->get() as $config) {
            try {
                $driver = StorageFactory::createFromAppConfig($config);
                if ($driver->testConnection()) {
                    return $config;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }

    /**
     * ترتيب أماكن التخزين المستهدفة: المحدد أولاً ثم الباقي حسب الأولوية.
     *
     * @return Collection<int, AppStorageConfig>
     */
    protected function resolveTargetAppConfigs(Backup $backup): Collection
    {
        $all = $this->activeAppConfigsQuery()->get();

        if ($backup->storage_config_id) {
            $preferred = $all->firstWhere('id', (int) $backup->storage_config_id);
            if ($preferred) {
                return collect([$preferred])->merge($all->where('id', '!=', $preferred->id))->values();
            }
        }

        if ($backup->storage_driver) {
            $byDriver = $all->where('driver', $backup->storage_driver)->values();
            if ($byDriver->isNotEmpty()) {
                $rest = $all->whereNotIn('id', $byDriver->pluck('id'))->values();
                return $byDriver->merge($rest)->values();
            }
        }

        return $all;
    }

    /**
     * حل سائق النسخة: AppStorageConfig أولاً، ثم توافق قديم مع BackupStorageConfig.
     */
    protected function resolveDriverForBackup(Backup $backup)
    {
        if ($backup->storage_config_id) {
            $appConfig = AppStorageConfig::find($backup->storage_config_id);
            if ($appConfig) {
                return StorageFactory::createFromAppConfig($appConfig);
            }
        }

        if ($backup->storage_driver) {
            $appConfig = AppStorageConfig::where('driver', $backup->storage_driver)
                ->where('is_active', true)
                ->orderBy('priority', 'desc')
                ->first();

            if ($appConfig) {
                return StorageFactory::createFromAppConfig($appConfig);
            }

            // توافق النسخ القديمة المخزّنة عبر backup_storage_configs
            $legacy = BackupStorageConfig::where('driver', $backup->storage_driver)
                ->orderByDesc('is_active')
                ->orderBy('priority', 'desc')
                ->first();

            if ($legacy) {
                return StorageFactory::create($legacy);
            }
        }

        throw new \Exception('تعذر العثور على إعداد التخزين لهذه النسخة الاحتياطية');
    }
}
