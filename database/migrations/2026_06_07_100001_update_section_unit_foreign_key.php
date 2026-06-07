<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('section_unit')) {
            return;
        }

        foreach (['unit_id', 'subject_section_id'] as $column) {
            $foreignKeys = collect(DB::select(
                "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE()
                 AND TABLE_NAME = 'section_unit'
                 AND COLUMN_NAME = ?
                 AND REFERENCED_TABLE_NAME IS NOT NULL",
                [$column]
            ))->pluck('CONSTRAINT_NAME');

            if ($foreignKeys->isEmpty()) {
                continue;
            }

            Schema::table('section_unit', function (Blueprint $table) use ($foreignKeys) {
                foreach ($foreignKeys as $foreignKey) {
                    $table->dropForeign($foreignKey);
                }
            });
        }

        Schema::table('section_unit', function (Blueprint $table) {
            if (Schema::hasColumn('section_unit', 'unit_id')) {
                $table->unsignedBigInteger('unit_id')->nullable()->change();
            }
            if (Schema::hasColumn('section_unit', 'subject_section_id')) {
                $table->unsignedBigInteger('subject_section_id')->nullable()->change();
            }
        });

        Schema::table('section_unit', function (Blueprint $table) {
            $table->foreign('unit_id')
                ->references('id')
                ->on('units')
                ->nullOnDelete();
            $table->foreign('subject_section_id')
                ->references('id')
                ->on('subject_sections')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('section_unit')) {
            return;
        }

        foreach (['unit_id', 'subject_section_id'] as $column) {
            $foreignKeys = collect(DB::select(
                "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE()
                 AND TABLE_NAME = 'section_unit'
                 AND COLUMN_NAME = ?
                 AND REFERENCED_TABLE_NAME IS NOT NULL",
                [$column]
            ))->pluck('CONSTRAINT_NAME');

            if ($foreignKeys->isEmpty()) {
                continue;
            }

            Schema::table('section_unit', function (Blueprint $table) use ($foreignKeys) {
                foreach ($foreignKeys as $foreignKey) {
                    $table->dropForeign($foreignKey);
                }
            });
        }

        Schema::table('section_unit', function (Blueprint $table) {
            if (Schema::hasColumn('section_unit', 'unit_id')) {
                $table->unsignedBigInteger('unit_id')->nullable(false)->change();
            }
            if (Schema::hasColumn('section_unit', 'subject_section_id')) {
                $table->unsignedBigInteger('subject_section_id')->nullable(false)->change();
            }
        });

        Schema::table('section_unit', function (Blueprint $table) {
            $table->foreign('unit_id')
                ->references('id')
                ->on('units')
                ->cascadeOnDelete();
            $table->foreign('subject_section_id')
                ->references('id')
                ->on('subject_sections')
                ->cascadeOnDelete();
        });
    }
};
