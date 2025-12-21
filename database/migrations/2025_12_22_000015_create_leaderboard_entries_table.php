<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaderboard_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leaderboard_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('rank');
            $table->integer('score');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['leaderboard_id', 'user_id']);
            $table->index(['leaderboard_id', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaderboard_entries');
    }
};

