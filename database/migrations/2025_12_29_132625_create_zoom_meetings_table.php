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
        Schema::create('zoom_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_session_id')->unique()->constrained('live_sessions')->onDelete('cascade');
            $table->string('zoom_meeting_id'); // Zoom meeting ID
            $table->string('zoom_uuid')->unique(); // Zoom meeting UUID
            $table->string('host_email');
            $table->string('host_id')->nullable();
            $table->string('topic');
            $table->timestampTz('start_time');
            $table->integer('duration'); // minutes
            $table->string('timezone');
            $table->text('encrypted_passcode')->nullable(); // Laravel encrypted
            $table->json('settings_json')->nullable(); // Zoom meeting settings
            $table->enum('status', ['created', 'started', 'ended', 'cancelled'])->default('created');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestampsTz();

            // Indexes
            $table->index('zoom_meeting_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoom_meetings');
    }
};
