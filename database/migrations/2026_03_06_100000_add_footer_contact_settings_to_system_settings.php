<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\SystemSetting;

return new class extends Migration
{
    public function up(): void
    {
        $contactKeys = [
            'contact_address' => ['العنوان الظاهر في الفوتر', 'دمشق - سوريا'],
            'contact_phone'   => ['رقم الهاتف الظاهر في الفوتر', '000 000 000'],
            'contact_email'   => ['البريد الإلكتروني الظاهر في الفوتر', 'info@example.com'],
        ];
        foreach ($contactKeys as $key => [$description, $default]) {
            if (! SystemSetting::where('key', $key)->exists()) {
                SystemSetting::set($key, $default, 'string', 'general', $description);
            }
        }
    }

    public function down(): void
    {
        SystemSetting::whereIn('key', ['contact_address', 'contact_phone', 'contact_email'])->delete();
    }
};
