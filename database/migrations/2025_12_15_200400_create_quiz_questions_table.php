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
        // جدول أسئلة الاختبار (Pivot)
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            
            // ترتيب ودرجة السؤال في هذا الاختبار
            $table->integer('order')->default(0)->comment('ترتيب السؤال في الاختبار');
            $table->decimal('points', 8, 2)->comment('درجة السؤال في هذا الاختبار');
            
            // إعدادات خاصة بالسؤال في هذا الاختبار
            $table->boolean('is_required')->default(true)->comment('سؤال إجباري');
            $table->boolean('shuffle_options')->nullable()->comment('خلط الخيارات (null = استخدام إعداد الاختبار)');
            
            $table->timestamps();

            $table->unique(['quiz_id', 'question_id']);
            $table->index(['quiz_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};

