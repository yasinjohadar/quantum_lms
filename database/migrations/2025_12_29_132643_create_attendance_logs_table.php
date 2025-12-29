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
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('live_session_id')->constrained('live_sessions')->onDelete('cascade');
            $table->string('zoom_meeting_id')->nullable();
            $table->timestampTz('joined_at');
            $table->timestampTz('left_at')->nullable();
            $table->string('join_ip', 45);
            $table->text('user_agent')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->json('meta_json')->nullable(); // Zoom participant data
            $table->timestampsTz();

            // Indexes
            $table->index(['user_id', 'live_session_id']);
            $table->index('joined_at');
            $table->index('live_session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
