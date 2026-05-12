<?php

namespace App\Services\Media;

use App\Models\Media;
use App\Models\MediaVariant;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * MediaUrlGenerator
 * 
 * الطبقة الموحدة لتوليد روابط الملفات
 * تدعم: public URLs, signed/temporary URLs, CDN, variants
 */
class MediaUrlGenerator
{
    /**
     * رابط عام للملف (بدون حماية)
     */
    public static function publicUrl(Media $media, ?string $variantName = null): string
    {
        if ($variantName) {
            $variant = $media->getVariant($variantName);
            if ($variant && $variant->is_generated) {
                return self::resolveUrl($variant->disk, $variant->path);
            }
        }

        return self::resolveUrl($media->disk, $media->path);
    }

    /**
     * رابط مؤقت محمي (للملفات الخاصة)
     */
    public static function temporaryUrl(Media $media, \DateTimeInterface $expiry, ?string $variantName = null): ?string
    {
        if ($variantName) {
            $variant = $media->getVariant($variantName);
            if ($variant && $variant->is_generated) {
                return self::generateSignedUrl($variant->disk, $variant->path, $expiry);
            }
        }

        return self::generateSignedUrl($media->disk, $media->path, $expiry);
    }

    /**
     * رابط تحميل مباشر
     */
    public static function downloadUrl(Media $media, ?string $variantName = null): string
    {
        $url = self::publicUrl($media, $variantName);
        
        // إضافة force-download parameter إذا كان CDN يدعم ذلك
        if (config('cdn.force_download_param')) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= $separator . 'download=1';
        }

        return $url;
    }

    /**
     * رابط thumbnail
     */
    public static function thumbnailUrl(Media $media, string $size = 'small'): ?string
    {
        $variantName = match ($size) {
            'tiny' => 'thumbnail',
            'small' => 'small',
            'medium' => 'medium',
            'large' => 'large',
            default => 'thumbnail',
        };

        $variant = $media->getVariant($variantName);
        if ($variant && $variant->is_generated) {
            return self::publicUrl($media, $variantName);
        }

        // Fallback للأصل إذا لم يوجد variant
        if ($media->mime_type && str_starts_with($media->mime_type, 'image/')) {
            return self::publicUrl($media);
        }

        return null;
    }

    /**
     * رابط streaming للفيديو
     */
    public static function streamUrl(Media $media): ?string
    {
        $variant = $media->getVariant('stream');
        if ($variant && $variant->is_generated) {
            return self::publicUrl($media, 'stream');
        }

        return self::publicUrl($media);
    }

    /**
     * رابط preview للمستندات
     */
    public static function previewUrl(Media $media): ?string
    {
        $variant = $media->getVariant('preview');
        if ($variant && $variant->is_generated) {
            return self::publicUrl($media, 'preview');
        }

        return null;
    }

    /**
     * رابط عام أو موقع حسب visibility
     */
    public static function url(Media $media, ?string $variantName = null): string
    {
        if ($media->visibility === 'private') {
            return self::temporaryUrl($media, now()->addHours(1), $variantName)
                ?? self::publicUrl($media, $variantName);
        }

        return self::publicUrl($media, $variantName);
    }

    /**
     * ========================
     * Internal Methods
     * ========================
     */

    private static function resolveUrl(string $disk, string $path): string
    {
        // CDN URL إذا كان متوفراً
        $cdnUrl = self::getCdnUrl($disk, $path);
        if ($cdnUrl) {
            return $cdnUrl;
        }

        try {
            return Storage::disk($disk)->url($path);
        } catch (\Exception $e) {
            Log::warning('MediaUrlGenerator: Failed to resolve URL', [
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return '';
        }
    }

    private static function generateSignedUrl(string $disk, string $path, \DateTimeInterface $expiry): ?string
    {
        try {
            $storageDisk = Storage::disk($disk);
            
            // S3/Cloud supports temporary URLs
            if (method_exists($storageDisk, 'temporaryUrl')) {
                return $storageDisk->temporaryUrl($path, $expiry);
            }

            // Fallback: generate signed route
            return self::generateSignedRoute($path, $expiry);
        } catch (\Exception $e) {
            Log::warning('MediaUrlGenerator: Failed to generate signed URL', [
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private static function generateSignedRoute(string $path, \DateTimeInterface $expiry): string
    {
        $signature = hash_hmac('sha256', $path . $expiry->getTimestamp(), config('app.key'));
        $expires = $expiry->getTimestamp();
        
        return route('media.signed', [
            'path' => base64_encode($path),
            'expires' => $expires,
            'signature' => $signature,
        ], false);
    }

    private static function getCdnUrl(string $disk, string $path): ?string
    {
        $cdnConfig = config('cdn.disks.' . $disk);
        if (!$cdnConfig || empty($cdnConfig['url'])) {
            return null;
        }

        $baseUrl = rtrim($cdnConfig['url'], '/');
        $path = ltrim($path, '/');

        // CDN path prefix إذا كان موجوداً
        $prefix = $cdnConfig['path_prefix'] ?? '';
        if ($prefix) {
            $path = rtrim($prefix, '/') . '/' . $path;
        }

        return $baseUrl . '/' . $path;
    }
}
