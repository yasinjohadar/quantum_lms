<?php

namespace App\Services;

use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserSessionService
{
    /**
     * إنشاء جلسة جديدة للمستخدم
     */
    public function createSession($userId, Request $request, $sessionName = null, $sessionDescription = null)
    {
        $userAgent = $request->userAgent();
        $parsedAgent = $this->parseUserAgent($userAgent);

        $session = UserSession::create([
            'user_id' => $userId,
            'session_uuid' => (string) Str::uuid(),
            'session_name' => $sessionName ?? 'جلسة عادية',
            'session_description' => $sessionDescription,
            'started_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $userAgent,
            'device_type' => $parsedAgent['device_type'],
            'browser' => $parsedAgent['browser'],
            'browser_version' => $parsedAgent['browser_version'],
            'platform' => $parsedAgent['platform'],
            'platform_version' => $parsedAgent['platform_version'],
            'screen_resolution' => $this->getScreenResolution($request),
            'connection_type' => $this->getConnectionType($request),
            'bandwidth_mbps' => $this->getBandwidth($request),
            'status' => 'active',
            'meta' => [
                'referrer' => $request->header('referer'),
                'accept_language' => $request->header('accept-language'),
            ],
        ]);

        return $session;
    }

    /**
     * إنهاء جلسة
     */
    public function endSession($sessionId, $status = 'completed', $notes = null)
    {
        $session = UserSession::findOrFail($sessionId);
        
        $session->update([
            'ended_at' => now(),
            'status' => $status,
            'notes' => $notes,
        ]);

        $session->calculateDuration();

        return $session;
    }

    /**
     * إنهاء جميع الجلسات النشطة للمستخدم
     */
    public function endAllActiveSessions($userId, $status = 'completed')
    {
        return UserSession::where('user_id', $userId)
            ->where('status', 'active')
            ->update([
                'ended_at' => now(),
                'status' => $status,
            ]);
    }

    /**
     * تحديث معلومات الجلسة
     */
    public function updateSession($sessionId, array $data)
    {
        $session = UserSession::findOrFail($sessionId);
        $session->update($data);
        return $session;
    }

    /**
     * تحليل User Agent
     */
    private function parseUserAgent($userAgent)
    {
        $deviceType = 'desktop';
        $browser = 'Unknown';
        $browserVersion = null;
        $platform = 'Unknown';
        $platformVersion = null;

        // تحديد نوع الجهاز
        if (preg_match('/mobile|android|iphone|ipad|ipod|blackberry|iemobile|opera mini/i', $userAgent)) {
            $deviceType = 'mobile';
            if (preg_match('/tablet|ipad/i', $userAgent)) {
                $deviceType = 'tablet';
            }
        }

        // تحديد المتصفح
        if (preg_match('/Chrome\/(\d+)/i', $userAgent, $matches)) {
            $browser = 'Chrome';
            $browserVersion = $matches[1];
        } elseif (preg_match('/Firefox\/(\d+)/i', $userAgent, $matches)) {
            $browser = 'Firefox';
            $browserVersion = $matches[1];
        } elseif (preg_match('/Safari\/(\d+)/i', $userAgent, $matches) && !preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Safari';
            $browserVersion = $matches[1];
        } elseif (preg_match('/Edge\/(\d+)/i', $userAgent, $matches)) {
            $browser = 'Edge';
            $browserVersion = $matches[1];
        } elseif (preg_match('/Opera\/(\d+)/i', $userAgent, $matches)) {
            $browser = 'Opera';
            $browserVersion = $matches[1];
        }

        // تحديد نظام التشغيل
        if (preg_match('/Windows NT (\d+\.\d+)/i', $userAgent, $matches)) {
            $platform = 'Windows';
            $platformVersion = $matches[1];
        } elseif (preg_match('/Mac OS X (\d+[._]\d+)/i', $userAgent, $matches)) {
            $platform = 'macOS';
            $platformVersion = str_replace('_', '.', $matches[1]);
        } elseif (preg_match('/Android (\d+\.\d+)/i', $userAgent, $matches)) {
            $platform = 'Android';
            $platformVersion = $matches[1];
        } elseif (preg_match('/iPhone OS (\d+[._]\d+)/i', $userAgent, $matches)) {
            $platform = 'iOS';
            $platformVersion = str_replace('_', '.', $matches[1]);
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $platform = 'Linux';
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
     * الحصول على دقة الشاشة من الطلب
     */
    private function getScreenResolution(Request $request)
    {
        // يمكن إرسال هذه المعلومات من الـ frontend
        $width = $request->header('X-Screen-Width');
        $height = $request->header('X-Screen-Height');
        
        if ($width && $height) {
            return "{$width}x{$height}";
        }

        return null;
    }

    /**
     * تحديد نوع الاتصال
     */
    private function getConnectionType(Request $request)
    {
        // يمكن إرسال هذه المعلومات من الـ frontend
        $connectionType = $request->header('X-Connection-Type');
        
        if (in_array($connectionType, ['wifi', 'cellular', 'ethernet'])) {
            return $connectionType;
        }

        return 'unknown';
    }

    /**
     * الحصول على عرض النطاق
     */
    private function getBandwidth(Request $request)
    {
        // يمكن إرسال هذه المعلومات من الـ frontend
        $bandwidth = $request->header('X-Bandwidth-Mbps');
        
        if ($bandwidth && is_numeric($bandwidth)) {
            return (float) $bandwidth;
        }

        return null;
    }

    /**
     * الحصول على معلومات جغرافية من IP
     */
    public function getGeoInfo($ip)
    {
        // Placeholder - يمكن استخدام خدمة مثل ipapi.co أو ip-api.com
        // مثال: https://ipapi.co/{ip}/json/
        return [
            'country' => null,
            'city' => null,
        ];
    }
}


