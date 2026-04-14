<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->foreignId('section_id')
                ->nullable()
                ->after('unit_id')
                ->constrained('subject_sections')
                ->nullOnDelete();
            $table->index(['section_id', 'order'], 'lessons_section_order_index');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->foreignId('section_id')
                ->nullable()
                ->after('unit_id')
                ->constrained('subject_sections')
                ->nullOnDelete();
            $table->index(['section_id', 'order'], 'quizzes_section_order_index');
        });

        DB::statement('
            UPDATE lessons l
            JOIN units u ON u.id = l.unit_id
            SET l.section_id = u.section_id
            WHERE l.unit_id IS NOT NULL AND l.section_id IS NULL
        ');

        DB::statement('
            UPDATE quizzes q
            JOIN units u ON u.id = q.unit_id
            SET q.section_id = u.section_id
            WHERE q.unit_id IS NOT NULL AND q.section_id IS NULL
        ');

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->change();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable(false)->change();
            $table->foreign('unit_id')->references('id')->on('units')->cascadeOnDelete();
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropIndex('quizzes_section_order_index');
            $table->dropForeign(['section_id']);
            $table->dropColumn('section_id');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropIndex('lessons_section_order_index');
            $table->dropForeign(['section_id']);
            $table->dropColumn('section_id');
        });
    }
};

