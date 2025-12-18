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
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id(); // ✅ مفتاح أساسي

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // ✅ مرن

            // معلومات التسجيل
            $table->string('ip_address', 45)->index();
            $table->text('user_agent');
            $table->string('device_type')->nullable();
            $table->string('browser')->nullable();
            $table->string('browser_version', 50)->nullable();
            $table->string('platform')->nullable();
            $table->string('platform_version', 50)->nullable();

            // معلومات جغرافية (اختياري)
            $table->string('country')->nullable();
            $table->string('city')->nullable();

            // حالة التسجيل
            $table->boolean('is_successful')->default(true)->index();
            $table->string('failure_reason')->nullable();

            // أوقات الدخول والخروج
            $table->timestampTz('login_at');
            $table->timestampTz('logout_at')->nullable();

            // مدة الجلسة
            $table->integer('session_duration_seconds')->nullable();

            // إضافات
            $table->string('session_id')->nullable();
            $table->json('meta')->nullable();

            $table->timestampsTz(); // ✅ Laravel standard

            // فهارس
            $table->index(['user_id', 'login_at']);
            $table->index(['user_id', 'ip_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};
