<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->timestamp('access_revoked_at')
                ->nullable()
                ->after('expires_at')
                ->comment('تاريخ إلغاء الوصول تلقائياً بعد انتهاء الصلاحية');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('access_revoked_at');
        });
    }
};
