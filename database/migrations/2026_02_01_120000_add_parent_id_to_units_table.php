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
        Schema::table('units', function (Blueprint $table) {
            if (!Schema::hasColumn('units', 'parent_id')) {
                $table->foreignId('parent_id')
                    ->nullable()
                    ->after('section_id')
                    ->constrained('units')
                    ->onDelete('cascade');
                $table->index('parent_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('units', 'parent_id')) {
            Schema::table('units', function (Blueprint $table) {
                $table->dropForeign(['parent_id']);
                $table->dropIndex(['parent_id']);
            });
        }
    }
};
