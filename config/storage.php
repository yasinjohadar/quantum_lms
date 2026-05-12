<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Runtime overlay from database
    |--------------------------------------------------------------------------
    |
    | When true, StorageServiceProvider merges system_settings rows (group: storage)
    | into this config at boot. Manage values under: الإعدادات → مجموعة «التخزين».
    |
    */
    'runtime_from_database' => true,

    /*
    |--------------------------------------------------------------------------
    | Storage Driver Mode
    |--------------------------------------------------------------------------
    |
    | Default only — overridden by system_settings.storage_driver_mode when present.
    | Supported: local_only, cloud_only, cloud_first, local_first, dual_write
    |
    */
    'driver_mode' => 'cloud_first',

    /*
    |--------------------------------------------------------------------------
    | Cloud-First Storage (legacy, maps to driver_mode)
    |--------------------------------------------------------------------------
    */
    'cloud_first' => true,

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Disk
    |--------------------------------------------------------------------------
    |
    | Optional logical disk name in storage_disk_mappings (fallback lookup).
    | Overridden by system_settings.storage_default_cloud_disk (may be empty).
    |
    */
    'default_cloud_disk' => '',

    /*
    |--------------------------------------------------------------------------
    | Fallback Local Disk
    |--------------------------------------------------------------------------
    */
    'fallback_disk' => 'public',

    /*
    |--------------------------------------------------------------------------
    | Media URLs (S3-compatible)
    |--------------------------------------------------------------------------
    |
    | عند true: روابط الصور/الملفات من السحابة تُولَّد كـ temporary signed URLs
    | (ضروري إذا كان الـ bucket يمنع القراءة العامة بدون توقيع).
    | عند false: يُستخدم الرابط العام من الـ disk (مناسب لـ bucket + CDN عامّين).
    |
    */
    'media_use_presigned_urls' => true,

    /*
    |--------------------------------------------------------------------------
    | Upload Settings
    |--------------------------------------------------------------------------
    */
    'max_file_size' => 500 * 1024 * 1024,

    'allowed_mimes' => [
        'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'],
        'video' => ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'],
        'document' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'archive' => ['application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed'],
        'audio' => ['audio/mpeg', 'audio/mp4', 'audio/ogg', 'audio/wav'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Deduplication
    |--------------------------------------------------------------------------
    */
    'deduplication' => [
        'enabled' => true,
        'min_size_bytes' => 10240,
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Settings
    |--------------------------------------------------------------------------
    */
    'sync_queue' => 'storage-sync',
    'sync_retries' => 3,
    'sync_backoff_seconds' => 30,

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */
    'log_uploads' => true,
    'log_channel' => 'daily',

    /*
    |--------------------------------------------------------------------------
    | Disk Mapping (logical name -> cloud disk name)
    |--------------------------------------------------------------------------
    */
    'disk_map' => [
        'avatars' => 'images',
        'images' => 'images',
        'videos' => 'videos',
        'attachments' => 'attachments',
        'library' => 'library',
        'receipts' => 'documents',
        'documents' => 'documents',
    ],

    /*
    |--------------------------------------------------------------------------
    | Media Processing
    |--------------------------------------------------------------------------
    */
    'virus_scan_enabled' => false,
    'auto_generate_thumbnails' => true,
    'auto_optimize_images' => true,

    /*
    |--------------------------------------------------------------------------
    | Retention (days for soft-deleted files)
    |--------------------------------------------------------------------------
    */
    'retention_days' => 30,
];
