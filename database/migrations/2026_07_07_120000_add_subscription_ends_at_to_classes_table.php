<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->timestamp('subscription_ends_at')
                ->nullable()
                ->after('free_join_auto_approve')
                ->comment('تاريخ نهاية اشتراك الصف لجميع الطلاب');
            $table->timestamp('subscription_revoked_at')
                ->nullable()
                ->after('subscription_ends_at')
                ->comment('تاريخ تنفيذ الإلغاء الجماعي لاشتراكات الصف');
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn(['subscription_ends_at', 'subscription_revoked_at']);
        });
    }
};
