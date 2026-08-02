<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_experiences', function (Blueprint $table) {
            $table->foreignId('subject_id')
                ->nullable()
                ->after('created_by')
                ->constrained('subjects')
                ->nullOnDelete();

            $table->foreignId('unit_id')
                ->nullable()
                ->after('subject_id')
                ->constrained('units')
                ->nullOnDelete();

            $table->index(['subject_id', 'unit_id'], 'le_subject_unit_idx');
        });
    }

    public function down(): void
    {
        Schema::table('learning_experiences', function (Blueprint $table) {
            $table->dropIndex('le_subject_unit_idx');
            $table->dropConstrainedForeignId('unit_id');
            $table->dropConstrainedForeignId('subject_id');
        });
    }
};
