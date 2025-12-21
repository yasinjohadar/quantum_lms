<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaderboards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // global, course, weekly, monthly
            $table->foreignId('subject_id')->nullable()->constrained()->onDelete('cascade');
            $table->dateTime('period_start')->nullable();
            $table->dateTime('period_end')->nullable();
            $table->json('criteria')->nullable(); // معايير اللوحة
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('is_active');
            $table->index(['period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaderboards');
    }
};

