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
        // جدول الاختبارات
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            
            // الربط بالمادة والوحدة
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete()->comment('اختياري - ربط بوحدة معينة');
            
            // معلومات الاختبار الأساسية
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable()->comment('تعليمات قبل بدء الاختبار');
            $table->string('image')->nullable()->comment('صورة الاختبار');
            
            // إعدادات الوقت
            $table->integer('duration_minutes')->nullable()->comment('المدة بالدقائق (null = غير محدود)');
            $table->boolean('show_timer')->default(true)->comment('إظهار المؤقت');
            $table->boolean('auto_submit')->default(true)->comment('إرسال تلقائي عند انتهاء الوقت');
            
            // إعدادات المحاولات
            $table->integer('max_attempts')->default(0)->comment('عدد المحاولات (0 = غير محدود)');
            $table->integer('delay_between_attempts')->default(0)->comment('التأخير بين المحاولات (دقائق)');
            
            // إعدادات التقييم
            $table->decimal('pass_percentage', 5, 2)->default(50)->comment('نسبة النجاح');
            $table->decimal('total_points', 10, 2)->default(0)->comment('إجمالي الدرجات');
            $table->enum('grading_method', ['highest', 'last', 'average', 'first'])->default('highest')->comment('طريقة احتساب الدرجة');
            
            // إعدادات العرض
            $table->boolean('shuffle_questions')->default(false)->comment('خلط ترتيب الأسئلة');
            $table->boolean('shuffle_options')->default(false)->comment('خلط ترتيب الخيارات');
            $table->integer('questions_per_page')->default(0)->comment('عدد الأسئلة في الصفحة (0 = الكل)');
            $table->boolean('allow_back_navigation')->default(true)->comment('السماح بالرجوع للأسئلة السابقة');
            
            // إعدادات النتائج
            $table->boolean('show_result_immediately')->default(true)->comment('إظهار النتيجة فور الانتهاء');
            $table->boolean('show_correct_answers')->default(false)->comment('إظهار الإجابات الصحيحة');
            $table->boolean('show_explanation')->default(false)->comment('إظهار شرح الإجابات');
            $table->boolean('show_points_per_question')->default(true)->comment('إظهار درجة كل سؤال');
            $table->enum('review_options', ['none', 'immediately', 'after_close', 'always'])->default('immediately')->comment('متى يمكن مراجعة الإجابات');
            
            // الجدولة
            $table->dateTime('available_from')->nullable()->comment('تاريخ بدء الإتاحة');
            $table->dateTime('available_to')->nullable()->comment('تاريخ انتهاء الإتاحة');
            
            // الحالة
            $table->boolean('is_active')->default(true);
            $table->boolean('is_published')->default(false)->comment('منشور للطلاب');
            $table->boolean('requires_password')->default(false);
            $table->string('password')->nullable()->comment('كلمة مرور للدخول');
            
            // إعدادات إضافية
            $table->boolean('require_webcam')->default(false)->comment('يتطلب كاميرا');
            $table->boolean('prevent_copy_paste')->default(true)->comment('منع النسخ واللصق');
            $table->boolean('fullscreen_required')->default(false)->comment('يتطلب وضع ملء الشاشة');
            
            $table->integer('order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['subject_id', 'is_active', 'is_published']);
            $table->index(['available_from', 'available_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};

