<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SMS\OTPService;
use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PhoneVerificationController extends Controller
{
    public function __construct(
        private OTPService $otpService
    ) {
        // إزالة middleware('auth') للسماح بالوصول للمستخدمين غير المسجلين دخول
        // سنتحقق من المستخدم يدوياً في show() و verify()
    }

    /**
     * Show phone verification page
     */
    public function show()
    {
        // محاولة الحصول على المستخدم من session (للمستخدمين الجدد)
        $userId = session('pending_verification_user_id');
        
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                return redirect()->route('register')
                    ->with('error', 'انتهت صلاحية جلسة التحقق. يرجى التسجيل مرة أخرى.');
            }
        } else {
            // للمستخدمين المسجلين دخول
            if (!Auth::check()) {
                return redirect()->route('login')
                    ->with('error', 'يجب تسجيل الدخول أولاً');
            }
            $user = Auth::user();
        }

        // إذا كان الرقم مفعلاً بالفعل
        if ($user->phone_verified_at) {
            // إذا كان المستخدم غير مسجل دخول، سجله دخول
            if (!Auth::check()) {
                Auth::login($user);
            }
            return redirect()->route('student.dashboard');
        }

        // التحقق من وجود رقم الهاتف
        if (!$user->phone) {
            return redirect()->route('register')
                ->with('error', 'رقم الهاتف غير موجود');
        }

        return view('auth.verify-phone', [
            'user' => $user,
            'phone' => $user->phone,
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

        // محاولة الحصول على المستخدم من session (للمستخدمين الجدد)
        $userId = session('pending_verification_user_id');
        
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                return redirect()->route('register')
                    ->with('error', 'انتهت صلاحية جلسة التحقق. يرجى التسجيل مرة أخرى.');
            }
        } else {
            // للمستخدمين المسجلين دخول
            if (!Auth::check()) {
                return redirect()->route('login')
                    ->with('error', 'يجب تسجيل الدخول أولاً');
            }
            $user = Auth::user();
        }

        if (!$user->phone) {
            throw ValidationException::withMessages([
                'code' => 'رقم الهاتف غير موجود',
            ]);
        }

        $verified = $this->otpService->verifyOTP(
            $user->phone,
            $request->code,
            'verification'
        );

        if (!$verified) {
            throw ValidationException::withMessages([
                'code' => 'رمز التحقق غير صحيح أو منتهي الصلاحية',
            ]);
        }

        // تفعيل الحساب
        $user->update([
            'is_active' => true,
            'phone_verified_at' => now(),
        ]);

        // تسجيل الدخول إذا لم يكن مسجل دخول
        if (!Auth::check()) {
            Auth::login($user);
        }

        // تنظيف session
        session()->forget('pending_verification_user_id');

        return redirect()->route('student.dashboard')
            ->with('success', 'تم التحقق من رقم الهاتف وتفعيل حسابك بنجاح');
    }

    /**
     * Send verification code
     */
    public function send(Request $request)
    {
        // محاولة الحصول على المستخدم من session (للمستخدمين الجدد)
        $userId = session('pending_verification_user_id');
        
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'انتهت صلاحية جلسة التحقق',
                ], 400);
            }
        } else {
            // للمستخدمين المسجلين دخول
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'يجب تسجيل الدخول أولاً',
                ], 401);
            }
            $user = Auth::user();
        }

        if (!$user->phone) {
            return response()->json([
                'success' => false,
                'message' => 'رقم الهاتف غير موجود',
            ], 400);
        }

        try {
            $otp = $this->otpService->generateOTP($user, $user->phone, 'verification');
            
            // Determine provider (default: sms, or from request)
            $provider = $request->input('provider', SystemSetting::get('otp_provider', 'sms'));
            $this->otpService->sendOTP($otp, $provider);

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال رمز التحقق بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
