<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // إضافة عمود photo إذا لم يكن موجوداً
            if (!Schema::hasColumn('users', 'photo')) {
                $table->string('photo')->nullable()->after('avatar');
            }
            
            // إضافة عمود created_by إذا لم يكن موجوداً
            if (!Schema::hasColumn('users', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('is_connected')
                    ->constrained('users')->nullOnDelete()
                    ->comment('المستخدم الذي أنشأ هذا الحساب');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // حذف عمود photo
            if (Schema::hasColumn('users', 'photo')) {
                $table->dropColumn('photo');
            }
            
            // حذف عمود created_by
            if (Schema::hasColumn('users', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });
    }
};
