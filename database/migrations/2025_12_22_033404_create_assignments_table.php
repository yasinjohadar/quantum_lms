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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('instructions')->nullable()->comment('تعليمات الواجب بصيغة HTML');
            
            // Polymorphic relationship - يمكن ربط الواجب بمادة/وحدة/درس
            $table->morphs('assignable');
            
            // المعلم الذي أنشأ الواجب
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            
            // إعدادات الدرجات
            $table->decimal('max_score', 8, 2)->default(100)->comment('الدرجة الكاملة');
            
            // موعد التسليم
            $table->dateTime('due_date')->nullable()->comment('موعد التسليم النهائي');
            $table->boolean('allow_late_submission')->default(false)->comment('السماح بالتسليم المتأخر');
            $table->decimal('late_penalty_percentage', 5, 2)->default(0)->comment('نسبة خصم التأخير');
            
            // إعدادات المحاولات
            $table->integer('max_attempts')->default(1)->comment('عدد المحاولات المسموحة');
            
            // إعدادات الملفات
            $table->json('allowed_file_types')->nullable()->comment('أنواع الملفات المسموحة: ["pdf", "doc", "docx"]');
            $table->integer('max_file_size')->default(10)->comment('الحد الأقصى لحجم الملف بالميجابايت');
            $table->integer('max_files_per_submission')->default(5)->comment('الحد الأقصى لعدد الملفات');
            
            // نوع التصحيح
            $table->enum('grading_type', ['manual', 'auto', 'mixed'])->default('manual')->comment('نوع التصحيح: يدوي/تلقائي/مزيج');
            
            // حالة النشر
            $table->boolean('is_published')->default(false);
            $table->dateTime('published_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            // Note: morphs() already creates an index on assignable_type and assignable_id
            $table->index('created_by');
            $table->index('due_date');
            $table->index('is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
