<?php

namespace App\Services;

use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserSessionService
{
    /**
     * إنشاء جلسة جديدة للمستخدم
     */
    public function createSession(int $userId, Request $request): UserSession
    {
        $userAgent = $request->userAgent();
        $deviceInfo = $this->parseUserAgent($userAgent);

        $session = UserSession::create([
            'user_id' => $userId,
            'session_uuid' => Str::uuid(),
            'session_name' => $deviceInfo['device_type'] . ' - ' . $deviceInfo['browser'],
            'started_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $userAgent,
            'device_type' => $deviceInfo['device_type'],
            'browser' => $deviceInfo['browser'],
            'browser_version' => $deviceInfo['browser_version'],
            'platform' => $deviceInfo['platform'],
            'platform_version' => $deviceInfo['platform_version'],
            'screen_resolution' => $request->input('screen_resolution'),
            'connection_type' => $request->input('connection_type', 'unknown'),
            'status' => 'active',
        ]);

        return $session;
    }

    /**
     * إنهاء جلسة معينة
     */
    public function endSession(string $sessionId, string $status = 'completed', ?string $notes = null): bool
    {
        $session = UserSession::findOrFail($sessionId);
        
        $session->ended_at = now();
        $session->status = $status;
        $session->notes = $notes;
        
        // حساب مدة الجلسة
        if ($session->started_at) {
            $session->duration_seconds = $session->ended_at->diffInSeconds($session->started_at);
        }
        
        return $session->save();
    }

    /**
     * إنهاء جميع الجلسات النشطة لمستخدم معين
     */
    public function endAllActiveSessions(int $userId, string $status = 'completed'): int
    {
        $sessions = UserSession::where('user_id', $userId)
            ->where('status', 'active')
            ->get();

        $endedCount = 0;
        foreach ($sessions as $session) {
            $session->ended_at = now();
            $session->status = $status;
            
            // حساب مدة الجلسة
            if ($session->started_at) {
                $session->duration_seconds = $session->ended_at->diffInSeconds($session->started_at);
            }
            
            if ($session->save()) {
                $endedCount++;
            }
        }

        return $endedCount;
    }

    /**
     * تحليل User Agent لاستخراج معلومات الجهاز
     */
    protected function parseUserAgent(?string $userAgent): array
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
            $platformVersion = $this->getWindowsVersion($matches[1]);
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
    protected function getWindowsVersion(string $version): string
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

