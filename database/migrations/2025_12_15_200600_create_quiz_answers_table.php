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
        // جدول إجابات الطلاب
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('quiz_attempts')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            
            // الإجابة
            $table->text('answer')->nullable()->comment('الإجابة (JSON للمرونة)');
            $table->text('answer_text')->nullable()->comment('نص الإجابة للمقالي');
            
            // للأسئلة متعددة الخيارات
            $table->text('selected_options')->nullable()->comment('الخيارات المحددة (JSON)');
            
            // للمطابقة
            $table->text('matching_pairs')->nullable()->comment('أزواج المطابقة (JSON)');
            
            // للترتيب
            $table->text('ordering')->nullable()->comment('الترتيب المختار (JSON)');
            
            // للإجابة الرقمية
            $table->decimal('numeric_answer', 15, 6)->nullable();
            
            // التقييم
            $table->boolean('is_correct')->nullable()->comment('صحيحة/خاطئة (null = غير مصحح)');
            $table->boolean('is_partially_correct')->default(false)->comment('صحيحة جزئياً');
            $table->decimal('points_earned', 8, 2)->default(0)->comment('الدرجة المكتسبة');
            $table->decimal('max_points', 8, 2)->default(0)->comment('أقصى درجة للسؤال');
            
            // للتصحيح اليدوي
            $table->text('feedback')->nullable()->comment('ملاحظات المصحح');
            $table->boolean('needs_manual_grading')->default(false)->comment('يحتاج تصحيح يدوي');
            $table->boolean('is_graded')->default(false)->comment('تم تصحيحه');
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('graded_at')->nullable();
            
            // التوقيت
            $table->dateTime('answered_at')->nullable();
            $table->integer('time_spent')->default(0)->comment('الوقت المستغرق على السؤال (ثواني)');
            
            // ترتيب الخيارات المعروضة (للخلط)
            $table->text('options_order')->nullable()->comment('ترتيب الخيارات المعروض (JSON)');
            
            $table->timestamps();

            $table->unique(['attempt_id', 'question_id']);
            $table->index(['attempt_id', 'is_graded']);
            $table->index('needs_manual_grading');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_answers');
    }
};

