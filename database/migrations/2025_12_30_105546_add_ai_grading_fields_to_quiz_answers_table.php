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
        Schema::table('quiz_answers', function (Blueprint $table) {
            $table->boolean('ai_graded')->default(false)->after('graded_at')->comment('هل تم التصحيح بواسطة AI');
            $table->json('ai_grading_data')->nullable()->after('ai_graded')->comment('بيانات التصحيح من AI (الدرجة المقترحة، التعليقات، المعايير)');
            $table->timestamp('ai_graded_at')->nullable()->after('ai_grading_data')->comment('تاريخ ووقت التصحيح بواسطة AI');
            $table->foreignId('ai_grading_model_id')->nullable()->after('ai_graded_at')->constrained('ai_models')->nullOnDelete()->comment('الموديل المستخدم للتصحيح');

            $table->index('ai_graded');
            $table->index('ai_grading_model_id');
        });

        Schema::table('question_answers', function (Blueprint $table) {
            $table->boolean('ai_graded')->default(false)->after('graded_at')->comment('هل تم التصحيح بواسطة AI');
            $table->json('ai_grading_data')->nullable()->after('ai_graded')->comment('بيانات التصحيح من AI (الدرجة المقترحة، التعليقات، المعايير)');
            $table->timestamp('ai_graded_at')->nullable()->after('ai_grading_data')->comment('تاريخ ووقت التصحيح بواسطة AI');
            $table->foreignId('ai_grading_model_id')->nullable()->after('ai_graded_at')->constrained('ai_models')->nullOnDelete()->comment('الموديل المستخدم للتصحيح');

            $table->index('ai_graded');
            $table->index('ai_grading_model_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_answers', function (Blueprint $table) {
            $table->dropForeign(['ai_grading_model_id']);
            $table->dropIndex(['ai_grading_model_id']);
            $table->dropIndex(['ai_graded']);
            $table->dropColumn(['ai_graded', 'ai_grading_data', 'ai_graded_at', 'ai_grading_model_id']);
        });

        Schema::table('question_answers', function (Blueprint $table) {
            $table->dropForeign(['ai_grading_model_id']);
            $table->dropIndex(['ai_grading_model_id']);
            $table->dropIndex(['ai_graded']);
            $table->dropColumn(['ai_graded', 'ai_grading_data', 'ai_graded_at', 'ai_grading_model_id']);
        });
    }
};
