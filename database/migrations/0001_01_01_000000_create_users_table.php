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
        Schema::create('users', function (Blueprint $table) {

            $table->id();

            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // حقول إضافية للطلاب
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->string('student_id')->unique()->nullable(); // رقم الطالب

            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();

            // تتبع آخر تسجيل دخول
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->string('last_device_type')->nullable();

            // حالة الحساب (مفعل / غير مفعل) للتحكم في إمكانية تسجيل الدخول
            $table->boolean('is_active')
                ->default(true)
                ->comment('هل الحساب مفعل ويمكنه تسجيل الدخول');

            // حالة الاتصال الحالية (متصل الآن داخل النظام) يتم تحديثها من خلال تتبع الجلسات
            $table->boolean('is_connected')
                ->default(false)
                ->comment('هل المستخدم متصل الآن داخل النظام');


            $table->text('address')->nullable()->comment('Full Address');
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();

        });




        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();

            // معرف المستخدم (يمكن أن يكون null للجلسات غير المرتبطة بمستخدم مسجل)
            $table->foreignId('user_id')->nullable()->index();

            // عنوان IP للجلسة
            $table->string('ip_address', 45)->nullable();

            // بيانات الـ User Agent (المتصفح والجهاز)
            $table->text('user_agent')->nullable();

            // نوع الجهاز (Desktop, Mobile, Tablet, ... )
            $table->string('device_type')->nullable();

            // اسم المتصفح (Chrome, Firefox, Safari, ...)
            $table->string('browser')->nullable();

            // نظام التشغيل (Windows, macOS, Android, iOS, ...)
            $table->string('os')->nullable();

            // الموقع التقريبي للمستخدم (مدينة، دولة، حسب الـ IP)
            $table->string('location')->nullable();

            // وقت تسجيل الدخول (بداية الجلسة)
            $table->timestamp('login_at')->nullable();

            // وقت تسجيل الخروج (نهاية الجلسة)
            $table->timestamp('logout_at')->nullable();

            // مدة الجلسة بالثواني (logout_at - login_at)
            $table->integer('session_duration')->nullable();

            // هل الجلسة نشطة حالياً (true: نشطة، false: منتهية)
            $table->boolean('is_current')->default(true);

            // عدد محاولات الدخول الفاشلة (لأغراض الأمان)
            $table->integer('failed_attempts')->default(0);

            // بيانات الجلسة (مشفرة أو مسلسلة)
            $table->longText('payload');

            // آخر نشاط (timestamp) لتتبع نشاط الجلسة
            $table->integer('last_activity')->index();

            // تاريخ الإنشاء والتحديث (إن أردت تخزينها)
            $table->timestamps();
        });
    }




    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};