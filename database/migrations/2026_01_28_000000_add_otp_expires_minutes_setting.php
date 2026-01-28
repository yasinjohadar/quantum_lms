<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\SystemSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!SystemSetting::where('key', 'otp_expires_minutes')->where('group', 'general')->exists()) {
            SystemSetting::set(
                'otp_expires_minutes',
                '5',
                'integer',
                'general',
                'مدة صلاحية كود التحقق بالدقائق (مثال: 5)'
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SystemSetting::where('key', 'otp_expires_minutes')->where('group', 'general')->delete();
    }
};
