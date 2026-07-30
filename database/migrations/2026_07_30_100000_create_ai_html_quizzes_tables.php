<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_html_quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 32)->default('draft')->index();
            $table->json('prompt_meta')->nullable();
            $table->longText('bundle_html')->nullable();
            $table->longText('bundle_css')->nullable();
            $table->longText('bundle_js')->nullable();
            $table->json('answer_key_json')->nullable();
            $table->string('schema_version', 32)->default('html-quiz-1.0');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('ai_html_quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_html_quiz_id')->constrained('ai_html_quizzes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('total')->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->unsignedInteger('duration')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->json('answers_json')->nullable();
            $table->json('result_json')->nullable();
            $table->timestamps();

            $table->index(['ai_html_quiz_id', 'user_id'], 'ai_html_quiz_attempts_quiz_user_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_html_quiz_attempts');
        Schema::dropIfExists('ai_html_quizzes');
    }
};
