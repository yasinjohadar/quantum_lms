<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * حذف نظام التخزين القديم الخاص بالنسخ الاحتياطي (BackupStorageConfig)
     * والذي أصبح مُستبدلاً بالكامل بنظام التخزين العام الموحّد (AppStorageConfig).
     * تشمل هذه الهجرة أيضاً جدول storage_analytics التابع له حصراً (منفصل عن
     * app_storage_analytics الحالي الذي يخدم AppStorageConfig).
     */
    public function up(): void
    {
        Schema::dropIfExists('storage_analytics');
        Schema::dropIfExists('backup_storage_configs');
    }

    /**
     * لا إعادة إنشاء تلقائية — الجداول القديمة استُبدلت بالكامل بـ app_storage_configs
     * وapp_storage_analytics. الرجوع عن هذه الهجرة يتطلب مراجعة يدوية.
     */
    public function down(): void
    {
        //
    }
};
