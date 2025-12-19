<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // تعديل enum لإضافة حالة 'pending'
        DB::statement("ALTER TABLE enrollments MODIFY COLUMN status ENUM('active', 'suspended', 'completed', 'pending') DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // إزالة حالة 'pending' من enum
        // أولاً: تحديث جميع السجلات التي لديها 'pending' إلى 'active'
        DB::table('enrollments')
            ->where('status', 'pending')
            ->update(['status' => 'active']);
        
        // ثانياً: تعديل enum لإزالة 'pending'
        DB::statement("ALTER TABLE enrollments MODIFY COLUMN status ENUM('active', 'suspended', 'completed') DEFAULT 'active'");
    }
};
