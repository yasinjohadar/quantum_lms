<?php

namespace App\Services;

use App\Models\LoginLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoginLogService
{
    /**
     * تسجيل محاولة دخول
     */
    public static function logLogin($user, Request $request, $isSuccessful = true, $failureReason = null)
    {
        try {
            $userAgent = $request->userAgent();
            $ipAddress = $request->ip();
            
            // تحليل User Agent
            $deviceInfo = self::parseUserAgent($userAgent);
            
            // معلومات جغرافية (يمكن إضافة API لاحقاً)
            $geoInfo = self::getGeoInfo($ipAddress);
            
            $data = [
                'user_id' => $isSuccessful && $user ? $user->id : null,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'device_type' => $deviceInfo['device_type'],
                'browser' => $deviceInfo['browser'],
                'browser_version' => $deviceInfo['browser_version'],
                'platform' => $deviceInfo['platform'],
                'platform_version' => $deviceInfo['platform_version'],
                'country' => $geoInfo['country'] ?? null,
                'city' => $geoInfo['city'] ?? null,
                'is_successful' => $isSuccessful,
                'failure_reason' => $failureReason,
                'login_at' => now(),
                'session_id' => $request->session()->getId(),
                'meta' => [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                ],
            ];
            
            return LoginLog::create($data);
        } catch (\Exception $e) {
            Log::error('Error logging login attempt: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * تسجيل خروج المستخدم
     */
    public static function logLogout($userId, $sessionId = null)
    {
        try {
            $query = LoginLog::where('user_id', $userId)
                ->where('is_successful', true)
                ->whereNull('logout_at');
            
            if ($sessionId) {
                $query->where('session_id', $sessionId);
            }
            
            $loginLog = $query->latest('login_at')->first();
            
            if ($loginLog) {
                $loginLog->update([
                    'logout_at' => now(),
                    'session_duration_seconds' => now()->diffInSeconds($loginLog->login_at),
                ]);
            }
            
            return $loginLog;
        } catch (\Exception $e) {
            Log::error('Error logging logout: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * تحليل User Agent
     */
    private static function parseUserAgent($userAgent)
    {
        $deviceType = 'Unknown';
        $browser = 'Unknown';
        $browserVersion = null;
        $platform = 'Unknown';
        $platformVersion = null;
        
        // تحديد نوع الجهاز
        if (preg_match('/Mobile|Android|iPhone|iPad/i', $userAgent)) {
            $deviceType = 'Mobile';
        } elseif (preg_match('/Tablet|iPad/i', $userAgent)) {
            $deviceType = 'Tablet';
        } else {
            $deviceType = 'Desktop';
        }
        
        // تحديد المتصفح
        if (preg_match('/Chrome\/([0-9.]+)/i', $userAgent, $matches)) {
            $browser = 'Chrome';
            $browserVersion = $matches[1];
        } elseif (preg_match('/Firefox\/([0-9.]+)/i', $userAgent, $matches)) {
            $browser = 'Firefox';
            $browserVersion = $matches[1];
        } elseif (preg_match('/Safari\/([0-9.]+)/i', $userAgent, $matches)) {
            $browser = 'Safari';
            $browserVersion = $matches[1];
        } elseif (preg_match('/Edge\/([0-9.]+)/i', $userAgent, $matches)) {
            $browser = 'Edge';
            $browserVersion = $matches[1];
        } elseif (preg_match('/Opera\/([0-9.]+)/i', $userAgent, $matches)) {
            $browser = 'Opera';
            $browserVersion = $matches[1];
        }
        
        // تحديد النظام
        if (preg_match('/Windows NT ([0-9.]+)/i', $userAgent, $matches)) {
            $platform = 'Windows';
            $platformVersion = $matches[1];
        } elseif (preg_match('/Mac OS X ([0-9_]+)/i', $userAgent, $matches)) {
            $platform = 'macOS';
            $platformVersion = str_replace('_', '.', $matches[1]);
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $platform = 'Linux';
        } elseif (preg_match('/Android ([0-9.]+)/i', $userAgent, $matches)) {
            $platform = 'Android';
            $platformVersion = $matches[1];
        } elseif (preg_match('/iPhone OS ([0-9_]+)/i', $userAgent, $matches)) {
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
     * الحصول على المعلومات الجغرافية (يمكن تحسينه لاحقاً باستخدام API)
     */
    private static function getGeoInfo($ipAddress)
    {
        // يمكن إضافة API مثل ipapi.co أو ip-api.com هنا
        // حالياً نرجع null
        return [
            'country' => null,
            'city' => null,
        ];
    }
}


