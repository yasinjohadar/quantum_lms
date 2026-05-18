<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE ai_question_generations MODIFY COLUMN source_type ENUM('lesson_content','manual_text','topic','image','pdf') NOT NULL DEFAULT 'manual_text'");
        }

        Schema::table('ai_question_generations', function (Blueprint $table) {
            if (Schema::hasColumn('ai_question_generations', 'source_content')) {
                $table->longText('source_content')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE ai_question_generations MODIFY COLUMN source_type ENUM('lesson_content','manual_text','topic','image') NOT NULL DEFAULT 'manual_text'");
        }

        Schema::table('ai_question_generations', function (Blueprint $table) {
            if (Schema::hasColumn('ai_question_generations', 'source_content')) {
                $table->text('source_content')->nullable()->change();
            }
        });
    }
};
