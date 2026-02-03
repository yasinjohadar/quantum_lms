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
        if (!Schema::hasColumn('subject_sections', 'parent_id')) {
            Schema::table('subject_sections', function (Blueprint $table) {
                $table->foreignId('parent_id')
                    ->nullable()
                    ->after('subject_id')
                    ->constrained('subject_sections')
                    ->onDelete('cascade');
                $table->index('parent_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('subject_sections', 'parent_id')) {
            Schema::table('subject_sections', function (Blueprint $table) {
                $table->dropForeign(['parent_id']);
                $table->dropIndex(['parent_id']);
                $table->dropColumn('parent_id');
            });
        }
    }
};
