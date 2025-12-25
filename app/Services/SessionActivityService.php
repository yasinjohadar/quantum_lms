<?php

namespace App\Services;

use App\Models\SessionActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionActivityService
{
    /**
     * تسجيل نشاط جديد
     */
    public function logActivity(
        int $sessionId,
        string $activityType,
        Request $request,
        ?array $details = null,
        ?string $pageUrl = null
    ): SessionActivity {
        $activity = SessionActivity::create([
            'user_session_id' => $sessionId,
            'activity_type' => $activityType,
            'activity_details' => $details ?? $this->extractRequestDetails($request),
            'page_url' => $pageUrl ?? $request->fullUrl(),
            'occurred_at' => now(),
        ]);

        return $activity;
    }

    /**
     * تسجيل بداية الجلسة
     */
    public function logSessionStart(int $sessionId, Request $request): SessionActivity
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
    public function logSessionEnd(int $sessionId, Request $request): SessionActivity
    {
        return $this->logActivity(
            $sessionId,
            'session_end',
            $request,
            [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
            $request->fullUrl()
        );
    }

    /**
     * الحصول على إحصائيات الجلسة
     */
    public function getSessionStats(int $sessionId): array
    {
        $activities = SessionActivity::where('user_session_id', $sessionId)->get();

        $stats = [
            'total' => $activities->count(),
            'by_type' => [],
            'first_activity' => $activities->min('occurred_at'),
            'last_activity' => $activities->max('occurred_at'),
            'page_views' => 0,
            'actions' => 0,
        ];

        // حساب الأنشطة حسب النوع
        foreach ($activities as $activity) {
            $type = $activity->activity_type;
            
            if (!isset($stats['by_type'][$type])) {
                $stats['by_type'][$type] = 0;
            }
            
            $stats['by_type'][$type]++;
            
            if ($type === 'page_view') {
                $stats['page_views']++;
            } elseif ($type === 'action') {
                $stats['actions']++;
            }
        }

        // حساب المدة الإجمالية
        if ($stats['first_activity'] && $stats['last_activity']) {
            $stats['duration_seconds'] = $stats['last_activity']->diffInSeconds($stats['first_activity']);
        } else {
            $stats['duration_seconds'] = 0;
        }

        return $stats;
    }

    /**
     * استخراج تفاصيل الطلب
     */
    protected function extractRequestDetails(Request $request): array
    {
        return [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'path' => $request->path(),
            'query' => $request->query(),
        ];
    }
}

