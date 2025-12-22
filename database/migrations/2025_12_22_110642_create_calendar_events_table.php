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
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('event_type', ['general', 'meeting', 'holiday', 'exam', 'other'])->default('general');
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->boolean('is_all_day')->default(false);
            $table->string('location')->nullable();
            $table->string('color')->nullable()->comment('لون الحدث في التقويم');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('set null');
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('set null');
            $table->boolean('is_public')->default(true)->comment('مرئي لجميع الطلاب');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('start_date');
            $table->index('end_date');
            $table->index(['start_date', 'end_date']);
            $table->index('event_type');
            $table->index('is_public');
            $table->index('subject_id');
            $table->index('class_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
