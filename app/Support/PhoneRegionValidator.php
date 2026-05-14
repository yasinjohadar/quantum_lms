<?php

namespace App\Support;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

class PhoneRegionValidator
{
    public const MESSAGE_AR = 'رقم الهاتف لا يتطابق مع رمز الدولة المحدد. تأكد من اختيار الدولة الصحيحة أو من الرقم المحلي.';

    /**
     * عند اختيار دولة من القائمة: الرقم المحلي فقط دون + أو 00 في بداية الحقل.
     */
    public static function messageIfRawPhoneHasInternationalPrefix(?string $countryCodeRaw, string $rawPhone): ?string
    {
        $countryCodeRaw = (string) ($countryCodeRaw ?? '');
        if ($countryCodeRaw === 'other') {
            return null;
        }

        $raw = trim($rawPhone);
        if ($raw === '') {
            return null;
        }

        if (str_contains($raw, '+')) {
            return 'أدخل الرقم المحلي فقط (أرقام) دون علامة + أو رمز الدولة؛ يُختار الرمز من القائمة ويُدمج تلقائياً.';
        }

        if (str_starts_with($raw, '00')) {
            return 'لا تبدأ الرقم بـ 00؛ أدخل الرقم المحلي فقط واختر الدولة من القائمة.';
        }

        return null;
    }

    /**
     * إن كان الرقم صالحاً للاختيار الحالي تعيد null، وإلا نصاً يصف سبب الرفض.
     */
    public static function messageForSelection(string $e164, ?string $countryCodeRaw, ?string $manualCountryCode): ?string
    {
        $e164 = trim($e164);
        if ($e164 === '' || ! preg_match('/^\+[1-9]\d{1,14}$/', $e164)) {
            return 'صيغة رقم الهاتف غير صالحة بعد الدمج مع رمز الدولة. أدخل الرقم المحلي فقط دون تكرار رمز الدولة.';
        }

        $util = PhoneNumberUtil::getInstance();

        try {
            $proto = $util->parse($e164, null);
        } catch (NumberParseException) {
            return 'تعذر قراءة رقم الهاتف. تحقق من الأرقام ورمز الدولة.';
        }

        $countryCodeRaw = (string) ($countryCodeRaw ?? '');
        $isOther = $countryCodeRaw === 'other';

        if ($isOther) {
            $manual = preg_replace('/\D+/', '', (string) $manualCountryCode);
            if ($manual === '') {
                return 'عند اختيار «رمز آخر» يجب إدخال رمز الدولة في الحقل المخصص.';
            }
            if (! $util->isValidNumber($proto)) {
                return 'رقم الهاتف غير صالح مع رمز الدولة اليدوي. تحقق من الرقم والرمز.';
            }

            return null;
        }

        $dialDigits = preg_replace('/\D+/', '', $countryCodeRaw);
        if ($dialDigits === '') {
            return 'اختر رمز الدولة من القائمة.';
        }

        $row = collect(config('countries', []))->firstWhere('dial_code', $dialDigits);
        $iso2 = isset($row['iso2']) ? (string) $row['iso2'] : null;

        if ($iso2 === null || $iso2 === '') {
            if (! $util->isValidNumber($proto)) {
                return 'رقم الهاتف غير صالح.';
            }

            return null;
        }

        $region = strtoupper($iso2);
        if ($util->isValidNumberForRegion($proto, $region)) {
            return null;
        }

        if ($util->isValidNumber($proto)) {
            $nameAr = (string) ($row['name_ar'] ?? '');
            $label = $nameAr !== '' ? $nameAr : ('+'.$dialDigits);

            return "الرقم صالح لكنه لا يطابق الدولة المختارة ({$label}). غيّر الدولة من القائمة أو صحّح الرقم المحلي دون إدخال رمز دولة مكرر.";
        }

        return 'رقم الهاتف غير صالح لهذه الدولة. تأكد من إدخال الرقم المحلي فقط دون تكرار رمز الدولة.';
    }

    /**
     * التحقق من أن الرقم المُطبَّع (E.164) صالح للمنطقة المختارة من القائمة، أو صالحاً دولياً عند "رمز آخر".
     */
    public static function isValidForSelection(string $e164, ?string $countryCodeRaw, ?string $manualCountryCode): bool
    {
        return self::messageForSelection($e164, $countryCodeRaw, $manualCountryCode) === null;
    }
}
