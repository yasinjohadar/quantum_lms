<?php

namespace App\Services;

use App\Events\UserNotificationPushed;
use App\Models\GamificationNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class GamificationNotificationService
{
    public function __construct(
        protected NotificationPreferenceService $preferenceService
    ) {}

    /**
     * ربط نوع الإشعار بفئة تفضيلات المستخدم.
     */
    public function resolvePreferenceType(string $type): string
    {
        $staff = [
            'lesson_review_submitted', 'lesson_review_approved', 'lesson_review_rejected',
            'lesson_review_submit_ack',
            'quiz_review_submitted', 'quiz_review_approved', 'quiz_review_rejected',
            'quiz_review_submit_ack',
            'staff_review',
        ];
        if (in_array($type, $staff, true)) {
            return 'staff_review';
        }

        if ($type === 'library_item') {
            return 'library';
        }

        if ($type === 'class_enrollment_decision') {
            return 'custom';
        }

        if ($type === 'custom_notification') {
            return 'custom';
        }

        if ($type === 'event_reminder') {
            return 'calendar';
        }

        if (in_array($type, ['student_lesson_available', 'student_quiz_available'], true)) {
            return 'system';
        }

        $gamification = [
            'badge_earned', 'achievement_unlocked', 'level_up', 'challenge_completed', 'reward_claimed',
            'leaderboard_update', 'challenge_reminder', 'points_awarded', 'task_completed',
            'lesson_attended', 'lesson_completed', 'quiz_started', 'quiz_completed', 'question_answered',
        ];
        if (in_array($type, $gamification, true)) {
            return 'gamification';
        }

        return 'system';
    }

    /**
     * إرسال إشعار وتخزينه ثم بثّه عبر Reverb (عند التفعيل).
     */
    public function sendNotification(
        User $user,
        string $type,
        string $title,
        string $message,
        array $data = [],
        bool $sendEmail = false,
        ?User $actor = null,
        ?string $actionUrl = null,
        bool $broadcastPush = false,
    ): GamificationNotification {
        $prefType = $this->resolvePreferenceType($type);

        if (!$this->preferenceService->isAllowed($user, $prefType, 'database')) {
            return new GamificationNotification([
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'is_read' => true,
            ]);
        }

        /** بيانات الفاعل تُحفظ وتُعرض للإدمن فقط (قائمة الإشعارات، التوست، البث). */
        $includeActor = $actor && $this->recipientShouldSeeActor($user);
        $actorRole = null;
        if ($includeActor) {
            $actor->loadMissing('roles');
            $actorRole = $actor->roles->first()?->name;
        }

        $notification = GamificationNotification::create([
            'user_id' => $user->id,
            'actor_id' => $includeActor ? $actor->id : null,
            'actor_name' => $includeActor ? $actor->name : null,
            'actor_role' => $includeActor ? $actorRole : null,
            'action_url' => $actionUrl,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'is_read' => false,
        ]);

        if ($sendEmail && $user->email && $this->preferenceService->isAllowed($user, $prefType, 'email')) {
            // TODO: Mailable للإشعارات
        }

        if ($user->phone && $this->preferenceService->isAllowed($user, $prefType, 'sms')) {
            try {
                $smsService = app(\App\Services\SMS\SMSService::class);
                $smsService->send($user->phone, $message, ['type' => 'notification']);
            } catch (\Exception $e) {
                \Log::error('Error sending SMS notification: ' . $e->getMessage());
            }
        }

        if ($broadcastPush && $notification->id) {
            $this->pushBroadcast($user, $notification);
        }

        return $notification;
    }

    /**
     * الإدمن فقط يرى اسم الفاعل ودوره في الإشعارات والتنبيهات.
     */
    protected function recipientShouldSeeActor(User $recipient): bool
    {
        return $recipient->hasRole('admin');
    }

    /**
     * بث فوري لإشعار مخزّن (للاستخدام بعد إدراج مجمع أو عند الحاجة).
     */
    public function pushBroadcast(User $user, GamificationNotification $notification): void
    {
        if (!$notification->id) {
            return;
        }

        try {
            event(new UserNotificationPushed($user, $notification->toBroadcastPayload()));
        } catch (\Throwable $e) {
            Log::warning('فشل بث الإشعار (تأكد من تشغيل Reverb: php artisan reverb:start): '.$e->getMessage(), [
                'user_id' => $user->id,
                'notification_id' => $notification->id,
            ]);
        }
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
        array $data = [],
        ?User $actor = null,
        ?string $actionUrl = null,
        ?string $messageForNonAdmin = null,
    ): int {
        $count = 0;

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if (!$user) {
                continue;
            }

            $body = $user->hasRole('admin') ? $message : ($messageForNonAdmin ?? $message);

            $notification = $this->sendNotification($user, $type, $title, $body, $data, false, $actor, $actionUrl, true);
            if ($notification->id) {
                $count++;
            }
        }

        return $count;
    }
}
