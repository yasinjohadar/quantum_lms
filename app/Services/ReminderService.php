<?php

namespace App\Services;

use App\Models\EventReminder;
use App\Models\User;
use App\Models\CalendarEvent;
use App\Models\Quiz;
use App\Models\Assignment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Services\GamificationNotificationService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class ReminderService
{
    public function __construct(
        private GamificationNotificationService $notificationService
    ) {}

    /**
     * إنشاء تذكير
     */
    public function createReminder(string $eventType, int $eventId, ?User $user = null, array $options = []): EventReminder
    {
        $reminderType = $options['reminder_type'] ?? 'single';
        $customMinutes = $options['custom_minutes'] ?? null;
        $reminderTimes = $options['reminder_times'] ?? null;

        return EventReminder::create([
            'event_type' => $eventType,
            'event_id' => $eventId,
            'user_id' => $user?->id,
            'reminder_type' => $reminderType,
            'custom_minutes' => $customMinutes,
            'reminder_times' => $reminderTimes,
        ]);
    }

    /**
     * التحقق من التذكيرات المستحقة
     */
    public function checkDueReminders(): Collection
    {
        $reminders = EventReminder::pending()->get();
        $dueReminders = collect();

        foreach ($reminders as $reminder) {
            if ($this->shouldSendReminder($reminder)) {
                $dueReminders->push($reminder);
            }
        }

        return $dueReminders;
    }

    /**
     * إرسال التذكيرات
     */
    public function sendReminders(): void
    {
        $dueReminders = $this->checkDueReminders();

        foreach ($dueReminders as $reminder) {
            try {
                $this->sendReminder($reminder);
            } catch (\Exception $e) {
                Log::error('Error sending reminder: ' . $e->getMessage(), [
                    'reminder_id' => $reminder->id,
                ]);
            }
        }
    }

    /**
     * إرسال تذكير واحد
     */
    private function sendReminder(EventReminder $reminder): void
    {
        $event = $this->getEvent($reminder);
        if (!$event) {
            return;
        }

        $eventDate = $this->getEventDate($reminder, $event);
        if (!$eventDate) {
            return;
        }

        $users = $this->getUsersForReminder($reminder);
        if ($users->isEmpty()) {
            return;
        }

        $title = $this->getReminderTitle($reminder, $event);
        $message = $this->getReminderMessage($reminder, $event, $eventDate);

        foreach ($users as $user) {
            // إرسال Event للتذكير
            Event::dispatch(new \App\Events\EventReminderSent(
                $user,
                $reminder,
                $title,
                $message
            ));
        }

        // تحديد التذكير كمرسل
        $reminder->markAsSent();
    }

    /**
     * حساب وقت الإرسال
     */
    public function calculateReminderTime(Carbon $eventDate, int $minutesBefore): Carbon
    {
        return $eventDate->copy()->subMinutes($minutesBefore);
    }

    /**
     * التحقق من أن التذكير يجب إرساله
     */
    private function shouldSendReminder(EventReminder $reminder): bool
    {
        if ($reminder->is_sent) {
            return false;
        }

        $event = $this->getEvent($reminder);
        if (!$event) {
            return false;
        }

        $eventDate = $this->getEventDate($reminder, $event);
        if (!$eventDate) {
            return false;
        }

        if ($reminder->reminder_type === 'single') {
            if ($reminder->custom_minutes) {
                $reminderTime = $this->calculateReminderTime($eventDate, $reminder->custom_minutes);
                return now()->gte($reminderTime) && now()->lt($eventDate);
            }
        } elseif ($reminder->reminder_type === 'multiple') {
            if ($reminder->reminder_times && is_array($reminder->reminder_times)) {
                foreach ($reminder->reminder_times as $hoursBefore) {
                    $reminderTime = $eventDate->copy()->subHours($hoursBefore);
                    if (now()->gte($reminderTime) && now()->lt($eventDate)) {
                        // التحقق من أن هذا التذكير لم يُرسل بعد
                        // TODO: تحسين هذا المنطق لتتبع التذكيرات المتعددة
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * الحصول على الحدث
     */
    private function getEvent(EventReminder $reminder)
    {
        return match($reminder->event_type) {
            'calendar_event' => CalendarEvent::find($reminder->event_id),
            'quiz' => Quiz::find($reminder->event_id),
            'assignment' => Assignment::find($reminder->event_id),
            default => null,
        };
    }

    /**
     * الحصول على تاريخ الحدث
     */
    private function getEventDate(EventReminder $reminder, $event): ?Carbon
    {
        return match($reminder->event_type) {
            'calendar_event' => $event->start_date ?? null,
            'quiz' => $event->available_from ?? null,
            'assignment' => $event->due_date ?? null,
            default => null,
        };
    }

    /**
     * الحصول على المستخدمين للتذكير
     */
    private function getUsersForReminder(EventReminder $reminder): Collection
    {
        if ($reminder->user_id) {
            return collect([User::find($reminder->user_id)])->filter();
        }

        // إذا كان null، يجب إرسال التذكير لجميع المستخدمين المعنيين
        $event = $this->getEvent($reminder);
        if (!$event) {
            return collect();
        }

        // للحصول على المستخدمين حسب نوع الحدث
        return match($reminder->event_type) {
            'calendar_event' => $this->getUsersForCalendarEvent($event),
            'quiz' => $this->getUsersForQuiz($event),
            'assignment' => $this->getUsersForAssignment($event),
            default => collect(),
        };
    }

    /**
     * الحصول على المستخدمين لحدث تقويم
     */
    private function getUsersForCalendarEvent(CalendarEvent $event): Collection
    {
        if ($event->is_public) {
            return User::students()->get();
        }

        $users = collect();

        if ($event->subject_id) {
            $users = $users->merge($event->subject->students);
        }

        if ($event->class_id) {
            $users = $users->merge($event->class->students);
        }

        return $users->unique('id');
    }

    /**
     * الحصول على المستخدمين لاختبار
     */
    private function getUsersForQuiz(Quiz $quiz): Collection
    {
        return $quiz->subject->students ?? collect();
    }

    /**
     * الحصول على المستخدمين لواجب
     */
    private function getUsersForAssignment(Assignment $assignment): Collection
    {
        $assignable = $assignment->assignable;
        if ($assignable instanceof \App\Models\Subject) {
            return $assignable->students ?? collect();
        }
        // يمكن إضافة منطق إضافي للوحدات والدروس
        return collect();
    }

    /**
     * الحصول على عنوان التذكير
     */
    private function getReminderTitle(EventReminder $reminder, $event): string
    {
        return match($reminder->event_type) {
            'calendar_event' => 'تذكير: ' . $event->title,
            'quiz' => 'تذكير: اختبار ' . $event->title,
            'assignment' => 'تذكير: واجب ' . $event->title,
            default => 'تذكير بحدث',
        };
    }

    /**
     * الحصول على رسالة التذكير
     */
    private function getReminderMessage(EventReminder $reminder, $event, Carbon $eventDate): string
    {
        $timeUntil = now()->diffForHumans($eventDate, true);
        
        return match($reminder->event_type) {
            'calendar_event' => "يبدأ الحدث '{$event->title}' خلال {$timeUntil}",
            'quiz' => "يبدأ الاختبار '{$event->title}' خلال {$timeUntil}",
            'assignment' => "موعد تسليم الواجب '{$event->title}' خلال {$timeUntil}",
            default => "حدث قادم خلال {$timeUntil}",
        };
    }
}

