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
        // جدول خيارات الأسئلة
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            
            // محتوى الخيار
            $table->text('content')->comment('نص الخيار');
            $table->string('image')->nullable()->comment('صورة للخيار');
            
            // للاختيار من متعدد وصح/خطأ
            $table->boolean('is_correct')->default(false)->comment('هل الخيار صحيح');
            $table->decimal('points', 8, 2)->nullable()->comment('درجة جزئية للخيار');
            
            // للمطابقة
            $table->text('match_target')->nullable()->comment('الهدف المطابق');
            
            // للترتيب
            $table->integer('correct_order')->nullable()->comment('الترتيب الصحيح');
            
            // ترتيب العرض
            $table->integer('order')->default(0);
            
            // ملاحظات للخيار
            $table->text('feedback')->nullable()->comment('ملاحظة عند اختيار هذا الخيار');
            
            $table->timestamps();

            $table->index(['question_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_options');
    }
};

