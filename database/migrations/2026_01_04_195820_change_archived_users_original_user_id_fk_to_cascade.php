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
        // Drop the existing foreign key constraint
        Schema::table('archived_users', function (Blueprint $table) {
            $table->dropForeign(['original_user_id']);
        });

        // Re-add the foreign key with cascade delete
        Schema::table('archived_users', function (Blueprint $table) {
            $table->foreign('original_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the cascade foreign key
        Schema::table('archived_users', function (Blueprint $table) {
            $table->dropForeign(['original_user_id']);
        });

        // Re-add the restrict foreign key
        Schema::table('archived_users', function (Blueprint $table) {
            $table->foreign('original_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');
        });
    }
};

