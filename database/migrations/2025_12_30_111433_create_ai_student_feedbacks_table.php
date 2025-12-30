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
        Schema::create('ai_student_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('quiz_attempt_id')->nullable()->constrained('quiz_attempts')->nullOnDelete();
            $table->enum('feedback_type', ['performance', 'general', 'improvement'])->default('general');
            $table->text('feedback_text');
            $table->json('suggestions')->nullable();
            $table->foreignId('ai_model_id')->nullable()->constrained('ai_models')->nullOnDelete();
            $table->integer('tokens_used')->nullable();
            $table->decimal('cost', 10, 6)->nullable();
            $table->timestamps();

            $table->index('student_id');
            $table->index('quiz_attempt_id');
            $table->index('feedback_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_student_feedbacks');
    }
};
