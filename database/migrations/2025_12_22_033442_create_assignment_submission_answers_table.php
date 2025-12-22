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
        Schema::create('assignment_submission_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('assignment_submissions')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('assignment_questions')->onDelete('cascade');
            
            // إجابة الطالب
            $table->json('answer')->nullable()->comment('إجابة الطالب');
            
            // نتيجة التصحيح التلقائي
            $table->boolean('is_correct')->nullable()->comment('هل الإجابة صحيحة');
            $table->decimal('points_earned', 8, 2)->default(0)->comment('الدرجة المكتسبة');
            $table->dateTime('auto_graded_at')->nullable()->comment('تاريخ التصحيح التلقائي');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['submission_id', 'question_id']);
            $table->unique(['submission_id', 'question_id'], 'unique_submission_answer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_submission_answers');
    }
};
