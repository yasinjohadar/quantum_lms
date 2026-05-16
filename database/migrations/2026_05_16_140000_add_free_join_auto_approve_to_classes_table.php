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
        Schema::table('classes', function (Blueprint $table) {
            $table->boolean('free_join_auto_approve')
                ->default(true)
                ->after('allow_subjects_purchase')
                ->comment('عند الانضمام بدون رسوم: قبول الطلب فوراً (true) أو طلب مراجعة إدارية (false)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn('free_join_auto_approve');
        });
    }
};
