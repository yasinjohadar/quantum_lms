<?php

namespace App\Listeners;

use App\Events\LibraryItemCreated;
use App\Events\CustomNotificationSent;
use App\Models\Subject;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class SendLibraryItemNotification
{
    /**
     * Handle the event.
     */
    public function handle(LibraryItemCreated $event): void
    {
        $item = $event->item;

        // إرسال إشعار فقط إذا كان العنصر مرتبط بمادة
        if (!$item->subject_id) {
            return;
        }

        try {
            $subject = Subject::find($item->subject_id);
            if (!$subject) {
                return;
            }

            // الحصول على جميع الطلاب المسجلين في المادة
            $students = $subject->students()->get();

            if ($students->isEmpty()) {
                return;
            }

            $title = 'عنصر جديد في المكتبة';
            $message = "تم إضافة عنصر جديد في مكتبة مادة {$subject->name}: {$item->title}";

            // إرسال إشعار لكل طالب
            foreach ($students as $student) {
                Event::dispatch(new CustomNotificationSent(
                    $student,
                    $title,
                    $message,
                    [
                        'type' => 'library_item_created',
                        'item_id' => $item->id,
                        'item_title' => $item->title,
                        'subject_id' => $subject->id,
                        'subject_name' => $subject->name,
                        'url' => route('student.library.show', $item->id),
                    ]
                ));
            }

            Log::info('Library item notifications sent', [
                'item_id' => $item->id,
                'subject_id' => $subject->id,
                'students_count' => $students->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending library item notifications: ' . $e->getMessage(), [
                'item_id' => $item->id,
                'exception' => $e,
            ]);
        }
    }
}
