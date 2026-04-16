<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OTPCode;
use App\Models\User;
use App\Models\SystemSetting;
use App\Services\SMS\OTPService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function __construct(
        private OTPService $otpService
    ) {}

    /**
     * عرض نموذج إعادة التعيين بعد إرسال OTP (الجلسة تحتوي على رقم الهاتف المُطبَّع).
     */
    public function create(Request $request): View|RedirectResponse
    {
        $phone = session('password_reset_phone');
        if (! $phone) {
            return redirect()
                ->route('password.request')
                ->with('error', 'يرجى طلب إعادة التعيين من البداية وإدخال رقم هاتفك.');
        }

        $activeOtp = OTPCode::where('phone', $phone)
            ->where('type', 'password_reset')
            ->valid()
            ->orderByDesc('id')
            ->first();

        return view('auth.reset-password', [
            'phone' => $phone,
            'otpExpiresAt' => $activeOtp?->expires_at?->toIso8601String(),
            'otpRemainingSeconds' => $activeOtp ? max(0, now()->diffInSeconds($activeOtp->expires_at, false)) : 0,
            'hasActiveOtp' => (bool) $activeOtp,
        ]);
    }

    /**
     * التحقق من OTP وتعيين كلمة المرور الجديدة.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $phone = session('password_reset_phone');
        if (! $phone) {
            return redirect()
                ->route('password.request')
                ->with('error', 'انتهت الجلسة. يرجى طلب رمز جديد.');
        }

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'code.required' => 'رمز التحقق مطلوب',
            'code.size' => 'رمز التحقق يجب أن يكون 6 أرقام',
        ]);

        $verified = $this->otpService->verifyOTP($phone, $request->code, 'password_reset');

        if (! $verified) {
            throw ValidationException::withMessages([
                'code' => 'رمز التحقق غير صحيح أو منتهي الصلاحية',
            ]);
        }

        $user = User::where('phone', $phone)->whereNull('deleted_at')->first();

        if (! $user) {
            session()->forget('password_reset_phone');

            return redirect()
                ->route('password.request')
                ->with('error', 'لم يُعثر على حساب مرتبط بهذا الرقم.');
        }

        $user->forceFill([
            'password' => $request->password,
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        session()->forget('password_reset_phone');

        return redirect()
            ->route('student.login')
            ->with('status', 'تم تغيير كلمة المرور بنجاح. يمكنك تسجيل الدخول الآن.');
    }

    /**
     * إعادة إرسال رمز إعادة التعيين (نفس رقم الجلسة).
     */
    public function sendOtp(Request $request): \Illuminate\Http\JsonResponse
    {
        $phone = session('password_reset_phone');
        if (! $phone) {
            return response()->json([
                'success' => false,
                'message' => 'انتهت الجلسة. ارجع لصفحة نسيت كلمة المرور.',
            ], 400);
        }

        $user = User::where('phone', $phone)->whereNull('deleted_at')->first();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'لم يُعثر على حساب مرتبط بهذا الرقم.',
            ], 400);
        }

        try {
            $otp = $this->otpService->generateOTP($user, $phone, 'password_reset');
            $provider = $request->input('provider', SystemSetting::get('otp_provider', 'sms'));
            $this->otpService->sendOTP($otp, $provider);

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال رمز التحقق بنجاح',
                'expires_at' => $otp->expires_at?->toIso8601String(),
                'remaining_seconds' => max(0, now()->diffInSeconds($otp->expires_at, false)),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * روابط قديمة في البريد تحتوي على token — توجيه للمسار الجديد.
     */
    public function redirectLegacyToken(Request $request, string $_token): RedirectResponse
    {
        return redirect()
            ->route('password.request')
            ->with('warning', 'تم تحديث استعادة الحساب: استخدم رقم هاتفك لاستلام رمز التحقق وإعادة تعيين كلمة المرور.');
    }
}
