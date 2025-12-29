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
        Schema::create('zoom_join_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('live_session_id')->constrained('live_sessions')->onDelete('cascade');
            $table->string('token_hash')->unique()->index(); // hashed token
            $table->timestampTz('expires_at')->index();
            $table->timestampTz('used_at')->nullable();
            $table->integer('use_count')->default(0);
            $table->integer('max_uses')->default(1);
            $table->string('user_agent_hash')->nullable();
            $table->string('ip_prefix', 15)->nullable(); // first 3 octets
            $table->timestampsTz();

            // Indexes
            $table->index(['user_id', 'live_session_id', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoom_join_tokens');
    }
};
