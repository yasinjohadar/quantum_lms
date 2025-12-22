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
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            
            // معلومات المحاولة
            $table->integer('attempt_number')->default(1)->comment('رقم المحاولة');
            
            // تاريخ الإرسال
            $table->dateTime('submitted_at')->nullable()->comment('تاريخ الإرسال');
            $table->boolean('is_late')->default(false)->comment('هل تم الإرسال متأخراً');
            
            // حالة الإرسال
            $table->enum('status', ['draft', 'submitted', 'graded', 'returned'])->default('draft')->comment('حالة الإرسال');
            
            // الدرجات
            $table->decimal('total_score', 8, 2)->nullable()->comment('الدرجة الإجمالية');
            $table->decimal('grade_percentage', 5, 2)->nullable()->comment('النسبة المئوية');
            
            // معلومات التصحيح
            $table->dateTime('graded_at')->nullable()->comment('تاريخ التصحيح');
            $table->foreignId('graded_by')->nullable()->constrained('users')->onDelete('set null')->comment('المعلم الذي صحح الواجب');
            
            // ملاحظات عامة
            $table->text('feedback')->nullable()->comment('ملاحظات عامة من المعلم');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['assignment_id', 'student_id']);
            $table->index('student_id');
            $table->index('status');
            $table->index('submitted_at');
            
            // Unique constraint: طالب واحد - واجب واحد - محاولة واحدة
            $table->unique(['assignment_id', 'student_id', 'attempt_number'], 'unique_submission_attempt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
    }
};
