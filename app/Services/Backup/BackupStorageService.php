<?php

namespace App\Services\Backup;

use App\Models\AppStorageConfig;
use App\Models\Backup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * طبقة توافق خفيفة — التخزين الفعلي يتم عبر StorageManager + أماكن التخزين العامة.
 */
class BackupStorageService
{
    public function __construct(
        private StorageManager $storageManager
    ) {}

    public function storeBackup(Backup $backup, string $filePath): string
    {
        $this->storageManager->storeWithFailover($backup, $filePath);
        return (string) $backup->fresh()->storage_path;
    }

    public function getBackupFromStorage(Backup $backup): string
    {
        $content = $this->storageManager->retrieve($backup);
        $tempPath = storage_path('app/temp/' . basename($backup->storage_path));

        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        file_put_contents($tempPath, $content);

        return $tempPath;
    }

    public function deleteBackupFromStorage(Backup $backup): bool
    {
        return $this->storageManager->delete($backup);
    }

    public function listBackupsInStorage(string $driver): Collection
    {
        $config = AppStorageConfig::where('driver', $driver)
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->first();

        if (!$config) {
            return collect();
        }

        try {
            $driverInstance = StorageFactory::createFromAppConfig($config);
            return collect($driverInstance->list('backups'));
        } catch (\Exception $e) {
            Log::warning('listBackupsInStorage failed: ' . $e->getMessage());
            return collect();
        }
    }

    public function getLocalStorage()
    {
        return Storage::disk('local');
    }

    public function testStorageConnection(string $driver, array $config): bool
    {
        try {
            $driverInstance = StorageFactory::createFromArray($driver, $config);
            return $driverInstance->testConnection();
        } catch (\Exception $e) {
            return false;
        }
    }
}
