<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Helpers\PhoneHelper;
use App\Models\User;
use App\Models\SystemSetting;
use App\Services\SMS\OTPService;
use App\Support\PhoneRegionValidator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(
        private OTPService $otpService
    ) {}

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $phoneVerificationEnabled = SystemSetting::get('phone_verification_enabled', false);
        return view('auth.register', compact('phoneVerificationEnabled'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $phoneVerificationEnabled = SystemSetting::get('phone_verification_enabled', false);

        $this->normalizeRegistrationPhoneForRequest($request);

        // التحقق من التفرد تجاهل المستخدمين المحذوفين (soft-deleted) حتى لا يظهر "مستخدم بالفعل" لرقم/بريد سبق تسجيله ثم حذف
        $validationRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'country_code' => ['nullable', 'string'],
            'manual_country_code' => ['nullable', 'string', 'regex:/^\d{1,4}$/'],
        ];

        if ($phoneVerificationEnabled) {
            $validationRules['phone'] = [
                'required',
                'string',
                'regex:/^\+[1-9]\d{1,14}$/',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    if (! PhoneRegionValidator::isValidForSelection(
                        (string) $value,
                        $request->input('country_code'),
                        $request->input('manual_country_code')
                    )) {
                        $fail(PhoneRegionValidator::MESSAGE_AR);
                    }
                },
                Rule::unique('users', 'phone')->whereNull('deleted_at'),
            ];
            $validationRules['country_code'] = ['required', 'string'];
            $validationRules['manual_country_code'] = ['nullable', 'string', 'regex:/^\d{1,4}$/', 'required_if:country_code,other'];
        }

        $validated = $request->validate($validationRules, [
            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.regex' => 'رقم الهاتف يجب أن يبدأ بـ + متبوعاً برمز الدولة',
            'phone.unique' => 'رقم الهاتف مستخدم بالفعل',
        ]);

        if ($phoneVerificationEnabled) {
            session()->forget('pending_verification_user_id');
            session([
                'pending_registration' => [
                    'name' => $validated['name'],
                    'email' => $validated['email'] ?? null,
                    'password_hash' => Hash::make($validated['password']),
                    'phone' => $validated['phone'],
                ],
            ]);

            try {
                Log::info('Generating OTP for pending registration (no user row yet)', [
                    'phone' => $validated['phone'],
                ]);

                $otp = $this->otpService->generateOTP(null, $validated['phone'], 'verification');

                Log::info('OTP generated successfully', [
                    'otp_id' => $otp->id,
                    'phone' => $otp->phone,
                    'expires_at' => $otp->expires_at,
                ]);

                $provider = $request->input('otp_provider', SystemSetting::get('otp_provider', 'whatsapp'));

                Log::info('Attempting to send OTP', [
                    'provider' => $provider,
                    'phone' => $validated['phone'],
                ]);

                $sent = $this->otpService->sendOTP($otp, $provider);

                if (! $sent) {
                    Log::warning('OTP send failed silently', [
                        'phone' => $validated['phone'],
                        'provider' => $provider,
                    ]);

                    $hint = $this->otpService->getLastDeliveryHint();

                    return redirect()->route('phone.verify')
                        ->with('warning', $hint ?? 'تعذر إرسال رمز التحقق الآن. يرجى إعادة إرسال الكود من هذه الصفحة بعد التأكد من الرقم.');
                }

                Log::info('OTP sent successfully', [
                    'phone' => $validated['phone'],
                    'provider' => $provider,
                ]);

                return redirect()->route('phone.verify')
                    ->with('success', 'أرسلنا كود التحقق إلى رقم هاتفك. أدخل الكود أدناه لإكمال إنشاء حسابك وتفعيل الرقم.');
            } catch (\Exception $e) {
                Log::error('Error sending OTP during registration', [
                    'phone' => $validated['phone'] ?? 'N/A',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return redirect()->route('phone.verify')
                    ->with('warning', 'تعذر إرسال رمز التحقق: '.$e->getMessage().'. يمكنك إعادة الإرسال من هذه الصفحة.');
            }
        }

        // التسجيل بدون تحقق هاتف: إنشاء المستخدم مباشرة
        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ];

        if (isset($validated['phone'])) {
            $userData['phone'] = $validated['phone'];
        }

        $user = User::create($userData);

        $user->assignRole('student');

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('student.dashboard', absolute: false));
    }

    /**
     * التحقق الفوري من تطابق الرقم مع رمز الدولة (نفس منطق التسجيل).
     */
    public function validatePhoneRegion(Request $request): JsonResponse
    {
        if (! SystemSetting::get('phone_verification_enabled', false)) {
            return response()->json(['valid' => true]);
        }

        $request->validate([
            'phone' => 'nullable|string',
            'country_code' => 'nullable|string',
            'manual_country_code' => 'nullable|string',
        ]);

        $normalized = $this->normalizeRegistrationPhoneValue($request);
        if ($normalized === null || $normalized === '') {
            return response()->json(['valid' => true, 'cleared' => true]);
        }

        if (! preg_match('/^\+[1-9]\d{1,14}$/', $normalized)) {
            return response()->json([
                'valid' => false,
                'message' => 'رقم الهاتف يجب أن يبدأ بـ + متبوعاً برمز الدولة',
            ]);
        }

        $ok = PhoneRegionValidator::isValidForSelection(
            $normalized,
            $request->input('country_code'),
            $request->input('manual_country_code')
        );

        if (! $ok) {
            return response()->json([
                'valid' => false,
                'message' => PhoneRegionValidator::MESSAGE_AR,
            ]);
        }

        return response()->json(['valid' => true]);
    }

    private function normalizeRegistrationPhoneForRequest(Request $request): void
    {
        $normalized = $this->normalizeRegistrationPhoneValue($request);
        if ($normalized !== null) {
            $request->merge(['phone' => $normalized]);
        }
    }

    private function normalizeRegistrationPhoneValue(Request $request): ?string
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
