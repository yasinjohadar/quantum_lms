<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            if (! Schema::hasColumn('units', 'sync_group_id')) {
                $table->uuid('sync_group_id')->nullable()->after('section_id')->index();
            }
            if (! Schema::hasColumn('units', 'is_sync_canonical')) {
                $table->boolean('is_sync_canonical')->default(true)->after('sync_group_id');
            }
            if (! Schema::hasColumn('units', 'cloned_from_unit_id')) {
                $table->foreignId('cloned_from_unit_id')
                    ->nullable()
                    ->after('is_sync_canonical')
                    ->constrained('units')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            if (Schema::hasColumn('units', 'cloned_from_unit_id')) {
                $table->dropForeign(['cloned_from_unit_id']);
                $table->dropColumn('cloned_from_unit_id');
            }
            if (Schema::hasColumn('units', 'is_sync_canonical')) {
                $table->dropColumn('is_sync_canonical');
            }
            if (Schema::hasColumn('units', 'sync_group_id')) {
                $table->dropColumn('sync_group_id');
            }
        });
    }
};
