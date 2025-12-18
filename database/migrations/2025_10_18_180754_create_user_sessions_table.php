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
        Schema::create('user_sessions', function (Blueprint $table) {

            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // معرف جلسة قابل للربط عبر أنظمة (WebSocket, analytics)
            $table->uuid('session_uuid')->nullable()->index();

            // معلومات الجلسة
            $table->string('session_name')->nullable();
            $table->text('session_description')->nullable();

            // أوقات الجلسة (timezone-aware)
            $table->timestampTz('started_at');
            $table->timestampTz('ended_at')->nullable();

            // مدة الجلسة بالثواني — وحيدة وممكن حسابها من timestamps إذا رغبت
            $table->integer('duration_seconds')->nullable()->default(0);

            // معلومات الجهاز والاتصال
            $table->string('ip_address', 45)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->string('device_type')->nullable(); // mobile, tablet, desktop
            $table->string('browser')->nullable();
            $table->string('browser_version', 50)->nullable();
            $table->string('platform')->nullable();
            $table->string('platform_version', 50)->nullable();

            // شاشة (قابلة للاستعلام بسهولة لو فصلت العرض/الارتفاع)
            $table->string('screen_resolution')->nullable();
            // أو: $table->integer('screen_width')->nullable(); $table->integer('screen_height')->nullable();

            // اتصال
            $table->enum('connection_type', ['wifi', 'cellular', 'ethernet', 'unknown'])->nullable();
            $table->decimal('bandwidth_mbps', 10, 2)->nullable();

            // حالة الجلسة — enum أو string حسب حاجتك
            $table->enum('status', ['active', 'completed', 'disconnected', 'timeout'])->default('active')->index();
            // أزلت is_active لأنها مكررة مع status
            $table->text('notes')->nullable();

            // حقل مرن لتخزين بيانات إضافية قابلة للتوسعة
            $table->json('meta')->nullable(); // e.g. { "mfa": true, "recording": false, "reconnect_count": 2 }

            $table->timestampsTz();

            // فهارس مفيدة
            $table->index(['user_id', 'started_at']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};
