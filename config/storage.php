<?php

use App\Enums\StorageDriverMode;

return [
    /*
    |--------------------------------------------------------------------------
    | Storage Driver Mode
    |--------------------------------------------------------------------------
    |
    | Controls how files are stored across local and cloud providers.
    |
    | Supported: local_only, cloud_only, cloud_first, local_first, dual_write
    */
    'driver_mode' => env('STORAGE_DRIVER_MODE', 'cloud_first'),

    /*
    |--------------------------------------------------------------------------
    | Cloud-First Storage (legacy, maps to driver_mode)
    |--------------------------------------------------------------------------
    */
    'cloud_first' => env('USE_CLOUD_FIRST_STORAGE', true),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Disk
    |--------------------------------------------------------------------------
    */
    'default_cloud_disk' => env('DEFAULT_CLOUD_DISK', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Fallback Local Disk
    |--------------------------------------------------------------------------
    */
    'fallback_disk' => env('STORAGE_FALLBACK_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Upload Settings
    |--------------------------------------------------------------------------
    */
    'max_file_size' => (int) env('MAX_UPLOAD_SIZE_MB', 500) * 1024 * 1024,

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
        'enabled' => env('STORAGE_DEDUPLICATION', true),
        'min_size_bytes' => (int) env('STORAGE_DEDUPLICATION_MIN_SIZE', 10240),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Settings
    |--------------------------------------------------------------------------
    */
    'sync_queue' => env('STORAGE_SYNC_QUEUE', 'storage-sync'),
    'sync_retries' => (int) env('STORAGE_SYNC_RETRIES', 3),
    'sync_backoff_seconds' => (int) env('STORAGE_SYNC_BACKOFF', 30),

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */
    'log_uploads' => env('STORAGE_LOG_UPLOADS', true),
    'log_channel' => env('STORAGE_LOG_CHANNEL', 'daily'),

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
    'virus_scan_enabled' => env('MEDIA_VIRUS_SCAN', false),
    'auto_generate_thumbnails' => env('MEDIA_AUTO_THUMBNAILS', true),
    'auto_optimize_images' => env('MEDIA_AUTO_OPTIMIZE', true),

    /*
    |--------------------------------------------------------------------------
    | Retention (days for soft-deleted files)
    |--------------------------------------------------------------------------
    */
    'retention_days' => (int) env('MEDIA_RETENTION_DAYS', 30),
];
