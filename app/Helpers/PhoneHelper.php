<?php

namespace App\Helpers;

class PhoneHelper
{
    /**
     * Build raw phone number from dial code + local phone.
     */
    public static function composeFromDialCode(?string $dialCode, ?string $phone): ?string
    {
        $dialCodeDigits = preg_replace('/\D+/', '', (string) $dialCode);
        $phoneDigits = preg_replace('/\D+/', '', (string) $phone);

        if ($phoneDigits === '') {
            return null;
        }

        if ($dialCodeDigits !== '' && !str_starts_with($phoneDigits, $dialCodeDigits)) {
            $phoneDigits = $dialCodeDigits . ltrim($phoneDigits, '0');
        }

        return $phoneDigits;
    }

    /**
     * تطبيع رقم الهاتف إلى صيغة +XXXXXXXX (مثال: +966501234567).
     * - إزالة المسافات والشرطات
     * - إذا بدأ بـ 0 (مثل 0501234567) يُستبدل برمز الدولة الافتراضي
     * - إذا كان أرقاماً فقط بدون + يُضاف + في البداية
     */
    public static function normalize(string $phone, string $defaultCountryCode = '966'): ?string
    {
        if ($phone === null || $phone === '') {
            return null;
        }

        $phone = trim($phone);
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
        $phone = preg_replace('/^\+/', '', $phone);

        if ($phone === '') {
            return null;
        }

        $digitsOnly = preg_replace('/\D/', '', $phone);
        if ($digitsOnly === '') {
            return null;
        }

        if (str_starts_with($digitsOnly, '0')) {
            $digitsOnly = $defaultCountryCode . substr($digitsOnly, 1);
        }

        $normalized = '+' . $digitsOnly;

        if (!preg_match('/^\+[1-9]\d{1,14}$/', $normalized)) {
            return null;
        }

        return $normalized;
    }
}
