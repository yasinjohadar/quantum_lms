<?php

namespace App\Helpers;

use App\Services\Storage\AppStorageManager;
use Illuminate\Contracts\Filesystem\Filesystem;

class StorageHelper
{
    /**
     * الحصول على disk
     */
    public static function disk(string $diskName): Filesystem
    {
        return app(AppStorageManager::class)->getDisk($diskName);
    }

    /**
     * تخزين ملف
     */
    public static function store(string $disk, string $path, $content, ?string $fileType = null): bool
    {
        return app(AppStorageManager::class)->store($disk, $path, $content, $fileType);
    }

    /**
     * تخزين مع Auto-failover
     */
    public static function storeWithFailover(string $disk, string $path, $content, ?string $fileType = null): bool
    {
        return app(AppStorageManager::class)->storeWithFailover($disk, $path, $content, $fileType);
    }

    /**
     * تخزين في أماكن متعددة
     */
    public static function storeToMultiple(string $disk, string $path, $content, ?string $fileType = null): array
    {
        return app(AppStorageManager::class)->storeToMultiple($disk, $path, $content, $fileType);
    }

    /**
     * استرجاع ملف
     */
    public static function retrieve(string $disk, string $path): string
    {
        return app(AppStorageManager::class)->retrieve($disk, $path);
    }

    /**
     * حذف ملف
     */
    public static function delete(string $disk, string $path): bool
    {
        return app(AppStorageManager::class)->delete($disk, $path);
    }

    /**
     * الحصول على URL
     */
    public static function url(string $disk, string $path): string
    {
        return app(AppStorageManager::class)->url($disk, $path);
    }
}

