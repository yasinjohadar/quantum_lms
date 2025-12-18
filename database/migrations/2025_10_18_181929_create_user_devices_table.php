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
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // بصمة الجهاز (unique per user)
            $table->string('device_fingerprint', 128);
            // معلومات الجهاز والمتصفح
            $table->string('device_name')->nullable(); // اسم مخصص يضعه المستخدم
            $table->string('device_type', 20); // mobile, tablet, desktop
            $table->string('browser', 50);
            $table->string('browser_version', 50)->nullable();
            $table->string('platform', 50);
            $table->string('platform_version', 50)->nullable();

            // معلومات إضافية (اختيارية)
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable(); // يُفيد في التحقق لاحقًا من تغير التفاصيل الدقيقة

            // إحصائيات الاستخدام
            $table->unsignedInteger('total_logins')->default(1);
            $table->timestampTz('first_used_at')->useCurrent();
            $table->timestampTz('last_used_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('last_ip_address', 45)->nullable();

            // حالة الجهاز
            $table->boolean('is_trusted')->default(false)->index();
            $table->boolean('is_blocked')->default(false)->index();

            // بيانات إضافية قابلة للتوسع
            $table->json('meta')->nullable(); // مثل { "geo_country": "SA", "risk_score": 0.3 }

            $table->timestampsTz();

            // Unique constraint على user_id + device_fingerprint معاً
            $table->unique(['user_id', 'device_fingerprint'], 'user_device_fingerprint_unique');
            $table->index(['user_id', 'last_used_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
