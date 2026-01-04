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
        // Add phone_verification_enabled setting if not exists
        if (!SystemSetting::where('key', 'phone_verification_enabled')->where('group', 'general')->exists()) {
            SystemSetting::set(
                'phone_verification_enabled',
                '0',
                'boolean',
                'general',
                'تفعيل/تعطيل التحقق من رقم الهاتف عند التسجيل'
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SystemSetting::where('key', 'phone_verification_enabled')
            ->where('group', 'general')
            ->delete();
    }
};

