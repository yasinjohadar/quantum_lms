<?php

namespace App\Listeners;

use App\Events\EventReminderSent;
use App\Services\GamificationNotificationService;
use Illuminate\Support\Facades\Log;

class SendEventReminderNotification
{
    public function __construct(
        private GamificationNotificationService $notificationService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(EventReminderSent $event): void
    {
        try {
            // حفظ الإشعار في قاعدة البيانات
            $this->notificationService->sendNotification(
                $event->user,
                'event_reminder',
                $event->title,
                $event->message,
                [
                    'reminder_id' => $event->reminder->id,
                    'event_type' => $event->reminder->event_type,
                    'event_id' => $event->reminder->event_id,
                ],
                false
            );
        } catch (\Exception $e) {
            Log::error('Error sending event reminder notification: ' . $e->getMessage(), [
                'reminder_id' => $event->reminder->id,
                'user_id' => $event->user->id,
            ]);
        }
    }
}
