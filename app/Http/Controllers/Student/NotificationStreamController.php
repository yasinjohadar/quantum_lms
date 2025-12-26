<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NotificationStreamController extends Controller
{
    /**
     * SSE Stream endpoint
     */
    public function stream(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            abort(401);
        }

        // Rate limiting: 1 connection per user
        $key = "sse:user:{$user->id}";
        if (Cache::has($key)) {
            // بدلاً من رفض الاتصال، نرجع إشعارات فورية
            return $this->getQuickNotifications($user);
        }

        return response()->stream(function () use ($user, $key) {
            // إعدادات مهمة للـ SSE
            @ini_set('zlib.output_compression', 'Off');
            @set_time_limit(0);
            @ignore_user_abort(false);
            
            // تعطيل output buffering إذا كان مفعلاً
            while (ob_get_level() > 0) {
                ob_end_flush();
            }
            
            Cache::put($key, true, now()->addMinutes(2));
            
            $lastNotificationId = null;
            $pingInterval = 30; // ثواني
            $lastPing = time();
            $maxRunTime = 55; // ثواني - أقل من حد PHP
            $startTime = time();

            try {
                while (true) {
                    // التحقق من تجاوز الوقت الأقصى
                    if ((time() - $startTime) >= $maxRunTime) {
                        $this->sendSSEMessage('reconnect', ['message' => 'Timeout, please reconnect']);
                        break;
                    }
                    
                    // التحقق من انقطاع الاتصال
                    if (connection_aborted()) {
                        break;
                    }

                    // جلب الإشعارات الجديدة
                    $notifications = $this->getNewNotifications($user->id, $lastNotificationId);
                    
                    foreach ($notifications as $notification) {
                        $this->sendSSEMessage('notification', $notification);
                        $lastNotificationId = $notification['id'] ?? null;
                    }

                    // إرسال ping كل 30 ثانية للحفاظ على الاتصال
                    if (time() - $lastPing >= $pingInterval) {
                        $this->sendSSEMessage('ping', ['timestamp' => now()->toIso8601String()]);
                        $lastPing = time();
                    }

                    // انتظار قصير قبل التكرار التالي
                    usleep(1000000); // 1 ثانية
                }
            } finally {
                // تنظيف عند انتهاء الاتصال
                Cache::forget($key);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * إرجاع إشعارات سريعة بدون stream (للاتصالات المتكررة)
     */
    private function getQuickNotifications($user)
    {
        $notifications = $this->getNewNotifications($user->id, null);
        return response()->json([
            'notifications' => $notifications,
            'message' => 'Quick response - connection already active'
        ]);
    }

    /**
     * إرسال رسالة SSE
     */
    private function sendSSEMessage(string $event, array $data): void
    {
        echo "event: {$event}\n";
        echo "data: " . json_encode($data) . "\n\n";
        
        // Flush output safely
        if (ob_get_level() > 0) {
            @ob_flush();
        }
        @flush();
    }

    /**
     * جلب الإشعارات الجديدة
     */
    private function getNewNotifications(int $userId, ?string $lastId): array
    {
        $key = "notifications:user:{$userId}";
        $notifications = Cache::get($key, []);

        if ($lastId === null) {
            // إرجاع آخر 5 إشعارات عند الاتصال الأول
            return array_slice($notifications, -5);
        }

        // إرجاع الإشعارات الجديدة فقط
        $newNotifications = [];
        $foundLastId = false;

        foreach (array_reverse($notifications) as $notification) {
            if ($foundLastId) {
                $newNotifications[] = $notification;
            }
            
            if (isset($notification['id']) && $notification['id'] === $lastId) {
                $foundLastId = true;
            }
        }

        return array_reverse($newNotifications);
    }
}