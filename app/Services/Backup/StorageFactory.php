<?php

namespace App\Services\Backup;

use App\Contracts\BackupStorageInterface;
use App\Models\AppStorageConfig;
use App\Models\BackupStorageConfig;
use App\Services\Backup\StorageDrivers\LocalStorageDriver;
use App\Services\Backup\StorageDrivers\S3StorageDriver;
use App\Services\Backup\StorageDrivers\AzureStorageDriver;
use App\Services\Backup\StorageDrivers\FTPStorageDriver;
use App\Services\Backup\StorageDrivers\DigitalOceanStorageDriver;
use App\Services\Backup\StorageDrivers\WasabiStorageDriver;
use App\Services\Backup\StorageDrivers\BackblazeStorageDriver;
use App\Services\Backup\StorageDrivers\CloudflareR2StorageDriver;
use App\Services\Storage\StorageConfigNormalizer;

class StorageFactory
{
    /**
     * إنشاء سائق نسخ من مكان تخزين عام (قراءة فقط من AppStorageConfig).
     */
    public static function createFromAppConfig(AppStorageConfig $config): BackupStorageInterface
    {
        $driverConfig = StorageConfigNormalizer::normalize(
            $config->getDecryptedConfig(),
            $config->driver
        );

        // جذر محلي فارغ لأن المسار يُبنى مسبقاً ببادئة backups/ من StorageManager
        if ($config->driver === 'local') {
            $driverConfig['path'] = '';
        }

        return self::createFromArray($config->driver, $driverConfig);
    }

    /**
     * توافق قديم مع BackupStorageConfig.
     */
    public static function create(BackupStorageConfig $config): BackupStorageInterface
    {
        $driverConfig = $config->getDecryptedConfig();

        return self::createFromArray($config->driver, $driverConfig);
    }

    public static function createFromArray(string $driver, array $config): BackupStorageInterface
    {
        return match ($driver) {
            'local' => new LocalStorageDriver($config),
            's3' => new S3StorageDriver($config),
            'azure' => new AzureStorageDriver($config),
            'ftp', 'sftp' => new FTPStorageDriver($config),
            'digitalocean' => new DigitalOceanStorageDriver($config),
            'wasabi' => new WasabiStorageDriver($config),
            'backblaze' => new BackblazeStorageDriver($config),
            'cloudflare_r2' => new CloudflareR2StorageDriver($config),
            default => throw new \Exception("نوع التخزين غير مدعوم للنسخ الاحتياطي: {$driver}"),
        };
    }
}
