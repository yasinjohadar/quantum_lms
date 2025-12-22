<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_question_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('set null');
            $table->foreignId('lesson_id')->nullable()->constrained('lessons')->onDelete('set null');
            $table->enum('source_type', ['lesson_content', 'manual_text', 'topic'])->default('manual_text');
            $table->text('source_content')->nullable()->comment('المحتوى المصدر');
            $table->text('prompt')->nullable()->comment('الـ prompt المستخدم');
            $table->enum('question_type', [
                'single_choice',
                'multiple_choice',
                'true_false',
                'short_answer',
                'essay',
                'matching',
                'ordering',
                'fill_blanks',
                'numerical',
                'drag_drop',
                'mixed'
            ])->default('mixed');
            $table->integer('number_of_questions')->default(5);
            $table->enum('difficulty_level', ['easy', 'medium', 'hard', 'mixed'])->default('mixed');
            $table->foreignId('ai_model_id')->nullable()->constrained('ai_models')->nullOnDelete();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->json('generated_questions')->nullable()->comment('الأسئلة المولدة');
            $table->text('error_message')->nullable();
            $table->integer('tokens_used')->nullable();
            $table->decimal('cost', 10, 6)->nullable();
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('subject_id');
            $table->index('lesson_id');
            $table->index('status');
            $table->index('ai_model_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_question_generations');
    }
};
