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
        Schema::table('ai_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_messages', 'quick_action')) {
                $table->string('quick_action')->nullable()->after('metadata')->comment('Quick action المستخدم');
            }
            if (!Schema::hasColumn('ai_messages', 'is_bookmarked')) {
                $table->boolean('is_bookmarked')->default(false)->after('quick_action');
            }

            if (Schema::hasColumn('ai_messages', 'quick_action')) {
                $table->index('quick_action');
            }
            if (Schema::hasColumn('ai_messages', 'is_bookmarked')) {
                $table->index('is_bookmarked');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_messages', function (Blueprint $table) {
            if (Schema::hasColumn('ai_messages', 'quick_action')) {
                $table->dropIndex(['quick_action']);
                $table->dropColumn('quick_action');
            }
            if (Schema::hasColumn('ai_messages', 'is_bookmarked')) {
                $table->dropIndex(['is_bookmarked']);
                $table->dropColumn('is_bookmarked');
            }
        });
    }
};
