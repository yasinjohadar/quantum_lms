<?php

namespace App\Services;

use App\Models\SessionActivity;
use Illuminate\Http\Request;

class SessionActivityService
{
    /**
     * تسجيل نشاط جديد
     */
    public function logActivity($sessionId, $activityType, Request $request = null, $details = null, $pageUrl = null)
    {
        $activity = SessionActivity::create([
            'user_session_id' => $sessionId,
            'activity_type' => $activityType,
            'activity_details' => $details ?? $this->extractActivityDetails($request),
            'page_url' => $pageUrl ?? ($request ? $request->fullUrl() : null),
            'occurred_at' => now(),
        ]);

        return $activity;
    }

    /**
     * تسجيل بداية الجلسة
     */
    public function logSessionStart($sessionId, Request $request = null)
    {
        return $this->logActivity($sessionId, 'session_start', $request);
    }

    /**
     * تسجيل نهاية الجلسة
     */
    public function logSessionEnd($sessionId, Request $request = null)
    {
        return $this->logActivity($sessionId, 'session_end', $request);
    }

    /**
     * تسجيل عرض صفحة
     */
    public function logPageView($sessionId, $pageUrl, Request $request = null)
    {
        return $this->logActivity($sessionId, 'page_view', $request, null, $pageUrl);
    }

    /**
     * تسجيل إجراء
     */
    public function logAction($sessionId, $actionName, $details = null, Request $request = null)
    {
        $details = array_merge($details ?? [], [
            'action_name' => $actionName,
        ]);

        return $this->logActivity($sessionId, 'action', $request, $details);
    }

    /**
     * تسجيل انقطاع
     */
    public function logDisconnect($sessionId, Request $request = null)
    {
        return $this->logActivity($sessionId, 'disconnect', $request);
    }

    /**
     * تسجيل إعادة اتصال
     */
    public function logReconnect($sessionId, Request $request = null)
    {
        return $this->logActivity($sessionId, 'reconnect', $request);
    }

    /**
     * تسجيل بداية الخمول
     */
    public function logIdleStart($sessionId, Request $request = null)
    {
        return $this->logActivity($sessionId, 'idle_start', $request);
    }

    /**
     * تسجيل نهاية الخمول
     */
    public function logIdleEnd($sessionId, Request $request = null)
    {
        return $this->logActivity($sessionId, 'idle_end', $request);
    }

    /**
     * تسجيل فقدان التركيز
     */
    public function logFocusLost($sessionId, Request $request = null)
    {
        return $this->logActivity($sessionId, 'focus_lost', $request);
    }

    /**
     * تسجيل استعادة التركيز
     */
    public function logFocusGained($sessionId, Request $request = null)
    {
        return $this->logActivity($sessionId, 'focus_gained', $request);
    }

    /**
     * استخراج تفاصيل النشاط من الطلب
     */
    private function extractActivityDetails(Request $request = null)
    {
        if (!$request) {
            return null;
        }

        return [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->header('referer'),
            'method' => $request->method(),
        ];
    }

    /**
     * الحصول على إحصائيات الأنشطة لجلسة
     */
    public function getSessionStats($sessionId)
    {
        $activities = SessionActivity::where('user_session_id', $sessionId)->get();

        return [
            'total' => $activities->count(),
            'page_views' => $activities->where('activity_type', 'page_view')->count(),
            'actions' => $activities->where('activity_type', 'action')->count(),
            'disconnects' => $activities->where('activity_type', 'disconnect')->count(),
            'reconnects' => $activities->where('activity_type', 'reconnect')->count(),
            'idle_time' => $this->calculateIdleTime($activities),
            'unique_pages' => $activities->where('activity_type', 'page_view')
                ->pluck('page_url')
                ->unique()
                ->count(),
        ];
    }

    /**
     * حساب وقت الخمول
     */
    private function calculateIdleTime($activities)
    {
        $idleStart = null;
        $totalIdle = 0;

        foreach ($activities->sortBy('occurred_at') as $activity) {
            if ($activity->activity_type === 'idle_start') {
                $idleStart = $activity->occurred_at;
            } elseif ($activity->activity_type === 'idle_end' && $idleStart) {
                $totalIdle += $activity->occurred_at->diffInSeconds($idleStart);
                $idleStart = null;
            }
        }

        return $totalIdle;
    }
}


