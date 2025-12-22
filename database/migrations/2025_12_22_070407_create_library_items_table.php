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
        Schema::create('library_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            
            // نوع العنصر
            $table->enum('type', ['file', 'link', 'video', 'document', 'book', 'worksheet'])->default('file');
            
            // التصنيف
            $table->foreignId('category_id')->constrained('library_categories')->onDelete('restrict');
            
            // ربط بمادة (nullable - للمكتبات الخاصة)
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('cascade');
            
            // من رفع العنصر
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            
            // معلومات الملف (nullable - للروابط)
            $table->string('file_path')->nullable()->comment('مسار الملف في التخزين');
            $table->string('file_name')->nullable()->comment('اسم الملف الأصلي');
            $table->string('file_type')->nullable()->comment('نوع الملف: pdf, doc, mp4...');
            $table->bigInteger('file_size')->nullable()->comment('حجم الملف بالبايت');
            
            // الرابط الخارجي (nullable - للملفات)
            $table->string('external_url')->nullable()->comment('رابط خارجي');
            
            // صورة مصغرة
            $table->string('thumbnail')->nullable()->comment('صورة مصغرة');
            
            // إعدادات
            $table->boolean('is_featured')->default(false)->comment('مميز');
            $table->boolean('is_public')->default(true)->comment('عام أو خاص بالمادة');
            $table->enum('access_level', ['public', 'enrolled', 'restricted'])->default('public')->comment('مستوى الوصول');
            
            // إحصائيات
            $table->integer('download_count')->default(0)->comment('عدد التحميلات');
            $table->integer('view_count')->default(0)->comment('عدد المشاهدات');
            $table->decimal('average_rating', 3, 2)->default(0)->comment('متوسط التقييم');
            $table->integer('total_ratings')->default(0)->comment('عدد التقييمات');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('slug');
            $table->index('category_id');
            $table->index('subject_id');
            $table->index('type');
            $table->index('is_featured');
            $table->index('is_public');
            $table->index('access_level');
            $table->index('uploaded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_items');
    }
};
