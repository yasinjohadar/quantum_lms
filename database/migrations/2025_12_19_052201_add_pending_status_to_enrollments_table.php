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
        // MODIFY COLUMN صيغة خاصة بـ MySQL — لا مكافئ لها في sqlite (تُستخدم في بيئة
        // الاختبارات via RefreshDatabase). العمود الأصلي أُنشئ عبر enum() القابل للنقل،
        // وsqlite لا يفرض enum فعلياً (نص عادي) فتخطّي هذه الخطوة عليه غير ضار.
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE enrollments MODIFY COLUMN status ENUM('active', 'suspended', 'completed', 'pending') DEFAULT 'active'");
        }
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

        // ثانياً: تعديل enum لإزالة 'pending' (MySQL فقط — انظر ملاحظة up() أعلاه)
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE enrollments MODIFY COLUMN status ENUM('active', 'suspended', 'completed') DEFAULT 'active'");
        }
    }
};