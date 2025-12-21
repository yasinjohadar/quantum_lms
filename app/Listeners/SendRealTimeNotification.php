<?php

namespace App\Listeners;

use App\Events\LessonAttended;
use App\Events\LessonCompleted;
use App\Events\QuizStarted;
use App\Events\QuizCompleted;
use App\Events\QuestionAnswered;
use App\Events\TaskCompleted;
use App\Events\PointsAwarded;
use App\Events\BadgeEarned;
use App\Events\AchievementUnlocked;
use App\Events\LevelUp;
use App\Events\ChallengeCompleted;
use App\Events\RewardClaimed;
use App\Models\GamificationNotification;
use App\Services\GamificationNotificationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SendRealTimeNotification
{
    public function __construct(
        private GamificationNotificationService $notificationService
    ) {}

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        try {
            // الحصول على بيانات الإشعار من Event
            $notificationData = $this->getNotificationData($event);
            
            if (!$notificationData) {
                return;
            }

            $user = $this->getUserFromEvent($event);
            
            if (!$user) {
                return;
            }

            // حفظ الإشعار في قاعدة البيانات
            $notification = $this->notificationService->sendNotification(
                $user,
                $notificationData['type'],
                $notificationData['title'],
                $notificationData['message'],
                $notificationData['data'] ?? [],
                false
            );

            // حفظ الإشعار في Cache للإرسال عبر SSE
            $this->storeNotificationForSSE($user->id, $notificationData);
        } catch (\Exception $e) {
            Log::error('Error sending real-time notification: ' . $e->getMessage(), [
                'event' => get_class($event),
                'exception' => $e,
            ]);
        }
    }

    /**
     * الحصول على بيانات الإشعار من Event
     */
    private function getNotificationData($event): ?array
    {
        if (method_exists($event, 'broadcastWith')) {
            return $event->broadcastWith();
        }

        return null;
    }

    /**
     * الحصول على User من Event
     */
    private function getUserFromEvent($event): ?\App\Models\User
    {
        if (isset($event->user) && $event->user instanceof \App\Models\User) {
            return $event->user;
        }

        return null;
    }

    /**
     * حفظ الإشعار في Cache للإرسال عبر SSE
     */
    private function storeNotificationForSSE(int $userId, array $notificationData): void
    {
        $key = "notifications:user:{$userId}";
        $notifications = Cache::get($key, []);
        
        $notifications[] = [
            'id' => uniqid(),
            ...$notificationData,
            'created_at' => now()->toIso8601String(),
        ];

        // الاحتفاظ بآخر 50 إشعار فقط
        if (count($notifications) > 50) {
            $notifications = array_slice($notifications, -50);
        }

        Cache::put($key, $notifications, now()->addHours(1));
    }
}
