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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            
            // معلومات الدرس الأساسية
            $table->string('title');
            $table->text('description')->nullable();
            
            // معلومات الفيديو
            $table->enum('video_type', ['upload', 'youtube', 'vimeo', 'external'])->default('youtube');
            $table->string('video_url')->nullable()->comment('رابط الفيديو أو مسار الملف');
            $table->string('video_id')->nullable()->comment('معرف الفيديو لـ YouTube/Vimeo');
            $table->string('thumbnail')->nullable()->comment('صورة مصغرة للفيديو');
            $table->integer('duration')->nullable()->comment('مدة الفيديو بالثواني');
            
            // إعدادات العرض
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_free')->default(false)->comment('درس مجاني للمعاينة');
            $table->boolean('is_preview')->default(false)->comment('متاح للمعاينة قبل الشراء');
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['unit_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};

