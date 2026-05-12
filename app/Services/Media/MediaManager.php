<?php

namespace App\Services\Media;

use App\Models\Media;
use App\Models\MediaVariant;
use App\Models\MediaUsage;
use App\Models\MediaConversion;
use App\Jobs\Media\GenerateThumbnailJob;
use App\Jobs\Media\OptimizeImageJob;
use App\Jobs\Media\VideoTranscodeJob;
use App\Jobs\Media\VirusScanJob;
use App\Services\Media\MediaUrlGenerator;
use App\Services\Media\Actions\GenerateUploadUrlAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * MediaManager
 * 
 * الطبقة الرئيسية لإدارة الملفات
 * توفر API موحد لـ: attach, detach, replace, sync, delete, variants
 */
class MediaManager
{
    /**
     * ربط ملف بنموذج
     */
    public function attach(UploadedFile $file, Model $model, string $collection = 'default', ?string $field = null, array $options = []): Media
    {
        return DB::transaction(function () use ($file, $model, $collection, $field, $options) {
            // رفع الملف
            $uploadResult = \App\Services\Storage\MediaStorageService::upload(
                $file,
                $options['directory'] ?? 'media',
                $options['category'] ?? null,
                $options['custom_name'] ?? null,
                ($options['private'] ?? false)
            );

            if (!$uploadResult['success']) {
                throw new \Exception('Failed to upload file: ' . ($uploadResult['error'] ?? 'Unknown error'));
            }

            // إنشاء Media record
            $media = Media::create([
                'disk' => $uploadResult['disk'],
                'path' => $uploadResult['path'],
                'provider' => $uploadResult['storage_provider'] ?? 'local',
                'visibility' => $options['private'] ? 'private' : 'public',
                'mime_type' => $file->getMimeType(),
                'extension' => $file->getClientOriginalExtension(),
                'size' => $file->getSize(),
                'checksum' => $uploadResult['checksum'],
                'uploaded_by' => auth()->id(),
                'is_synced' => !$uploadResult['needs_sync'],
                'sync_status' => $uploadResult['needs_sync'] ? 'pending' : 'completed',
                'metadata' => [
                    'original_name' => $file->getClientOriginalName(),
                    'upload_method' => 'direct',
                ],
            ]);

            // تسجيل الاستخدام
            MediaUsage::attach($media, $model, $collection, $field, $options['context'] ?? null);

            // جدولة المعالجة
            $this->scheduleProcessing($media, $options);

            return $media;
        });
    }

    /**
     * ربط ملف موجود بنموذج
     */
    public function attachExisting(Media $media, Model $model, string $collection = 'default', ?string $field = null, ?string $context = null): MediaUsage
    {
        return MediaUsage::attach($media, $model, $collection, $field, $context);
    }

    /**
     * فصل ملف عن نموذج
     */
    public function detach(Media $media, Model $model, string $collection = 'default', ?string $field = null): bool
    {
        return MediaUsage::detach($media, $model, $collection, $field);
    }

    /**
     * فصل جميع الاستخدامات
     */
    public function detachAll(Media $media): int
    {
        return MediaUsage::detachAll($media);
    }

    /**
     * استبدال ملف
     */
    public function replace(Media $oldMedia, UploadedFile $newFile, Model $model, string $collection = 'default', ?string $field = null): Media
    {
        return DB::transaction(function () use ($oldMedia, $newFile, $model, $collection, $field) {
            // رفع الجديد
            $newMedia = $this->attach($newFile, $model, $collection, $field);

            // فصل القديم
            $this->detach($oldMedia, $model, $collection, $field);

            // حذف القديم إذا لم يعد مستخدماً
            if ($oldMedia->isOrphaned()) {
                $this->delete($oldMedia);
            }

            return $newMedia;
        });
    }

    /**
     * حذف ملف (distributed delete)
     */
    public function delete(Media $media, bool $force = false): bool
    {
        if (!$force && !$media->isOrphaned()) {
            Log::warning('MediaManager: Cannot delete media with active references', [
                'media_id' => $media->id,
                'reference_count' => $media->reference_count,
            ]);
            return false;
        }

        return DB::transaction(function () use ($media) {
            // حذف variants
            foreach ($media->variants as $variant) {
                $this->deleteVariant($variant);
            }

            // حذف من التخزين
            try {
                \App\Services\Storage\MediaStorageService::delete($media->path);
            } catch (\Exception $e) {
                Log::error('MediaManager: Failed to delete from storage', [
                    'media_id' => $media->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // soft delete
            $media->softDelete();

            return true;
        });
    }

    /**
     * حذف نهائي
     */
    public function forceDelete(Media $media): bool
    {
        return $this->delete($media, true);
    }

    /**
     * استعادة ملف محذوف
     */
    public function restore(Media $media): bool
    {
        if (!$media->isDeleted()) {
            return false;
        }

        $media->restore();
        return true;
    }

    /**
     * إنشاء variant
     */
    public function generateVariant(Media $media, string $name, array $config = []): MediaVariant
    {
        $variant = $media->variants()->firstOrCreate(
            ['name' => $name],
            [
                'disk' => $media->disk,
                'path' => $media->path . '/variants/' . $name,
                'conversion_params' => $config,
                'is_generated' => false,
            ]
        );

        // جدولة التحويل
        MediaConversion::create([
            'media_id' => $media->id,
            'type' => 'variant_' . $name,
            'status' => MediaConversion::STATUS_PENDING,
            'config' => $config,
        ]);

        return $variant;
    }

    /**
     * الحصول على URL
     */
    public function url(Media $media, ?string $variantName = null): string
    {
        return MediaUrlGenerator::url($media, $variantName);
    }

    /**
     * الحصول على URL مؤقت
     */
    public function temporaryUrl(Media $media, \DateTimeInterface $expiry, ?string $variantName = null): ?string
    {
        return MediaUrlGenerator::temporaryUrl($media, $expiry, $variantName);
    }

    /**
     * توليد presigned upload URL
     */
    public function generateUploadUrl(string $fileName, string $mimeType, int $maxSize, string $disk = 's3', array $options = []): array
    {
        return app(GenerateUploadUrlAction::class)->execute($fileName, $mimeType, $maxSize, $disk, $options);
    }

    /**
     * جدولة معالجة الملف
     */
    private function scheduleProcessing(Media $media, array $options = []): void
    {
        $mime = $media->mime_type;

        // صور
        if ($mime && str_starts_with($mime, 'image/')) {
            if ($options['generate_thumbnails'] ?? true) {
                dispatch(new GenerateThumbnailJob($media, 'thumbnail', 150, 150));
                dispatch(new GenerateThumbnailJob($media, 'small', 300, 300));
                dispatch(new GenerateThumbnailJob($media, 'medium', 600, 600));
            }

            if ($options['optimize'] ?? true) {
                dispatch(new OptimizeImageJob($media));
            }
        }

        // فيديو
        if ($mime && str_starts_with($mime, 'video/')) {
            dispatch(new VideoTranscodeJob($media));
        }

        // فحص أمان
        if ($options['virus_scan'] ?? config('media.virus_scan_enabled', false)) {
            dispatch(new VirusScanJob($media));
        }
    }

    /**
     * حذف variant
     */
    private function deleteVariant(MediaVariant $variant): void
    {
        try {
            \Illuminate\Support\Facades\Storage::disk($variant->disk)->delete($variant->path);
        } catch (\Exception $e) {
            Log::warning('MediaManager: Failed to delete variant', [
                'variant_id' => $variant->id,
                'error' => $e->getMessage(),
            ]);
        }
        $variant->delete();
    }
}
