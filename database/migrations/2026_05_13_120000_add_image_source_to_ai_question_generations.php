<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_question_generations', function (Blueprint $table) {
            if (! Schema::hasColumn('ai_question_generations', 'source_image_path')) {
                $table->string('source_image_path', 500)->nullable()->after('source_content');
            }
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE ai_question_generations MODIFY COLUMN source_type ENUM('lesson_content','manual_text','topic','image') NOT NULL DEFAULT 'manual_text'");
        }
    }

    public function down(): void
    {
        Schema::table('ai_question_generations', function (Blueprint $table) {
            if (Schema::hasColumn('ai_question_generations', 'source_image_path')) {
                $table->dropColumn('source_image_path');
            }
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE ai_question_generations MODIFY COLUMN source_type ENUM('lesson_content','manual_text','topic') NOT NULL DEFAULT 'manual_text'");
        }
    }
};
