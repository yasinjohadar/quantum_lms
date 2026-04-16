<?php

namespace App\Support;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

class PhoneRegionValidator
{
    public const MESSAGE_AR = 'رقم الهاتف لا يتطابق مع رمز الدولة المحدد. تأكد من اختيار الدولة الصحيحة أو من الرقم المحلي.';

    /**
     * التحقق من أن الرقم المُطبَّع (E.164) صالح للمنطقة المختارة من القائمة، أو صالحاً دولياً عند "رمز آخر".
     */
    public static function isValidForSelection(string $e164, ?string $countryCodeRaw, ?string $manualCountryCode): bool
    {
        $e164 = trim($e164);
        if ($e164 === '' || ! preg_match('/^\+[1-9]\d{1,14}$/', $e164)) {
            return false;
        }

        $util = PhoneNumberUtil::getInstance();

        try {
            $proto = $util->parse($e164, null);
        } catch (NumberParseException) {
            return false;
        }

        $countryCodeRaw = (string) ($countryCodeRaw ?? '');
        $isOther = $countryCodeRaw === 'other';

        if ($isOther) {
            $manual = preg_replace('/\D+/', '', (string) $manualCountryCode);
            if ($manual === '') {
                return false;
            }

            return $util->isValidNumber($proto);
        }

        $dialDigits = preg_replace('/\D+/', '', $countryCodeRaw);
        if ($dialDigits === '') {
            return false;
        }

        $row = collect(config('countries', []))->firstWhere('dial_code', $dialDigits);
        $iso2 = isset($row['iso2']) ? (string) $row['iso2'] : null;

        if ($iso2 === null || $iso2 === '') {
            return $util->isValidNumber($proto);
        }

        $region = strtoupper($iso2);

        return $util->isValidNumberForRegion($proto, $region);
    }
}
