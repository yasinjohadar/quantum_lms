<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_experiences', function (Blueprint $table) {
            $table->foreignId('lesson_id')
                ->nullable()
                ->after('unit_id')
                ->constrained('lessons')
                ->nullOnDelete();

            $table->index(['unit_id', 'lesson_id'], 'le_unit_lesson_idx');
        });
    }

    public function down(): void
    {
        Schema::table('learning_experiences', function (Blueprint $table) {
            $table->dropIndex('le_unit_lesson_idx');
            $table->dropConstrainedForeignId('lesson_id');
        });
    }
};
