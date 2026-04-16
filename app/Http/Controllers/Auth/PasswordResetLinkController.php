<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\Concerns\NormalizesAuthPhoneInput;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\SMS\OTPService;
use App\Support\PhoneRegionValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    use NormalizesAuthPhoneInput;

    public function __construct(
        private OTPService $otpService
    ) {}

    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * إرسال رمز OTP لإعادة تعيين كلمة المرور عبر الهاتف.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->normalizeAuthPhoneForRequest($request);

        $validated = $request->validate([
            'phone' => [
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
            ],
            'country_code' => ['required', 'string'],
            'manual_country_code' => ['nullable', 'string', 'regex:/^\d{1,4}$/', 'required_if:country_code,other'],
        ], [
            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.regex' => 'رقم الهاتف يجب أن يبدأ بـ + متبوعاً برمز الدولة',
        ]);

        $phone = $validated['phone'];

        $user = User::where('phone', $phone)->whereNull('deleted_at')->first();

        if (! $user) {
            return back()
                ->withInput($request->only('country_code', 'manual_country_code', 'phone'))
                ->withErrors(['phone' => 'رقم الهاتف غير مسجّل في المنصة.']);
        }

        try {
            $otp = $this->otpService->generateOTP($user, $phone, 'password_reset');

            $provider = $request->input('otp_provider', SystemSetting::get('otp_provider', 'whatsapp'));

            $sent = $this->otpService->sendOTP($otp, $provider);

            if (! $sent) {
                $hint = $this->otpService->getLastDeliveryHint();

                return back()
                    ->withInput($request->only('country_code', 'manual_country_code', 'phone'))
                    ->with('warning', $hint ?? 'تعذر إرسال رمز التحقق الآن. حاول مرة أخرى لاحقاً.');
            }

            session(['password_reset_phone' => $phone]);

            Log::info('Password reset OTP sent', ['user_id' => $user->id, 'phone' => $phone]);

            return redirect()
                ->route('password.reset')
                ->with('success', 'تم إرسال رمز التحقق إلى رقم هاتفك. أدخل الرمز أدناه ثم كلمة المرور الجديدة.');
        } catch (\Exception $e) {
            Log::error('Password reset OTP failed', ['phone' => $phone, 'error' => $e->getMessage()]);

            return back()
                ->withInput($request->only('country_code', 'manual_country_code', 'phone'))
                ->withErrors(['phone' => $e->getMessage()]);
        }
    }

    /**
     * التحقق الفوري من تطابق الرقم مع رمز الدولة (نفس منطق التسجيل).
     */
    public function validatePhoneRegion(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'nullable|string',
            'country_code' => 'nullable|string',
            'manual_country_code' => 'nullable|string',
        ]);

        $normalized = $this->normalizeAuthPhoneValue($request);
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
}
