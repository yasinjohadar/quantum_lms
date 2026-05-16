<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->boolean('free_join_auto_approve')->nullable()->default(true)->after('is_free_override')
                ->comment('قبول انضمام المادة المجانية تلقائياً؛ null/true = آلي، false = بموافقة الإدارة');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('free_join_auto_approve');
        });
    }
};
