<?php

namespace App\Services;

use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserSessionService
{
    /**
     * إنشاء جلسة مستخدم جديدة
     */
    public function createSession(int $userId, Request $request): UserSession
    {
        $userAgent = $request->userAgent() ?? '';
        
        // استخراج معلومات المتصفح والجهاز
        $deviceInfo = $this->parseUserAgent($userAgent);

        $session = UserSession::create([
            'user_id' => $userId,
            'session_uuid' => (string) Str::uuid(),
            'session_name' => $deviceInfo['device_type'] . ' - ' . $deviceInfo['browser'],
            'session_description' => 'جلسة دخول من ' . $deviceInfo['platform'],
            'started_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $userAgent,
            'device_type' => $deviceInfo['device_type'],
            'browser' => $deviceInfo['browser'],
            'browser_version' => $deviceInfo['browser_version'],
            'platform' => $deviceInfo['platform'],
            'platform_version' => $deviceInfo['platform_version'],
            'screen_resolution' => $request->header('X-Screen-Resolution'),
            'status' => 'active',
        ]);

        return $session;
    }

    /**
     * إنهاء جميع الجلسات النشطة لمستخدم
     */
    public function endAllActiveSessions(int $userId, string $status = 'completed'): void
    {
        $sessions = UserSession::where('user_id', $userId)
            ->where('status', 'active')
            ->get();

        foreach ($sessions as $session) {
            $this->endSession($session->id, $status);
        }
    }

    /**
     * إنهاء جلسة محددة
     */
    public function endSession(int|string $sessionId, string $status = 'completed', ?string $notes = null): UserSession
    {
        $session = UserSession::findOrFail($sessionId);

        $session->update([
            'status' => $status,
            'ended_at' => now(),
            'duration_seconds' => $session->started_at ? $session->started_at->diffInSeconds(now()) : null,
            'notes' => $notes,
        ]);

        return $session->fresh();
    }

    /**
     * تحليل User Agent لاستخراج معلومات المتصفح والجهاز
     */
    protected function parseUserAgent(string $userAgent): array
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

