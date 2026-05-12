<?php

namespace App\Services\Storage;

use App\Enums\StorageDriverMode;

/**
 * Resolves effective storage mode from config (driver_mode + legacy cloud_first flag).
 */
final class StorageDriverModeResolver
{
    public static function current(): StorageDriverMode
    {
        $raw = config('storage.driver_mode');
        if (is_string($raw)) {
            $normalized = strtolower(trim($raw));
            if ($normalized !== '' && ($mode = StorageDriverMode::tryFrom($normalized)) !== null) {
                return $mode;
            }
        }

        return config('storage.cloud_first', true)
            ? StorageDriverMode::CLOUD_FIRST
            : StorageDriverMode::LOCAL_ONLY;
    }
}
