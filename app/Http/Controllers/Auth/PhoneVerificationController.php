<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SMS\OTPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\SystemSetting;

class PhoneVerificationController extends Controller
{
    public function __construct(
        private OTPService $otpService
    ) {
        $this->middleware('auth');
    }

    /**
     * Show phone verification page
     */
    public function show()
    {
        $user = Auth::user();

        if ($user->phone_verified_at) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-phone');
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

        $user = Auth::user();

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

        return redirect()->route('dashboard')
            ->with('success', 'تم التحقق من رقم الهاتف بنجاح');
    }

    /**
     * Send verification code
     */
    public function send(Request $request)
    {
        $user = Auth::user();

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
