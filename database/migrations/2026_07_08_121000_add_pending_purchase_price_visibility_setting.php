<?php

use App\Models\SystemSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (! SystemSetting::where('key', 'student_pending_purchase_price_visible')->where('group', 'general')->exists()) {
            SystemSetting::set(
                'student_pending_purchase_price_visible',
                true,
                'boolean',
                'general',
                'إظهار قيمة الاشتراك في بطاقات الطلبات قيد المراجعة للطالب'
            );
        }
    }

    public function down(): void
    {
        SystemSetting::where('key', 'student_pending_purchase_price_visible')->where('group', 'general')->delete();
    }
};
