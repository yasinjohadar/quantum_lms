<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\SystemSetting;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            [
                'key' => 'payments_iban_display_name',
                'value' => 'تحويل بنكي (IBAN)',
                'type' => 'string',
                'description' => 'اسم طريقة الدفع الوحيدة المعروضة للطالب.',
            ],
            [
                'key' => 'payments_iban_account_iban',
                'value' => 'SA1234567890123456789012',
                'type' => 'string',
                'description' => 'رقم IBAN المعروض للطالب.',
            ],
            [
                'key' => 'payments_iban_account_bank_name',
                'value' => 'البنك الأهلي السعودي',
                'type' => 'string',
                'description' => 'اسم البنك المعروض للطالب.',
            ],
            [
                'key' => 'payments_iban_account_holder',
                'value' => '',
                'type' => 'string',
                'description' => 'اسم صاحب الحساب (اختياري).',
            ],
            [
                'key' => 'payments_iban_pending_message',
                'value' => 'الطلب قيد المعالجة. يجب التواصل مع المشرفة لتأكيد الاشتراك.',
                'type' => 'text',
                'description' => 'رسالة تظهر للطالب بعد تأكيد الدفع وفي تنبيه أعلى نموذج التحويل.',
            ],
        ];

        foreach ($defaults as $row) {
            if (! SystemSetting::where('key', $row['key'])->exists()) {
                SystemSetting::set(
                    $row['key'],
                    $row['value'],
                    $row['type'],
                    'payments',
                    $row['description']
                );
            }
        }
    }

    public function down(): void
    {
        SystemSetting::whereIn('key', [
            'payments_iban_display_name',
            'payments_iban_account_iban',
            'payments_iban_account_bank_name',
            'payments_iban_account_holder',
            'payments_iban_pending_message',
        ])->delete();
    }
};
