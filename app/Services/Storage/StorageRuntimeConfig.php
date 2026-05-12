<?php

namespace App\Services\Storage;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

/**
 * يطبّق إعدادات التخزين من جدول system_settings (مجموعة storage) على config('storage.*')
 * بعد تحميل ملف config/storage.php — دون الحاجة إلى متغيرات .env.
 */
final class StorageRuntimeConfig
{
    private static bool $applied = false;

    public static function resetApplicationCache(): void
    {
        self::$applied = false;
    }

    public static function applyFromDatabase(): void
    {
        if (self::$applied) {
            return;
        }
        self::$applied = true;

        if (! config('storage.runtime_from_database', true)) {
            return;
        }

        try {
            if (! Schema::hasTable('system_settings')) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        $rows = SystemSetting::query()->where('group', 'storage')->get()->keyBy('key');

        $bind = static function (string $settingKey, string $configKey, string $cast = 'string') use ($rows): void {
            $row = $rows->get($settingKey);
            if (! $row) {
                return;
            }
            $raw = $row->attributes['value'] ?? null;
            if ($raw === null) {
                return;
            }
            $allowEmpty = $settingKey === 'storage_default_cloud_disk';
            if ($raw === '' && ! $allowEmpty) {
                return;
            }
            $value = match ($cast) {
                'bool' => filter_var($raw, FILTER_VALIDATE_BOOLEAN),
                'int' => (int) $raw,
                'size_mb' => max(1, (int) $raw) * 1024 * 1024,
                default => (string) $raw,
            };
            Config::set($configKey, $value);
        };

        $bind('storage_driver_mode', 'storage.driver_mode', 'string');
        $bind('storage_cloud_first', 'storage.cloud_first', 'bool');
        $bind('storage_default_cloud_disk', 'storage.default_cloud_disk', 'string');
        $bind('storage_fallback_disk', 'storage.fallback_disk', 'string');
        $bind('storage_media_presigned_urls', 'storage.media_use_presigned_urls', 'bool');
        $bind('storage_sync_queue', 'storage.sync_queue', 'string');
        $bind('storage_sync_retries', 'storage.sync_retries', 'int');
        $bind('storage_sync_backoff_seconds', 'storage.sync_backoff_seconds', 'int');
        $bind('storage_max_upload_size_mb', 'storage.max_file_size', 'size_mb');
        $bind('storage_log_uploads', 'storage.log_uploads', 'bool');
        $bind('storage_log_channel', 'storage.log_channel', 'string');
        $bind('storage_deduplication_enabled', 'storage.deduplication.enabled', 'bool');
        $bind('storage_deduplication_min_size_bytes', 'storage.deduplication.min_size_bytes', 'int');
        $bind('storage_virus_scan_enabled', 'storage.virus_scan_enabled', 'bool');
        $bind('storage_auto_generate_thumbnails', 'storage.auto_generate_thumbnails', 'bool');
        $bind('storage_auto_optimize_images', 'storage.auto_optimize_images', 'bool');
        $bind('storage_retention_days', 'storage.retention_days', 'int');
    }
}
