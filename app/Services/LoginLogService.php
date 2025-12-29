<?php

namespace App\Services;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\Request;

class LoginLogService
{
    /**
     * تسجيل محاولة دخول
     */
    public static function logLogin(
        ?User $user,
        Request|object $request,
        bool $isSuccessful,
        ?string $failureReason = null
    ): LoginLog {
        // استخراج معلومات من Request
        $ipAddress = method_exists($request, 'ip') ? $request->ip() : ($request->ip ?? null);
        $userAgent = method_exists($request, 'userAgent') ? $request->userAgent() : ($request->userAgent ?? null);
        
        // تحليل User Agent
        $deviceInfo = self::parseUserAgent($userAgent ?? '');

        return LoginLog::create([
            'user_id' => $user?->id,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'device_type' => $deviceInfo['device_type'],
            'browser' => $deviceInfo['browser'],
            'browser_version' => $deviceInfo['browser_version'],
            'platform' => $deviceInfo['platform'],
            'platform_version' => $deviceInfo['platform_version'],
            'is_successful' => $isSuccessful,
            'failure_reason' => $failureReason,
            'login_at' => now(),
        ]);
    }

    /**
     * تسجيل تسجيل خروج
     */
    public static function logLogout(int $userId, ?string $sessionId = null): void
    {
        // البحث عن آخر سجل دخول ناجح للمستخدم
        $loginLog = LoginLog::where('user_id', $userId)
            ->where('is_successful', true)
            ->whereNull('logout_at')
            ->latest('login_at')
            ->first();

        if ($loginLog) {
            $loginLog->update([
                'logout_at' => now(),
                'session_id' => $sessionId,
                'session_duration_seconds' => $loginLog->login_at 
                    ? $loginLog->login_at->diffInSeconds(now()) 
                    : null,
            ]);
        }
    }

    /**
     * تحليل User Agent لاستخراج معلومات المتصفح والجهاز
     */
    protected static function parseUserAgent(string $userAgent): array
    {
        $default = [
            'device_type' => 'desktop',
            'browser' => 'Unknown',
            'browser_version' => null,
            'platform' => 'Unknown',
            'platform_version' => null,
        ];

        if (empty($userAgent)) {
            return $default;
        }

        // تحديد نوع الجهاز
        $deviceType = 'desktop';
        if (preg_match('/mobile|android|iphone|ipad|ipod|blackberry|iemobile|opera mini/i', $userAgent)) {
            $deviceType = 'mobile';
        } elseif (preg_match('/tablet|ipad|playbook|silk/i', $userAgent)) {
            $deviceType = 'tablet';
        }

        // تحديد المتصفح
        $browser = 'Unknown';
        $browserVersion = null;
        
        if (preg_match('/Chrome\/(\d+)/i', $userAgent, $matches)) {
            $browser = 'Chrome';
            $browserVersion = $matches[1] ?? null;
        } elseif (preg_match('/Firefox\/(\d+)/i', $userAgent, $matches)) {
            $browser = 'Firefox';
            $browserVersion = $matches[1] ?? null;
        } elseif (preg_match('/Safari\/(\d+)/i', $userAgent, $matches) && !preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Safari';
            $browserVersion = $matches[1] ?? null;
        } elseif (preg_match('/Edge\/(\d+)/i', $userAgent, $matches)) {
            $browser = 'Edge';
            $browserVersion = $matches[1] ?? null;
        } elseif (preg_match('/Edg\/(\d+)/i', $userAgent, $matches)) {
            $browser = 'Edge';
            $browserVersion = $matches[1] ?? null;
        } elseif (preg_match('/Opera\/(\d+)/i', $userAgent, $matches) || preg_match('/OPR\/(\d+)/i', $userAgent, $matches)) {
            $browser = 'Opera';
            $browserVersion = $matches[1] ?? null;
        } elseif (preg_match('/MSIE (\d+)/i', $userAgent, $matches)) {
            $browser = 'Internet Explorer';
            $browserVersion = $matches[1] ?? null;
        }

        // تحديد النظام الأساسي
        $platform = 'Unknown';
        $platformVersion = null;
        
        if (preg_match('/Windows NT ([\d.]+)/i', $userAgent, $matches)) {
            $platform = 'Windows';
            $platformVersion = $matches[1] ?? null;
        } elseif (preg_match('/Mac OS X ([\d_]+)/i', $userAgent, $matches)) {
            $platform = 'macOS';
            $platformVersion = str_replace('_', '.', $matches[1] ?? '');
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $platform = 'Linux';
        } elseif (preg_match('/Android ([\d.]+)/i', $userAgent, $matches)) {
            $platform = 'Android';
            $platformVersion = $matches[1] ?? null;
        } elseif (preg_match('/iPhone OS ([\d_]+)/i', $userAgent, $matches) || preg_match('/iOS ([\d_]+)/i', $userAgent, $matches)) {
            $platform = 'iOS';
            $platformVersion = str_replace('_', '.', $matches[1] ?? '');
        } elseif (preg_match('/iPad.*OS ([\d_]+)/i', $userAgent, $matches)) {
            $platform = 'iOS';
            $platformVersion = str_replace('_', '.', $matches[1] ?? '');
        }

        return [
            'device_type' => $deviceType,
            'browser' => $browser,
            'browser_version' => $browserVersion,
            'platform' => $platform,
            'platform_version' => $platformVersion,
        ];
    }
}

