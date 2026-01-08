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
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_name')->nullable();
            $table->string('event_type'); // page_view, click, start_quiz, etc.
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('set null');
            $table->foreignId('lesson_id')->nullable()->constrained('lessons')->onDelete('set null');
            $table->foreignId('quiz_id')->nullable()->constrained('quizzes')->onDelete('set null');
            $table->foreignId('question_id')->nullable()->constrained('questions')->onDelete('set null');
            $table->json('metadata')->nullable();
            $table->string('session_id')->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            $table->index('event_name');
            $table->index('event_type');
            $table->index('user_id');
            $table->index('subject_id');
            $table->index('quiz_id');
            $table->index('occurred_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};

