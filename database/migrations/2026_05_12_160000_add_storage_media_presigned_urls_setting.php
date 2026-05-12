<?php

use App\Models\SystemSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $key = 'storage_media_presigned_urls';
        if (! SystemSetting::where('key', $key)->exists()) {
            SystemSetting::set(
                $key,
                '1',
                'boolean',
                'storage',
                'روابط الصور من S3 موقّتة (مفعّل للـ buckets الخاصة). عطّله إذا كان الـ bucket عاماً مع CDN.'
            );
        }
    }

    public function down(): void
    {
        SystemSetting::where('group', 'storage')->where('key', 'storage_media_presigned_urls')->delete();
    }
};
