<?php

namespace App\Services\Storage;

class StorageConfigNormalizer
{
    /**
     * الحقول التي يجب أن تكون boolean في إعدادات S3
     */
    private const S3_BOOLEAN_FIELDS = [
        'use_path_style_endpoint',
        'use_path_style',
        'use_accelerate_endpoint',
        'use_dual_stack_endpoint',
        'throw',
    ];

    /**
     * الحقول التي يجب أن تكون boolean في إعدادات FTP
     */
    private const FTP_BOOLEAN_FIELDS = [
        'passive',
        'use_tls',
        'ssl',
    ];

    /**
     * تطبيع إعدادات التخزين بالكامل
     */
    public static function normalize(array $config, string $driver): array
    {
        return match ($driver) {
            's3', 'digitalocean', 'wasabi', 'backblaze', 'cloudflare_r2' => self::normalizeS3($config),
            'ftp', 'sftp' => self::normalizeFTP($config),
            default => $config,
        };
    }

    /**
     * تطبيع إعدادات S3-compatible
     */
    public static function normalizeS3(array $config): array
    {
        // إضافة https:// للـ endpoint إذا لم يكن موجوداً
        if (isset($config['endpoint']) && !str_starts_with($config['endpoint'], 'http://') && !str_starts_with($config['endpoint'], 'https://')) {
            $config['endpoint'] = 'https://' . ltrim($config['endpoint'], '/');
        }

        foreach (self::S3_BOOLEAN_FIELDS as $field) {
            if (array_key_exists($field, $config)) {
                $config[$field] = self::toBool($config[$field]);
            }
        }

        return $config;
    }

    /**
     * تطبيع إعدادات FTP/SFTP
     */
    public static function normalizeFTP(array $config): array
    {
        foreach (self::FTP_BOOLEAN_FIELDS as $field) {
            if (array_key_exists($field, $config)) {
                $config[$field] = self::toBool($config[$field]);
            }
        }

        return $config;
    }

    /**
     * تحويل قيمة إلى boolean حقيقي
     * يدعم: "1", "0", "true", "false", "yes", "no", true, false, 1, 0
     */
    public static function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        if (is_string($value)) {
            $lower = strtolower(trim($value));
            return in_array($lower, ['1', 'true', 'yes', 'on'], true);
        }

        return (bool) $value;
    }
}
