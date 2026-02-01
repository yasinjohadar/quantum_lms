<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove live sessions feature: drop attendance_logs and live_sessions tables.
     */
    public function up(): void
    {
        Schema::dropIfExists('attendance_logs');
        Schema::dropIfExists('live_sessions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('live_sessions', function ($table) {
            $table->id();
            $table->string('sessionable_type');
            $table->unsignedBigInteger('sessionable_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestampTz('scheduled_at');
            $table->integer('duration_minutes');
            $table->string('timezone')->nullable();
            $table->string('status')->default('scheduled');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampsTz();
            $table->index(['sessionable_type', 'sessionable_id']);
        });

        Schema::create('attendance_logs', function ($table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('live_session_id')->constrained('live_sessions')->onDelete('cascade');
            $table->timestampTz('joined_at');
            $table->timestampTz('left_at')->nullable();
            $table->string('join_ip', 45);
            $table->text('user_agent')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->json('meta_json')->nullable();
            $table->timestampsTz();
            $table->index(['user_id', 'live_session_id']);
            $table->index('joined_at');
            $table->index('live_session_id');
        });
    }
};
