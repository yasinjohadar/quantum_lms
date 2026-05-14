<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\SystemSetting;

return new class extends Migration
{
    public function up(): void
    {
        if (! SystemSetting::where('key', 'payments_iban_receipt_required')->exists()) {
            SystemSetting::set(
                'payments_iban_receipt_required',
                '1',
                'boolean',
                'payments',
                'عند التعطيل لا يُطلب من الطالب رفع وصل التحويل البنكي ويُخفى حقل الرفع.'
            );
        }

        if (! SystemSetting::where('key', 'payments_iban_student_instructions')->exists()) {
            SystemSetting::set(
                'payments_iban_student_instructions',
                '',
                'text',
                'payments',
                'نص تعليمات يظهر للطالب عند اختيار التحويل البنكي (أسطر متعددة). إذا تُرك فارغاً يُعرض نص افتراضي قصير.'
            );
        }
    }

    public function down(): void
    {
        SystemSetting::whereIn('key', [
            'payments_iban_receipt_required',
            'payments_iban_student_instructions',
        ])->delete();
    }
};
