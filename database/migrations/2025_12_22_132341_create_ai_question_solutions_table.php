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
        Schema::create('ai_question_solutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->foreignId('ai_model_id')->nullable()->constrained('ai_models')->nullOnDelete();
            $table->text('solution')->comment('الحل المولّد');
            $table->text('explanation')->nullable()->comment('الشرح');
            $table->decimal('confidence_score', 3, 2)->nullable()->comment('درجة الثقة (0-1)');
            $table->integer('tokens_used')->nullable();
            $table->decimal('cost', 10, 6)->nullable();
            $table->boolean('is_verified')->default(false)->comment('تم التحقق منه من قبل معلم');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('question_id');
            $table->index('ai_model_id');
            $table->index('is_verified');
            $table->unique(['question_id', 'ai_model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_question_solutions');
    }
};
