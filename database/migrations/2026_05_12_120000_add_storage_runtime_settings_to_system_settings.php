<?php

use App\Models\SystemSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            ['storage_driver_mode', 'cloud_first', 'string', 'وضع التخزين: local_only, cloud_only, cloud_first, local_first, dual_write'],
            ['storage_cloud_first', '1', 'boolean', 'توافق قديم: يُستخدم عند كون وضع التخزين غير صالح'],
            ['storage_default_cloud_disk', '', 'string', 'اسم قرص احتياطي في storage_disk_mappings عندما لا يوجد ربط باسم القرص المنطقي (اتركه فارغاً لتعطيل الاحتياط)'],
            ['storage_fallback_disk', 'public', 'string', 'قرص Laravel للملفات المحلية الاحتياطية'],
            ['storage_sync_queue', 'storage-sync', 'string', 'اسم طابور مزامنة التخزين'],
            ['storage_sync_retries', '3', 'integer', 'عدد إعادات محاولة المزامنة'],
            ['storage_sync_backoff_seconds', '30', 'integer', 'تأخير إعادة المحاولة (ثانية)'],
            ['storage_max_upload_size_mb', '500', 'integer', 'أقصى حجم رفع للملفات (ميغابايت)'],
            ['storage_log_uploads', '1', 'boolean', 'تسجيل عمليات الرفع'],
            ['storage_log_channel', 'daily', 'string', 'قناة السجل'],
            ['storage_deduplication_enabled', '1', 'boolean', 'تفعيل منع تكرار الملفات'],
            ['storage_deduplication_min_size_bytes', '10240', 'integer', 'أدنى حجم (بايت) لدخول الملف في فحص التكرار'],
            ['storage_virus_scan_enabled', '0', 'boolean', 'فحص فيروسات (إن وُجد مزود)'],
            ['storage_auto_generate_thumbnails', '1', 'boolean', 'توليد مصغّرات تلقائياً'],
            ['storage_auto_optimize_images', '1', 'boolean', 'تحسين الصور تلقائياً'],
            ['storage_retention_days', '30', 'integer', 'أيام الاحتفاظ بالملفات المحذوفة ناعماً'],
        ];

        foreach ($defaults as [$key, $value, $type, $description]) {
            if (! SystemSetting::where('key', $key)->exists()) {
                SystemSetting::set($key, $value, $type, 'storage', $description);
            }
        }
    }

    public function down(): void
    {
        SystemSetting::where('group', 'storage')->whereIn('key', [
            'storage_driver_mode',
            'storage_cloud_first',
            'storage_default_cloud_disk',
            'storage_fallback_disk',
            'storage_sync_queue',
            'storage_sync_retries',
            'storage_sync_backoff_seconds',
            'storage_max_upload_size_mb',
            'storage_log_uploads',
            'storage_log_channel',
            'storage_deduplication_enabled',
            'storage_deduplication_min_size_bytes',
            'storage_virus_scan_enabled',
            'storage_auto_generate_thumbnails',
            'storage_auto_optimize_images',
            'storage_retention_days',
        ])->delete();
    }
};
