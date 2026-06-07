<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('section_subjects')) {
            return;
        }

        $foreignKeys = collect(DB::select(
            "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = 'section_subjects'
             AND COLUMN_NAME = 'section_id'
             AND REFERENCED_TABLE_NAME IS NOT NULL"
        ))->pluck('CONSTRAINT_NAME');

        Schema::table('section_subjects', function (Blueprint $table) use ($foreignKeys) {
            foreach ($foreignKeys as $foreignKey) {
                $table->dropForeign($foreignKey);
            }
        });

        Schema::table('section_subjects', function (Blueprint $table) {
            $table->unsignedBigInteger('section_id')->nullable()->change();
        });

        Schema::table('section_subjects', function (Blueprint $table) {
            $table->foreign('section_id')
                ->references('id')
                ->on('subject_sections')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('section_subjects')) {
            return;
        }

        $foreignKeys = collect(DB::select(
            "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = 'section_subjects'
             AND COLUMN_NAME = 'section_id'
             AND REFERENCED_TABLE_NAME IS NOT NULL"
        ))->pluck('CONSTRAINT_NAME');

        Schema::table('section_subjects', function (Blueprint $table) use ($foreignKeys) {
            foreach ($foreignKeys as $foreignKey) {
                $table->dropForeign($foreignKey);
            }
        });

        Schema::table('section_subjects', function (Blueprint $table) {
            $table->unsignedBigInteger('section_id')->nullable(false)->change();
        });

        Schema::table('section_subjects', function (Blueprint $table) {
            $table->foreign('section_id')
                ->references('id')
                ->on('subject_sections')
                ->cascadeOnDelete();
        });
    }
};
