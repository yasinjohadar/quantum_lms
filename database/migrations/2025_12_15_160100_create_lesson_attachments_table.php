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
        Schema::create('lesson_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained('lessons')->onDelete('cascade');
            
            // نوع المرفق
            $table->enum('type', ['file', 'link', 'document', 'image', 'audio'])->default('file');
            
            // معلومات المرفق
            $table->string('title');
            $table->text('description')->nullable();
            
            // للملفات المرفوعة
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable()->comment('اسم الملف الأصلي');
            $table->string('file_type')->nullable()->comment('نوع الملف: pdf, doc, zip...');
            $table->bigInteger('file_size')->nullable()->comment('حجم الملف بالبايت');
            
            // للروابط الخارجية
            $table->string('url')->nullable();
            
            // إعدادات
            $table->integer('order')->default(0);
            $table->boolean('is_downloadable')->default(true)->comment('قابل للتحميل');
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['lesson_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_attachments');
    }
};

