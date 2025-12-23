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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type')->comment('نوع الحدث، مثل login_success, login_failed, quiz_focus_lost');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('action')->nullable()->comment('وصف قصير للعمل المنفذ');
            $table->json('metadata')->nullable()->comment('تفاصيل إضافية (JSON)');
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['user_id', 'event_type', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
