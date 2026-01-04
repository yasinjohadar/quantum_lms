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
        // Add otp_message_template setting if not exists
        if (!SystemSetting::where('key', 'otp_message_template')->where('group', 'phone_verification')->exists()) {
            SystemSetting::set(
                'otp_message_template',
                'رمز التحقق الخاص بك هو: {code} - صالح لمدة {expires_in} دقائق',
                'text',
                'phone_verification',
                'نص رسالة كود التحقق (استخدم {code} للرمز و {expires_in} لوقت الصلاحية بالدقائق)'
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SystemSetting::where('key', 'otp_message_template')
            ->where('group', 'phone_verification')
            ->delete();
    }
};
