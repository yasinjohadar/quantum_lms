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
        Schema::create('question_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->foreignId('lesson_id')->nullable()->constrained('lessons')->nullOnDelete()->comment('الدرس المرتبط به السؤال');
            
            // رقم المحاولة
            $table->integer('attempt_number')->default(1);
            
            // التوقيت
            $table->dateTime('started_at');
            $table->dateTime('finished_at')->nullable();
            $table->integer('time_spent')->default(0)->comment('الوقت المستغرق بالثواني');
            $table->dateTime('last_activity_at')->nullable()->comment('آخر نشاط');
            
            // الوقت المحدد للإجابة (بالثواني)
            $table->integer('time_limit')->nullable()->comment('الوقت المحدد للإجابة (null = غير محدود)');
            
            // الحالة
            $table->enum('status', [
                'in_progress',  // جاري
                'completed',    // مكتمل
                'abandoned',    // متروك
                'timed_out',    // انتهى الوقت
            ])->default('in_progress');
            
            // الدرجات
            $table->decimal('score', 10, 2)->default(0)->comment('الدرجة المحصلة');
            $table->decimal('max_score', 10, 2)->default(0)->comment('الدرجة الكاملة');
            $table->boolean('is_correct')->nullable()->comment('هل الإجابة صحيحة (null = غير مصحح)');
            
            // معلومات الجهاز
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'question_id', 'status']);
            $table->index(['question_id', 'status']);
            $table->index(['lesson_id']);
            $table->unique(['user_id', 'question_id', 'attempt_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_attempts');
    }
};
