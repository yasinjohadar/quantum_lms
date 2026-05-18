<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_question_generations', function (Blueprint $table) {
            $table->timestamp('questions_saved_at')->nullable()->after('generated_questions');
            $table->text('ai_response_preview')->nullable()->after('prompt');
        });
    }

    public function down(): void
    {
        Schema::table('ai_question_generations', function (Blueprint $table) {
            $table->dropColumn(['questions_saved_at', 'ai_response_preview']);
        });
    }
};
