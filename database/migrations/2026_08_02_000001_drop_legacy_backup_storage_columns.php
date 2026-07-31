<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إنهاء تنظيف المسار المزدوج (dual-path): بعد أن أكملت الهجرتان السابقتان
     * تعبئة storage_config_id لكل الصفوف، لم تعد الحاجة لعمود storage_driver
     * النصي القديم — التخزين مرجعه الوحيد الآن هو storage_config_id → AppStorageConfig.
     */
    public function up(): void
    {
        // أي صف لا يزال بلا storage_config_id (لم يُحل في هجرة التعبئة) يُستبعد
        // من قيد NOT NULL بحذفه فقط إن كان بلا أي أثر تخزين فعلي (احتياط نظري
        // بحت — لا صفوف من هذا النوع في أي بيئة تم التحقق منها).
        DB::table('backups')
            ->whereNull('storage_config_id')
            ->whereNull('storage_path')
            ->delete();

        Schema::table('backups', function (Blueprint $table) {
            $table->dropColumn('storage_driver');
        });

        // doctrine/dbal غير مثبَّت في هذا المشروع، لذا Schema::change() غير متاحة —
        // نستخدم SQL خام مباشرةً لتحويل storage_config_id إلى NOT NULL.
        DB::statement('ALTER TABLE backups MODIFY storage_config_id BIGINT UNSIGNED NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE backups MODIFY storage_config_id BIGINT UNSIGNED NULL');

        Schema::table('backups', function (Blueprint $table) {
            $table->string('storage_driver')->nullable()->after('backup_type');
        });
    }
};
