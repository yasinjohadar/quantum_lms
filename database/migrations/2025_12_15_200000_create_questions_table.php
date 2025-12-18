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
        // جدول الأسئلة (بنك الأسئلة)
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            
            // نوع السؤال
            $table->enum('type', [
                'single_choice',    // اختيار واحد
                'multiple_choice',  // اختيار متعدد
                'true_false',       // صح/خطأ
                'short_answer',     // إجابة قصيرة
                'essay',            // مقالي
                'matching',         // مطابقة
                'ordering',         // ترتيب
                'fill_blanks',      // ملء الفراغات
                'numerical',        // رقمي
                'drag_drop',        // سحب وإفلات
            ])->default('single_choice');
            
            // محتوى السؤال
            $table->string('title')->comment('عنوان/نص السؤال المختصر');
            $table->text('content')->nullable()->comment('محتوى السؤال الكامل (يدعم HTML)');
            $table->text('explanation')->nullable()->comment('شرح الإجابة الصحيحة');
            $table->string('image')->nullable()->comment('صورة مرفقة بالسؤال');
            
            // إعدادات السؤال
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium')->comment('مستوى الصعوبة');
            $table->decimal('default_points', 8, 2)->default(1)->comment('الدرجة الافتراضية');
            $table->boolean('case_sensitive')->default(false)->comment('حساسية حالة الأحرف للإجابات النصية');
            $table->decimal('tolerance', 8, 4)->nullable()->comment('نسبة التسامح للأسئلة الرقمية');
            
            // للأسئلة من نوع ملء الفراغات
            $table->text('blank_answers')->nullable()->comment('إجابات الفراغات (JSON)');
            
            // الحالة
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            
            // تصنيف السؤال
            $table->string('category')->nullable()->comment('تصنيف/وسم السؤال');
            $table->text('tags')->nullable()->comment('وسوم إضافية (JSON)');
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'difficulty', 'is_active']);
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};

