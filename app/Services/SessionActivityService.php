<?php

namespace App\Services;

use App\Models\SessionActivity;
use Illuminate\Http\Request;

class SessionActivityService
{
    /**
     * تسجيل بداية الجلسة
     */
    public function logSessionStart(int|string $sessionId, Request $request): SessionActivity
    {
        return $this->logActivity(
            $sessionId,
            'session_start',
            $request,
            [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
            $request->fullUrl()
        );
    }

    /**
     * تسجيل نهاية الجلسة
     */
    public function logSessionEnd(int|string $sessionId, Request $request): SessionActivity
    {
        return $this->logActivity(
            $sessionId,
            'session_end',
            $request,
            [
                'ip_address' => $request->ip(),
            ],
            $request->fullUrl()
        );
    }

    /**
     * تسجيل نشاط عام
     */
    public function logActivity(
        int|string $sessionId,
        string $activityType,
        Request $request,
        array $activityDetails = [],
        ?string $pageUrl = null
    ): SessionActivity {
        return SessionActivity::create([
            'user_session_id' => $sessionId,
            'activity_type' => $activityType,
            'activity_details' => $activityDetails,
            'page_url' => $pageUrl ?? $request->fullUrl(),
            'occurred_at' => now(),
        ]);
    }

    /**
     * الحصول على إحصائيات الجلسة
     */
    public function getSessionStats(int|string $sessionId): array
    {
        $activities = SessionActivity::where('user_session_id', $sessionId)->get();

        $stats = [
            'total_activities' => $activities->count(),
            'page_views' => $activities->where('activity_type', 'page_view')->count(),
            'actions' => $activities->where('activity_type', 'action')->count(),
            'disconnects' => $activities->where('activity_type', 'disconnect')->count(),
            'reconnects' => $activities->where('activity_type', 'reconnect')->count(),
            'idle_periods' => $activities->where('activity_type', 'idle_start')->count(),
            'focus_lost' => $activities->where('activity_type', 'focus_lost')->count(),
            'focus_gained' => $activities->where('activity_type', 'focus_gained')->count(),
            'first_activity' => $activities->min('occurred_at'),
            'last_activity' => $activities->max('occurred_at'),
        ];

        // حساب المدة الإجمالية إذا كانت هناك أنشطة
        if ($stats['first_activity'] && $stats['last_activity']) {
            $stats['total_duration_seconds'] = $stats['first_activity']->diffInSeconds($stats['last_activity']);
        } else {
            $stats['total_duration_seconds'] = 0;
        }

        // حساب المدة بصيغة مقروءة
        if ($stats['total_duration_seconds'] > 0) {
            $hours = floor($stats['total_duration_seconds'] / 3600);
            $minutes = floor(($stats['total_duration_seconds'] % 3600) / 60);
            $seconds = $stats['total_duration_seconds'] % 60;

            if ($hours > 0) {
                $stats['total_duration_formatted'] = sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
            } else {
                $stats['total_duration_formatted'] = sprintf('%d:%02d', $minutes, $seconds);
            }
        } else {
            $stats['total_duration_formatted'] = '00:00';
        }

        return $stats;
    }
}

