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
        Schema::create('live_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('sessionable_type'); // Subject or Lesson (polymorphic)
            $table->unsignedBigInteger('sessionable_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestampTz('scheduled_at');
            $table->integer('duration_minutes');
            $table->string('timezone')->default('UTC');
            $table->enum('status', ['scheduled', 'live', 'completed', 'cancelled'])->default('scheduled');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestampsTz();

            // Indexes
            $table->index(['sessionable_type', 'sessionable_id']);
            $table->index('scheduled_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_sessions');
    }
};
