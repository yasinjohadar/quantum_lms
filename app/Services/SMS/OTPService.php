<?php

namespace App\Services\SMS;

use App\Models\OTPCode;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\SMS\SMSService;
use App\Services\WhatsApp\SendWhatsAppMessage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OTPService
{
    protected SMSService $smsService;
    protected ?SendWhatsAppMessage $whatsappService;

    public function __construct(SMSService $smsService, ?SendWhatsAppMessage $whatsappService = null)
    {
        $this->smsService = $smsService;
        $this->whatsappService = $whatsappService;
    }

    /**
     * Generate OTP code for user
     */
    public function generateOTP(User $user, string $phone, string $type = 'verification'): OTPCode
    {
        // Check rate limiting
        $this->checkRateLimit($phone, $type);

        // Invalidate any existing valid OTP for this phone and type
        OTPCode::where('phone', $phone)
            ->where('type', $type)
            ->valid()
            ->update(['used_at' => now()]);

        // Generate 6-digit code
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // مدة الصلاحية من الإعدادات (افتراضي 5 دقائق، بحد أدنى 1 وأقصى 60)
        $expiresMinutes = (int) SystemSetting::get('otp_expires_minutes', 5);
        $expiresMinutes = max(1, min(60, $expiresMinutes));

        // Create OTP record
        $otp = OTPCode::create([
            'user_id' => $user->id ?? null,
            'phone' => $phone,
            'code' => $code,
            'type' => $type,
            'expires_at' => now()->addMinutes($expiresMinutes),
        ]);

        // Increment rate limit counter
        $this->incrementRateLimit($phone, $type);

        return $otp;
    }

    /**
     * Verify OTP code
     */
    public function verifyOTP(string $phone, string $code, string $type = 'verification'): bool
    {
        $otp = OTPCode::where('phone', $phone)
            ->where('code', $code)
            ->where('type', $type)
            ->valid()
            ->first();

        if (!$otp) {
            return false;
        }

        // Mark as verified and used
        $otp->markAsVerified();
        $otp->markAsUsed();

        // If type is verification, mark user's phone as verified
        if ($type === 'verification' && $otp->user_id) {
            $user = User::find($otp->user_id);
            if ($user) {
                $user->update(['phone_verified_at' => now()]);
            }
        }

        return true;
    }

    /**
     * Send OTP via SMS or WhatsApp
     */
    public function sendOTP(OTPCode $otp, string $provider = 'sms'): bool
    {
        // Get custom message template from settings or use default (يمكن تخصيص النص من الإعدادات)
        $template = SystemSetting::get('otp_message_template', 'رمز التحقق الخاص بك هو: {code} - صالح لمدة {expires_in} دقائق');
        
        // عرض المدة المُعدّة في الإعدادات (5 دقائق) وليس الوقت المتبقي حتى انتهاء الصلاحية
        $expiresInMinutes = (int) SystemSetting::get('otp_expires_minutes', 5);
        $expiresInMinutes = max(1, min(60, $expiresInMinutes));
        
        // Replace placeholders
        $message = str_replace(
            ['{code}', '{expires_in}'],
            [$otp->code, $expiresInMinutes],
            $template
        );

        if ($provider === 'whatsapp') {
            // Try to resolve WhatsApp service if not injected
            if (!$this->whatsappService) {
                try {
                    $this->whatsappService = app(\App\Services\WhatsApp\SendWhatsAppMessage::class);
                } catch (\Exception $e) {
                    Log::error('WhatsApp service is not available: ' . $e->getMessage());
                    return false;
                }
            }

            try {
                // OTP verification messages should be sent immediately (without queue)
                // If it doesn't throw exception, consider it successful
                $whatsappMessage = $this->whatsappService->sendTextNow(
                    $otp->phone,
                    $message,
                    false,
                    \App\Models\WhatsAppMessage::CATEGORY_VERIFICATION
                );
                
                Log::info('OTP sent via WhatsApp successfully', [
                    'otp_id' => $otp->id,
                    'phone' => $otp->phone,
                    'whatsapp_message_id' => $whatsappMessage->id ?? 'N/A',
                ]);
                
                return true;
            } catch (\Exception $e) {
                Log::error('Error sending OTP via WhatsApp: ' . $e->getMessage(), [
                    'otp_id' => $otp->id,
                    'phone' => $otp->phone,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return false;
            }
        }

        return $this->smsService->send($otp->phone, $message, [
            'type' => 'otp',
        ]);
    }

    /**
     * Resend OTP
     */
    public function resendOTP(string $phone, string $type = 'verification'): ?OTPCode
    {
        // Find user by phone if exists
        $user = User::where('phone', $phone)->first();

        if (!$user && $type === 'verification') {
            throw new \Exception('المستخدم غير موجود');
        }

        // Generate new OTP
        $otp = $this->generateOTP($user ?? new User(), $phone, $type);

        // Send OTP
        $this->sendOTP($otp);

        return $otp;
    }

    /**
     * Cleanup expired OTP codes
     */
    public function cleanupExpiredOTPs(): int
    {
        return OTPCode::expired()
            ->where('created_at', '<', now()->subDays(7))
            ->delete();
    }

    /**
     * Check rate limiting (3 attempts per 15 minutes)
     */
    protected function checkRateLimit(string $phone, string $type): void
    {
        $key = "otp_rate_limit:{$phone}:{$type}";
        $count = Cache::get($key, 0);

        if ($count >= 3) {
            throw new \Exception('تم تجاوز الحد المسموح. يرجى المحاولة مرة أخرى بعد 15 دقيقة');
        }
    }

    /**
     * Increment rate limit counter
     */
    protected function incrementRateLimit(string $phone, string $type): void
    {
        $key = "otp_rate_limit:{$phone}:{$type}";
        $count = Cache::get($key, 0);
        Cache::put($key, $count + 1, now()->addMinutes(15));
    }
}

