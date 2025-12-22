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
        Schema::create('assignment_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('assignment_submissions')->onDelete('cascade');
            
            // معايير التصحيح اليدوي
            $table->json('criteria')->nullable()->comment('معايير التصحيح: [{"criterion": "...", "points": 10, "earned": 8}]');
            
            // الدرجة اليدوية
            $table->decimal('manual_score', 8, 2)->default(0)->comment('الدرجة اليدوية');
            
            // تعليقات مفصلة
            $table->text('comments')->nullable()->comment('تعليقات مفصلة من المعلم');
            
            // معلومات المصحح
            $table->foreignId('graded_by')->constrained('users')->onDelete('cascade')->comment('المعلم الذي صحح الواجب');
            $table->dateTime('graded_at')->comment('تاريخ التصحيح');
            
            $table->timestamps();
            
            // Indexes
            $table->index('submission_id');
            $table->index('graded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_grades');
    }
};
