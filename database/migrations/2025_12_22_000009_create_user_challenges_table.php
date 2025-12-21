<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('challenge_id')->constrained()->onDelete('cascade');
            $table->integer('progress')->default(0); // تقدم نحو التحدي (0-100)
            $table->timestamp('completed_at')->nullable();
            $table->boolean('reward_claimed')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'challenge_id']);
            $table->index('completed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_challenges');
    }
};

