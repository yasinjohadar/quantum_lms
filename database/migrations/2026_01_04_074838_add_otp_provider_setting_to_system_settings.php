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
        // Add otp_provider setting if not exists
        if (!SystemSetting::where('key', 'otp_provider')->where('group', 'phone_verification')->exists()) {
            SystemSetting::set(
                'otp_provider',
                'whatsapp', // Default to WhatsApp since user mentioned it works correctly
                'string',
                'phone_verification',
                'مزود إرسال كود التحقق (sms أو whatsapp)'
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SystemSetting::where('key', 'otp_provider')
            ->where('group', 'phone_verification')
            ->delete();
    }
};
