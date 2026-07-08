<?php

use App\Models\SystemSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (! SystemSetting::where('key', 'student_supervisor_whatsapp_button_enabled')->where('group', 'general')->exists()) {
            SystemSetting::set(
                'student_supervisor_whatsapp_button_enabled',
                true,
                'boolean',
                'general',
                'إظهار زر واتساب قسم الإشراف للطلاب'
            );
        }

        if (! SystemSetting::where('key', 'student_supervisor_whatsapp_message')->where('group', 'general')->exists()) {
            SystemSetting::set(
                'student_supervisor_whatsapp_message',
                'حتى يتم تفعيل حسابك يرجى التواصل مع قسم الإشراف عبر الواتساب',
                'text',
                'general',
                'رسالة التواصل عبر واتساب قسم الإشراف (مودال طلب الانضمام)'
            );
        }

        if (! SystemSetting::where('key', 'student_supervisor_whatsapp_button_label')->where('group', 'general')->exists()) {
            SystemSetting::set(
                'student_supervisor_whatsapp_button_label',
                'واتساب قسم الإشراف',
                'string',
                'general',
                'نص زر واتساب قسم الإشراف للطلاب'
            );
        }

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
        SystemSetting::where('key', 'student_supervisor_whatsapp_button_enabled')->where('group', 'general')->delete();
        SystemSetting::where('key', 'student_supervisor_whatsapp_message')->where('group', 'general')->delete();
        SystemSetting::where('key', 'student_supervisor_whatsapp_button_label')->where('group', 'general')->delete();
        SystemSetting::where('key', 'student_pending_purchase_price_visible')->where('group', 'general')->delete();
    }
};
