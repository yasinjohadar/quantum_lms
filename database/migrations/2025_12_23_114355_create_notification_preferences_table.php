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
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // نوع الإشعار (system, assignment, quiz, library, gamification, backup, calendar, custom, etc.)
            $table->string('type')->comment('نوع الإشعار');

            // القنوات المفعلة
            $table->boolean('via_database')->default(true);
            $table->boolean('via_email')->default(false);
            $table->boolean('via_sms')->default(false);

            // حالة الكتم (مثلاً إيقاف نوع معين تماماً)
            $table->boolean('muted')->default(false);

            $table->timestamps();

            $table->unique(['user_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
