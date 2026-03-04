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
        if (!SystemSetting::where('key', 'whatsapp_contact_number')->where('group', 'general')->exists()) {
            SystemSetting::set(
                'whatsapp_contact_number',
                '',
                'string',
                'general',
                'رقم واتساب للتواصل (مثال: 963912345678)'
            );
        }

        if (!SystemSetting::where('key', 'whatsapp_float_button_enabled')->where('group', 'general')->exists()) {
            SystemSetting::set(
                'whatsapp_float_button_enabled',
                '0',
                'boolean',
                'general',
                'إظهار أيقونة واتساب العائمة في أسفل الموقع'
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SystemSetting::where('key', 'whatsapp_contact_number')->where('group', 'general')->delete();
        SystemSetting::where('key', 'whatsapp_float_button_enabled')->where('group', 'general')->delete();
    }
};
