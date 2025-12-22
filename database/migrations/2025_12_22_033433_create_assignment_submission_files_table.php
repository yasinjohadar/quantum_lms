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
        Schema::create('assignment_submission_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('assignment_submissions')->onDelete('cascade');
            
            // معلومات الملف
            $table->string('file_path')->comment('مسار الملف في التخزين');
            $table->string('file_name')->comment('اسم الملف الأصلي');
            $table->string('file_type')->nullable()->comment('نوع الملف: pdf, doc, docx, jpg, ...');
            $table->bigInteger('file_size')->comment('حجم الملف بالبايت');
            
            // ترتيب الملف
            $table->integer('order')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['submission_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_submission_files');
    }
};
