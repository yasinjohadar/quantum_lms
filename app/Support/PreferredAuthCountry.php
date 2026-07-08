<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class PreferredAuthCountry
{
    public const SESSION_CODE_KEY = 'preferred_country_code';

    public const SESSION_MANUAL_KEY = 'preferred_manual_country_code';

    public const COOKIE_CODE_KEY = 'preferred_country_code';

    public const COOKIE_MANUAL_KEY = 'preferred_manual_country_code';

    /**
     * Minutes the preferred country cookies stay valid (5 years).
     */
    public static function cookieMinutes(): int
    {
        return (int) config('auth.preferred_country_minutes', 60 * 24 * 365 * 5);
    }

    /**
     * @return array{country_code: string, manual_country_code: string}
     */
    public static function resolve(?Request $request = null): array
    {
        $request ??= request();
        $default = (string) config('app.phone_default_country_code', '963');

        $countryCode = (string) (
            $request->session()->get(self::SESSION_CODE_KEY)
            ?? $request->cookie(self::COOKIE_CODE_KEY)
            ?? $default
        );

        $manualCode = (string) (
            $request->session()->get(self::SESSION_MANUAL_KEY)
            ?? $request->cookie(self::COOKIE_MANUAL_KEY)
            ?? ''
        );

        if ($countryCode === 'other') {
            $manualCode = preg_replace('/\D+/', '', $manualCode) ?: '';
        } else {
            $manualCode = '';
        }

        return [
            'country_code' => $countryCode !== '' ? $countryCode : $default,
            'manual_country_code' => $manualCode,
        ];
    }

    public static function remember(Request $request): void
    {
        $countryCode = (string) $request->input('country_code', '');
        if ($countryCode === '') {
            return;
        }

        $manualCode = '';
        if ($countryCode === 'other') {
            $manualCode = preg_replace('/\D+/', '', (string) $request->input('manual_country_code', '')) ?: '';
            if ($manualCode === '') {
                return;
            }
        }

        $request->session()->put(self::SESSION_CODE_KEY, $countryCode);
        $request->session()->put(self::SESSION_MANUAL_KEY, $manualCode);

        $minutes = self::cookieMinutes();
        Cookie::queue(cookie(self::COOKIE_CODE_KEY, $countryCode, $minutes));
        Cookie::queue(cookie(self::COOKIE_MANUAL_KEY, $manualCode, $minutes));
    }
}
