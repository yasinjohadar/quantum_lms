<?php

namespace App\Http\Requests\Auth;

use App\Helpers\PhoneHelper;
use App\Services\LoginLogService;
use App\Support\PhoneRegionValidator;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

class LoginRequest extends FormRequest
{
    /**
     * التحقق من تطابق الرقم مع رمز الدولة المختار (دخول بالهاتف).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->isStudentLoginRequest() && ! $this->isAdminPhoneMode()) {
                return;
            }

            $phone = $this->normalizedPhoneFromCountryInputs();
            if ($phone === '' || ! preg_match('/^\+[1-9]\d{1,14}$/', $phone)) {
                return;
            }

            if (! PhoneRegionValidator::isValidForSelection(
                $phone,
                $this->input('country_code'),
                $this->input('manual_country_code')
            )) {
                $validator->errors()->add('phone', PhoneRegionValidator::MESSAGE_AR);
            }
        });
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->isStudentLoginRequest()) {
            return [
                'country_code' => ['required', 'string'],
                'manual_country_code' => ['nullable', 'string', 'regex:/^\d{1,4}$/', 'required_if:country_code,other'],
                'phone' => ['required', 'string'],
                'password' => ['required', 'string'],
            ];
        }

        if ($this->isAdminPhoneMode()) {
            return [
                'admin_auth_mode' => ['required', 'in:email,phone'],
                'country_code' => ['required', 'string'],
                'manual_country_code' => ['nullable', 'string', 'regex:/^\d{1,4}$/', 'required_if:country_code,other'],
                'phone' => ['required', 'string'],
                'password' => ['required', 'string'],
            ];
        }

        return [
            'admin_auth_mode' => ['nullable', 'in:email,phone'],
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $authenticated = false;
        foreach ($this->resolveLoginCredentials() as $credentials) {
            if (Auth::attempt($credentials, $this->boolean('remember'))) {
                $authenticated = true;
                break;
            }
        }

        if (! $authenticated) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                $this->errorField() => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        // تسجيل محاولة دخول فاشلة بسبب rate limit
        LoginLogService::logLogin(null, $this, false, 'Too many login attempts');

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            $this->errorField() => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        $key = ($this->isStudentLoginRequest() || $this->isAdminPhoneMode())
            ? $this->normalizedPhoneFromCountryInputs()
            : trim((string) $this->input('login'));

        return Str::transliterate(Str::lower($this->loginMode().':'.$key).'|'.$this->ip());
    }

    public function errorField(): string
    {
        return ($this->isStudentLoginRequest() || $this->isAdminPhoneMode()) ? 'phone' : 'login';
    }

    /**
     * Build credential candidates for email or phone login.
     *
     * @return array<int, array<string, string>>
     */
    private function resolveLoginCredentials(): array
    {
        if ($this->isStudentLoginRequest() || $this->isAdminPhoneMode()) {
            $password = (string) $this->input('password');
            $phone = $this->normalizedPhoneFromCountryInputs();

            if ($phone === '') {
                return [];
            }

            return [[
                'phone' => $phone,
                'password' => $password,
            ]];
        }

        $identifier = trim((string) $this->input('login'));
        $password = (string) $this->input('password');

        if (str_contains($identifier, '@')) {
            return [[
                'email' => Str::lower($identifier),
                'password' => $password,
            ]];
        }

        $candidates = [];
        $defaultCountryCode = (string) config('app.phone_default_country_code', '966');
        $normalizedPhone = PhoneHelper::normalize($identifier, $defaultCountryCode);

        if ($normalizedPhone !== null) {
            $candidates[] = $normalizedPhone;
        }

        $strippedPhone = preg_replace('/\s+/', '', $identifier);
        if ($strippedPhone !== '' && ! in_array($strippedPhone, $candidates, true)) {
            $candidates[] = $strippedPhone;
        }

        return array_map(static fn (string $phone) => [
            'phone' => $phone,
            'password' => $password,
        ], $candidates);
    }

    private function normalizedStudentPhone(): string
    {
        $countryCode = (string) $this->input('country_code', config('app.phone_default_country_code', '963'));
        if ($countryCode === 'other') {
            $countryCode = (string) $this->input('manual_country_code', '');
        }

        $rawPhone = PhoneHelper::composeFromDialCode($countryCode, (string) $this->input('phone'));
        if ($rawPhone === null) {
            return '';
        }

        return PhoneHelper::normalize($rawPhone, (string) config('app.phone_default_country_code', '963')) ?? '';
    }

    private function isStudentLoginRequest(): bool
    {
        return $this->routeIs('student.login.store');
    }

    private function isAdminPhoneMode(): bool
    {
        return !$this->isStudentLoginRequest() && $this->input('admin_auth_mode', 'email') === 'phone';
    }

    private function normalizedPhoneFromCountryInputs(): string
    {
        return $this->normalizedStudentPhone();
    }

    private function loginMode(): string
    {
        if ($this->isStudentLoginRequest()) {
            return 'student_phone';
        }

        if ($this->isAdminPhoneMode()) {
            return 'admin_phone';
        }

        return 'admin_identifier';
    }
}