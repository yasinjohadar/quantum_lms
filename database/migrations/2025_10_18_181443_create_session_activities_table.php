<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('session_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_session_id')->constrained()->onDelete('cascade');

            $table->enum('activity_type', [
                'session_start',
                'session_end',
                'page_view',
                'action',
                'disconnect',
                'reconnect',
                'idle_start',
                'idle_end',
                'focus_lost',
                'focus_gained'
            ])->index(); // إضافة index هنا مفيد للفرز حسب النوع

            // يُفضل أن يكون JSON بدل text ليسهل الاستعلام لاحقاً
            $table->json('activity_details')->nullable();

            $table->string('page_url', 2048)->nullable(); // بعض الروابط طويلة
            $table->timestampTz('occurred_at');

            $table->timestampsTz();

            $table->index(['user_session_id', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_activities');
    }
};
