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
        Schema::table('ai_conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_conversations', 'settings')) {
                $table->json('settings')->nullable()->after('is_active')->comment('إعدادات المحادثة: {temperature, max_tokens, mode, etc}');
            }
            if (!Schema::hasColumn('ai_conversations', 'context_history')) {
                $table->json('context_history')->nullable()->after('settings')->comment('تاريخ تغييرات السياق');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_conversations', function (Blueprint $table) {
            if (Schema::hasColumn('ai_conversations', 'settings')) {
                $table->dropColumn('settings');
            }
            if (Schema::hasColumn('ai_conversations', 'context_history')) {
                $table->dropColumn('context_history');
            }
        });
    }
};
