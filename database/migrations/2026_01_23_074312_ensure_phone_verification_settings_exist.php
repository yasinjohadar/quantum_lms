<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SystemSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // التأكد من وجود phone_verification_enabled في مجموعة general
        if (!SystemSetting::where('key', 'phone_verification_enabled')->where('group', 'general')->exists()) {
            SystemSetting::set(
                'phone_verification_enabled',
                '0',
                'boolean',
                'general',
                'تفعيل/تعطيل التحقق من رقم الهاتف عند التسجيل عبر الواتساب أو SMS'
            );
        }

        // التأكد من وجود otp_provider في مجموعة general (نقل من phone_verification إذا كان موجوداً هناك)
        $existingOtpProvider = SystemSetting::where('key', 'otp_provider')->first();
        if ($existingOtpProvider) {
            // إذا كان موجوداً في مجموعة أخرى، نقل إلى general
            if ($existingOtpProvider->group !== 'general') {
                $existingOtpProvider->group = 'general';
                $existingOtpProvider->save();
            }
        } else {
            // إذا لم يكن موجوداً، إنشاؤه في general
            SystemSetting::set(
                'otp_provider',
                'whatsapp',
                'string',
                'general',
                'مزود إرسال كود التحقق (sms أو whatsapp)'
            );
        }

        // التأكد من وجود otp_message_template في مجموعة general (نقل من phone_verification إذا كان موجوداً هناك)
        $existingOtpTemplate = SystemSetting::where('key', 'otp_message_template')->first();
        if ($existingOtpTemplate) {
            // إذا كان موجوداً في مجموعة أخرى، نقل إلى general
            if ($existingOtpTemplate->group !== 'general') {
                $existingOtpTemplate->group = 'general';
                $existingOtpTemplate->save();
            }
        } else {
            // إذا لم يكن موجوداً، إنشاؤه في general
            SystemSetting::set(
                'otp_message_template',
                'رمز التحقق الخاص بك هو: {code} - صالح لمدة {expires_in} دقائق',
                'text',
                'general',
                'نص رسالة كود التحقق (استخدم {code} للرمز و {expires_in} لوقت الصلاحية بالدقائق)'
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // لا نحذف الإعدادات عند rollback، فقط نتركها كما هي
    }
};
