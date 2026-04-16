<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('whatsapp_messages')) {
            return;
        }

        Schema::table('whatsapp_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('whatsapp_messages', 'message_category')) {
                $table->string('message_category')
                    ->default('system')
                    ->after('status')
                    ->comment('verification | system');
                $table->index('message_category');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('whatsapp_messages')) {
            return;
        }

        Schema::table('whatsapp_messages', function (Blueprint $table) {
            if (Schema::hasColumn('whatsapp_messages', 'message_category')) {
                $table->dropIndex(['message_category']);
                $table->dropColumn('message_category');
            }
        });
    }
};

