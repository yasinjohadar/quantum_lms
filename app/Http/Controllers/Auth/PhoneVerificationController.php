<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OTPCode;
use App\Services\Auth\PendingPhoneRegistrationCleanup;
use App\Services\SMS\OTPService;
use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PhoneVerificationController extends Controller
{
    public function __construct(
        private OTPService $otpService,
        private PendingPhoneRegistrationCleanup $pendingPhoneRegistrationCleanup
    ) {
        // إزالة middleware('auth') للسماح بالوصول للمستخدمين غير المسجلين دخول
        // سنتحقق من المستخدم يدوياً في show() و verify()
    }

    /**
     * Show phone verification page
     */
    public function show()
    {
        if (session()->has('pending_registration')) {
            $data = session('pending_registration', []);
            $phone = $data['phone'] ?? null;
            if (! $phone) {
                return redirect()->route('register')
                    ->with('error', 'انتهت صلاحية جلسة التسجيل. يرجى التسجيل مرة أخرى.');
            }

            $activeOtp = $this->getLatestActiveOtp($phone);

            return view('auth.verify-phone', [
                'user' => null,
                'phone' => $phone,
                'otpExpiresAt' => $activeOtp?->expires_at?->toIso8601String(),
                'otpRemainingSeconds' => $activeOtp ? max(0, now()->diffInSeconds($activeOtp->expires_at, false)) : 0,
                'hasActiveOtp' => (bool) $activeOtp,
            ]);
        }

        // مسار قديم: مستخدم أُنشئ قبل التحقق (يُنظَّف عبر PendingPhoneRegistrationCleanup عند انتهاء OTP)
        $userId = session('pending_verification_user_id');

        if ($userId) {
            $user = User::find($userId);
            if (! $user) {
                return redirect()->route('register')
                    ->with('error', 'انتهت صلاحية جلسة التحقق. يرجى التسجيل مرة أخرى.');
            }

            if ($this->pendingPhoneRegistrationCleanup->purge($user)) {
                session()->forget('pending_verification_user_id');

                return redirect()->route('register')
                    ->with('error', 'انتهت صلاحية التحقق وتم إلغاء الحساب. يرجى التسجيل مرة أخرى.');
            }
        } else {
            // للمستخدمين المسجلين دخول
            if (! Auth::check()) {
                return redirect()->route('login')
                    ->with('error', 'يجب تسجيل الدخول أولاً');
            }
            $user = Auth::user();
        }

        // إذا كان الرقم مفعلاً بالفعل
        if ($user->phone_verified_at) {
            if (! Auth::check()) {
                Auth::login($user);
            }

            return redirect()->route('student.dashboard');
        }

        if (! $user->phone) {
            return redirect()->route('register')
                ->with('error', 'رقم الهاتف غير موجود');
        }

        $activeOtp = $this->getLatestActiveOtp($user->phone);

        return view('auth.verify-phone', [
            'user' => $user,
            'phone' => $user->phone,
            'otpExpiresAt' => $activeOtp?->expires_at?->toIso8601String(),
            'otpRemainingSeconds' => $activeOtp ? max(0, now()->diffInSeconds($activeOtp->expires_at, false)) : 0,
            'hasActiveOtp' => (bool) $activeOtp,
        ]);
    }

    /**
     * Verify phone number
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ], [
            'code.required' => 'رمز التحقق مطلوب',
            'code.size' => 'رمز التحقق يجب أن يكون 6 أرقام',
        ]);

        if (session()->has('pending_registration')) {
            $pr = session('pending_registration', []);
            $phone = $pr['phone'] ?? null;
            if (! $phone) {
                return redirect()->route('register')
                    ->with('error', 'انتهت صلاحية جلسة التسجيل. يرجى التسجيل مرة أخرى.');
            }

            $verified = $this->otpService->verifyOTP(
                $phone,
                $request->code,
                'verification'
            );

            if (! $verified) {
                throw ValidationException::withMessages([
                    'code' => 'رمز التحقق غير صحيح أو منتهي الصلاحية',
                ]);
            }

            if (User::where('phone', $phone)->whereNull('deleted_at')->exists()) {
                session()->forget('pending_registration');

                throw ValidationException::withMessages([
                    'code' => 'رقم الهاتف مسجّل مسبقاً. يرجى تسجيل الدخول أو استخدام رقم آخر.',
                ]);
            }

            $email = $pr['email'] ?? null;
            if ($email !== null && $email !== '' && User::where('email', $email)->whereNull('deleted_at')->exists()) {
                session()->forget('pending_registration');

                throw ValidationException::withMessages([
                    'code' => 'البريد الإلكتروني مسجّل مسبقاً. يرجى استخدام بريد آخر أو تسجيل الدخول.',
                ]);
            }

            $user = DB::transaction(function () use ($pr, $phone, $email) {
                $user = User::create([
                    'name' => $pr['name'],
                    'email' => ($email !== null && $email !== '') ? $email : null,
                    'password' => $pr['password_hash'],
                    'phone' => $phone,
                    'is_active' => true,
                    'phone_verified_at' => now(),
                ]);
                $user->assignRole('student');

                return $user;
            });

            event(new Registered($user));

            session()->forget(['pending_registration', 'pending_verification_user_id']);

            Auth::login($user);

            return redirect()->route('student.dashboard')
                ->with('success', 'تم إنشاء حسابك والتحقق من رقم الهاتف بنجاح');
        }

        $userId = session('pending_verification_user_id');

        if ($userId) {
            $user = User::find($userId);
            if (! $user) {
                return redirect()->route('register')
                    ->with('error', 'انتهت صلاحية جلسة التحقق. يرجى التسجيل مرة أخرى.');
            }
        } else {
            if (! Auth::check()) {
                return redirect()->route('login')
                    ->with('error', 'يجب تسجيل الدخول أولاً');
            }
            $user = Auth::user();
        }

        if (! $user->phone) {
            throw ValidationException::withMessages([
                'code' => 'رقم الهاتف غير موجود',
            ]);
        }

        $verified = $this->otpService->verifyOTP(
            $user->phone,
            $request->code,
            'verification'
        );

        if (! $verified) {
            throw ValidationException::withMessages([
                'code' => 'رمز التحقق غير صحيح أو منتهي الصلاحية',
            ]);
        }

        $user->update([
            'is_active' => true,
            'phone_verified_at' => now(),
        ]);

        if (! Auth::check()) {
            Auth::login($user);
        }

        session()->forget('pending_verification_user_id');

        return redirect()->route('student.dashboard')
            ->with('success', 'تم التحقق من رقم الهاتف وتفعيل حسابك بنجاح');
    }

    /**
     * Send verification code
     */
    public function send(Request $request)
    {
        if (session()->has('pending_registration')) {
            $data = session('pending_registration', []);
            $phone = $data['phone'] ?? null;
            if (! $phone) {
                return response()->json([
                    'success' => false,
                    'message' => 'انتهت صلاحية جلسة التسجيل',
                ], 400);
            }

            try {
                $otp = $this->otpService->generateOTP(null, $phone, 'verification');

                $provider = $request->input('provider', SystemSetting::get('otp_provider', 'sms'));
                $this->otpService->sendOTP($otp, $provider);

                return response()->json([
                    'success' => true,
                    'message' => 'تم إرسال رمز التحقق بنجاح',
                    'expires_at' => $otp->expires_at?->toIso8601String(),
                    'remaining_seconds' => max(0, now()->diffInSeconds($otp->expires_at, false)),
                    'resend_available_at' => $otp->expires_at?->toIso8601String(),
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 400);
            }
        }

        $userId = session('pending_verification_user_id');

        if ($userId) {
            $user = User::find($userId);
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'انتهت صلاحية جلسة التحقق',
                ], 400);
            }
        } else {
            if (! Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'يجب تسجيل الدخول أولاً',
                ], 401);
            }
            $user = Auth::user();
        }

        if (! $user->phone) {
            return response()->json([
                'success' => false,
                'message' => 'رقم الهاتف غير موجود',
            ], 400);
        }

        try {
            $otp = $this->otpService->generateOTP($user, $user->phone, 'verification');

            $provider = $request->input('provider', SystemSetting::get('otp_provider', 'sms'));
            $this->otpService->sendOTP($otp, $provider);

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال رمز التحقق بنجاح',
                'expires_at' => $otp->expires_at?->toIso8601String(),
                'remaining_seconds' => max(0, now()->diffInSeconds($otp->expires_at, false)),
                'resend_available_at' => $otp->expires_at?->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    private function getLatestActiveOtp(string $phone): ?OTPCode
    {
        return OTPCode::where('phone', $phone)
            ->where('type', 'verification')
            ->valid()
            ->orderByDesc('id')
            ->first();
    }
}
