<?php

namespace App\Services;

use App\Models\User;
use App\Models\GamificationNotification;
use Illuminate\Support\Facades\Mail;
use App\Services\NotificationPreferenceService;

class GamificationNotificationService
{
    protected NotificationPreferenceService $preferenceService;

    public function __construct(NotificationPreferenceService $preferenceService)
    {
        $this->preferenceService = $preferenceService;
    }
    /**
     * إرسال إشعار
     */
    public function sendNotification(
        User $user,
        string $type,
        string $title,
        string $message,
        array $data = [],
        bool $sendEmail = false
    ): GamificationNotification {
        // احترام تفضيلات الإشعارات (قناة قاعدة البيانات)
        if (!$this->preferenceService->isAllowed($user, $type, 'database')) {
            // إذا كان نوع الإشعار مكتوماً، لا ننشئ سجلاً
            return new GamificationNotification([
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'is_read' => true,
            ]);
        }

        $notification = GamificationNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'is_read' => false,
        ]);

        // إرسال بريد إلكتروني إذا كان مفعلاً
        if ($sendEmail && $user->email && $this->preferenceService->isAllowed($user, $type, 'email')) {
            // TODO: إنشاء Mailable class للإشعارات
            // Mail::to($user->email)->send(new GamificationNotificationMail($notification));
        }

        return $notification;
    }

    /**
     * تحديد كمقروء
     */
    public function markAsRead(GamificationNotification $notification): void
    {
        $notification->markAsRead();
    }

    /**
     * تحديد الكل كمقروء
     */
    public function markAllAsRead(User $user): int
    {
        return GamificationNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * عدد غير المقروءة
     */
    public function getUnreadCount(User $user): int
    {
        return GamificationNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
    }

    /**
     * جلب الإشعارات
     */
    public function getNotifications(User $user, int $limit = 20)
    {
        return GamificationNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * إرسال إشعار لعدة مستخدمين
     */
    public function sendBulkNotification(
        array $userIds,
        string $type,
        string $title,
        string $message,
        array $data = []
    ): int {
        $notifications = [];
        $now = now();
        $jsonData = json_encode($data); // تحويل البيانات إلى JSON

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if (!$user) {
                continue;
            }

            // تجاهل المستخدمين الذين أوقفوا هذا النوع من الإشعارات
            if (!$this->preferenceService->isAllowed($user, $type, 'database')) {
                continue;
            }

            $notifications[] = [
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $jsonData, // استخدام JSON string
                'is_read' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // إدراج مجمع
        if (!empty($notifications)) {
            GamificationNotification::insert($notifications);
        }

        // إرسال Events للإشعارات الفورية
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                \Illuminate\Support\Facades\Event::dispatch(
                    new \App\Events\CustomNotificationSent($user, $title, $message, $data)
                );
            }
        }

        return count($notifications);
    }
}

