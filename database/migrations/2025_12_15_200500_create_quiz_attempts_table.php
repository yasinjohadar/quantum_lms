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
        // جدول محاولات الاختبار
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // رقم المحاولة
            $table->integer('attempt_number')->default(1);
            
            // التوقيت
            $table->dateTime('started_at');
            $table->dateTime('finished_at')->nullable();
            $table->integer('time_spent')->default(0)->comment('الوقت المستغرق بالثواني');
            $table->dateTime('last_activity_at')->nullable()->comment('آخر نشاط');
            
            // الدرجات
            $table->decimal('score', 10, 2)->default(0)->comment('الدرجة المحصلة');
            $table->decimal('max_score', 10, 2)->default(0)->comment('الدرجة الكاملة');
            $table->decimal('percentage', 5, 2)->default(0)->comment('النسبة المئوية');
            $table->boolean('passed')->default(false)->comment('هل نجح');
            
            // الحالة
            $table->enum('status', [
                'in_progress',  // جاري
                'completed',    // مكتمل
                'abandoned',    // متروك
                'timed_out',    // انتهى الوقت
                'under_review', // قيد المراجعة (للمقالي)
            ])->default('in_progress');
            
            // معلومات إضافية
            $table->integer('questions_answered')->default(0)->comment('عدد الأسئلة المجابة');
            $table->integer('questions_correct')->default(0)->comment('عدد الإجابات الصحيحة');
            $table->integer('questions_wrong')->default(0)->comment('عدد الإجابات الخاطئة');
            $table->integer('questions_skipped')->default(0)->comment('عدد الأسئلة المتروكة');
            
            // ترتيب الأسئلة المعروضة (للخلط)
            $table->text('question_order')->nullable()->comment('ترتيب الأسئلة (JSON)');
            
            // معلومات الجهاز
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            
            // ملاحظات المصحح
            $table->text('grader_notes')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('graded_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['quiz_id', 'user_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->unique(['quiz_id', 'user_id', 'attempt_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};

