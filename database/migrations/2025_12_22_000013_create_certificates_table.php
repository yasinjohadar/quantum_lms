<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained()->onDelete('set null');
            $table->string('type'); // course_completion, excellence, attendance, achievement
            $table->string('certificate_number')->unique();
            $table->timestamp('issued_at');
            $table->foreignId('template_id')->nullable()->constrained('certificate_templates')->onDelete('set null');
            $table->json('metadata')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index('issued_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};

