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
            abort(429, 'Too many connections');
        }
        Cache::put($key, true, now()->addMinutes(5));

        return response()->stream(function () use ($user, $key) {
            $lastNotificationId = null;
            $pingInterval = 30; // ثواني
            $lastPing = time();

            while (true) {
                // التحقق من انقطاع الاتصال
                if (connection_aborted()) {
                    Cache::forget($key);
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
                usleep(500000); // 0.5 ثانية
            }
            
            // تنظيف عند انتهاء الاتصال
            Cache::forget($key);
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * إرسال رسالة SSE
     */
    private function sendSSEMessage(string $event, array $data): void
    {
        $message = "event: {$event}\n";
        $message .= "data: " . json_encode($data) . "\n\n";
        
        echo $message;
        ob_flush();
        flush();
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
