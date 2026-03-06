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
        Schema::create('academic_weeks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->unsignedSmallInteger('week_number')->comment('رقم الأسبوع داخل السنة');
            $table->string('title')->nullable()->comment('عنوان اختياري للأسبوع');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('required_lessons_target')->default(0)->comment('الهدف الافتراضي لعدد الدروس المطلوبة');
            $table->json('meta')->nullable()->comment('خصوصية الأسبوع: نوع، ملاحظات، إلخ');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['academic_year_id', 'start_date', 'end_date']);
            $table->unique(['academic_year_id', 'week_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_weeks');
    }
};
