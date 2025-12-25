<?php

namespace App\Services;

use App\Models\LoginLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LoginLogService
{
    /**
     * تسجيل محاولة دخول
     */
    public static function logLogin(
        $user,
        $request,
        bool $isSuccessful,
        ?string $failureReason = null
    ): LoginLog {
        // إذا كان $request هو LoginRequest object وليس Request
        if (method_exists($request, 'ip')) {
            $ipAddress = $request->ip();
            $userAgent = $request->userAgent();
        } elseif (is_object($request) && property_exists($request, 'ip')) {
            $ipAddress = $request->ip();
            $userAgent = $request->userAgent() ?? $request->header('User-Agent');
        } else {
            $ipAddress = request()->ip();
            $userAgent = request()->userAgent();
        }

        $deviceInfo = self::parseUserAgent($userAgent);

        $logData = [
            'user_id' => $user ? $user->id : null,
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
            'session_id' => session()->getId(),
        ];

        return LoginLog::create($logData);
    }

    /**
     * تسجيل تسجيل خروج
     */
    public static function logLogout(int $userId, string $sessionId): void
    {
        $log = LoginLog::where('user_id', $userId)
            ->where('session_id', $sessionId)
            ->where('is_successful', true)
            ->whereNull('logout_at')
            ->latest('login_at')
            ->first();

        if ($log) {
            $log->logout_at = now();
            
            // حساب مدة الجلسة
            if ($log->login_at) {
                $log->session_duration_seconds = $log->logout_at->diffInSeconds($log->login_at);
            }
            
            $log->save();
        }
    }

    /**
     * تحليل User Agent لاستخراج معلومات الجهاز
     */
    protected static function parseUserAgent(?string $userAgent): array
    {
        $defaultInfo = [
            'device_type' => 'desktop',
            'browser' => 'Unknown',
            'browser_version' => null,
            'platform' => 'Unknown',
            'platform_version' => null,
        ];

        if (!$userAgent) {
            return $defaultInfo;
        }

        // تحديد نوع الجهاز
        $deviceType = 'desktop';
        if (preg_match('/Mobile|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $userAgent)) {
            if (preg_match('/iPad/i', $userAgent)) {
                $deviceType = 'tablet';
            } else {
                $deviceType = 'mobile';
            }
        } elseif (preg_match('/Tablet|iPad/i', $userAgent)) {
            $deviceType = 'tablet';
        }

        // تحديد المتصفح
        $browser = 'Unknown';
        $browserVersion = null;

        if (preg_match('/MSIE|Trident/i', $userAgent)) {
            $browser = 'Internet Explorer';
            if (preg_match('/MSIE\s([\d.]+)/', $userAgent, $matches)) {
                $browserVersion = $matches[1];
            } elseif (preg_match('/rv:([\d.]+)/', $userAgent, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (preg_match('/Edge\/([\d.]+)/', $userAgent, $matches)) {
            $browser = 'Edge';
            $browserVersion = $matches[1];
        } elseif (preg_match('/Edg\/([\d.]+)/', $userAgent, $matches)) {
            $browser = 'Edge (Chromium)';
            $browserVersion = $matches[1];
        } elseif (preg_match('/Chrome\/([\d.]+)/', $userAgent, $matches)) {
            $browser = 'Chrome';
            $browserVersion = $matches[1];
        } elseif (preg_match('/Safari\/([\d.]+)/', $userAgent, $matches) && !preg_match('/Chrome/', $userAgent)) {
            $browser = 'Safari';
            $browserVersion = $matches[1];
        } elseif (preg_match('/Firefox\/([\d.]+)/', $userAgent, $matches)) {
            $browser = 'Firefox';
            $browserVersion = $matches[1];
        } elseif (preg_match('/Opera\/([\d.]+)/', $userAgent, $matches)) {
            $browser = 'Opera';
            $browserVersion = $matches[1];
        }

        // تحديد النظام الأساسي
        $platform = 'Unknown';
        $platformVersion = null;

        if (preg_match('/Windows NT ([\d.]+)/', $userAgent, $matches)) {
            $platform = 'Windows';
            $platformVersion = self::getWindowsVersion($matches[1]);
        } elseif (preg_match('/Mac OS X ([\d_]+)/', $userAgent, $matches)) {
            $platform = 'macOS';
            $platformVersion = str_replace('_', '.', $matches[1]);
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $platform = 'Linux';
            if (preg_match('/(Ubuntu|Debian|Fedora|CentOS)/i', $userAgent, $matches)) {
                $platform = $matches[1];
            }
        } elseif (preg_match('/Android ([\d.]+)/', $userAgent, $matches)) {
            $platform = 'Android';
            $platformVersion = $matches[1];
        } elseif (preg_match('/iOS ([\d_]+)/', $userAgent, $matches)) {
            $platform = 'iOS';
            $platformVersion = str_replace('_', '.', $matches[1]);
        }

        return [
            'device_type' => $deviceType,
            'browser' => $browser,
            'browser_version' => $browserVersion,
            'platform' => $platform,
            'platform_version' => $platformVersion,
        ];
    }

    /**
     * الحصول على اسم إصدار Windows من رقم الإصدار
     */
    protected static function getWindowsVersion(string $version): string
    {
        $versions = [
            '10.0' => '10/11',
            '6.3' => '8.1',
            '6.2' => '8',
            '6.1' => '7',
            '6.0' => 'Vista',
            '5.1' => 'XP',
        ];

        return $versions[$version] ?? $version;
    }
}

