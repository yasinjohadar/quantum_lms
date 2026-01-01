<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SMS\OTPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\SystemSetting;

class OTPController extends Controller
{
    public function __construct(
        private OTPService $otpService
    ) {}

    /**
     * Send OTP
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:20',
            'type' => 'required|string|in:login,verification,password_reset',
        ], [
            'phone.required' => 'رقم الهاتف مطلوب',
            'type.required' => 'نوع OTP مطلوب',
            'type.in' => 'نوع OTP غير صالح',
        ]);

        try {
            // Find user by phone if exists
            $user = \App\Models\User::where('phone', $validated['phone'])->first();

            if (!$user && $validated['type'] === 'login') {
                throw ValidationException::withMessages([
                    'phone' => 'رقم الهاتف غير مسجل',
                ]);
            }

            // Generate and send OTP
            $otp = $this->otpService->generateOTP(
                $user ?? new \App\Models\User(),
                $validated['phone'],
                $validated['type']
            );

            // Determine provider (default: sms, or from request)
            $provider = $request->input('provider', SystemSetting::get('otp_provider', 'sms'));

            // Send OTP via SMS or WhatsApp
            $this->otpService->sendOTP($otp, $provider);

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال رمز التحقق بنجاح',
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error sending OTP: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Verify OTP
     */
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:20',
            'code' => 'required|string|size:6',
            'type' => 'required|string|in:login,verification,password_reset',
        ], [
            'phone.required' => 'رقم الهاتف مطلوب',
            'code.required' => 'رمز التحقق مطلوب',
            'code.size' => 'رمز التحقق يجب أن يكون 6 أرقام',
            'type.required' => 'نوع OTP مطلوب',
            'type.in' => 'نوع OTP غير صالح',
        ]);

        try {
            $verified = $this->otpService->verifyOTP(
                $validated['phone'],
                $validated['code'],
                $validated['type']
            );

            if (!$verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'رمز التحقق غير صحيح أو منتهي الصلاحية',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم التحقق بنجاح',
            ]);
        } catch (\Exception $e) {
            Log::error('Error verifying OTP: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Resend OTP
     */
    public function resend(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:20',
            'type' => 'required|string|in:login,verification,password_reset',
        ], [
            'phone.required' => 'رقم الهاتف مطلوب',
            'type.required' => 'نوع OTP مطلوب',
            'type.in' => 'نوع OTP غير صالح',
        ]);

        try {
            $otp = $this->otpService->resendOTP(
                $validated['phone'],
                $validated['type']
            );

            return response()->json([
                'success' => true,
                'message' => 'تم إعادة إرسال رمز التحقق بنجاح',
            ]);
        } catch (\Exception $e) {
            Log::error('Error resending OTP: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
