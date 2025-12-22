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
        Schema::create('event_reminders', function (Blueprint $table) {
            $table->id();
            $table->enum('event_type', ['calendar_event', 'quiz', 'assignment']);
            $table->unsignedBigInteger('event_id')->comment('ID الحدث');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade')->comment('null = لجميع المستخدمين');
            $table->enum('reminder_type', ['single', 'multiple'])->default('single');
            $table->json('reminder_times')->nullable()->comment('[1, 24, 168] ساعات قبل الحدث');
            $table->integer('custom_minutes')->nullable()->comment('للتذكير الواحد');
            $table->boolean('is_sent')->default(false);
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['event_type', 'event_id']);
            $table->index('user_id');
            $table->index('is_sent');
            $table->index('reminder_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_reminders');
    }
};
