<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subject_sections', function (Blueprint $table) {
            if (! Schema::hasColumn('subject_sections', 'sync_group_id')) {
                $table->uuid('sync_group_id')->nullable()->after('subject_id')->index();
            }
            if (! Schema::hasColumn('subject_sections', 'is_sync_canonical')) {
                $table->boolean('is_sync_canonical')->default(true)->after('sync_group_id');
            }
            if (! Schema::hasColumn('subject_sections', 'cloned_from_section_id')) {
                $table->foreignId('cloned_from_section_id')
                    ->nullable()
                    ->after('is_sync_canonical')
                    ->constrained('subject_sections')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('subject_sections', function (Blueprint $table) {
            if (Schema::hasColumn('subject_sections', 'cloned_from_section_id')) {
                $table->dropForeign(['cloned_from_section_id']);
                $table->dropColumn('cloned_from_section_id');
            }
            if (Schema::hasColumn('subject_sections', 'is_sync_canonical')) {
                $table->dropColumn('is_sync_canonical');
            }
            if (Schema::hasColumn('subject_sections', 'sync_group_id')) {
                $table->dropColumn('sync_group_id');
            }
        });
    }
};
