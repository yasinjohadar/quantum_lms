<?php

namespace App\Http\Controllers\Auth\Concerns;

use App\Helpers\PhoneHelper;
use Illuminate\Http\Request;

trait NormalizesAuthPhoneInput
{
    protected function normalizeAuthPhoneForRequest(Request $request): void
    {
        $normalized = $this->normalizeAuthPhoneValue($request);
        if ($normalized !== null) {
            $request->merge(['phone' => $normalized]);
        }
    }

    protected function normalizeAuthPhoneValue(Request $request): ?string
    {
        if (! $request->filled('phone')) {
            return null;
        }

        $countryCode = (string) $request->input('country_code', config('app.phone_default_country_code', '963'));
        $manualCountryCode = preg_replace('/\D+/', '', (string) $request->input('manual_country_code', ''));
        if ($countryCode === 'other') {
            $countryCode = $manualCountryCode;
        }

        $rawPhone = PhoneHelper::composeFromDialCode($countryCode, (string) $request->input('phone')) ?? (string) $request->input('phone');
        $normalized = PhoneHelper::normalize($rawPhone, config('app.phone_default_country_code', '963'));
        if ($normalized !== null) {
            return $normalized;
        }

        return preg_replace('/\s+/', '', trim((string) $request->input('phone')));
    }
}
