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
        Schema::create('teacher_week_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('academic_week_id')->constrained('academic_weeks')->cascadeOnDelete();
            $table->unsignedInteger('required_lessons_target')->comment('عدد الدروس المطلوبة لهذا المعلم في هذا الأسبوع');
            $table->timestamps();

            $table->unique(['teacher_id', 'academic_week_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_week_targets');
    }
};
