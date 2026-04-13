<?php

namespace App\Listeners;

use App\Events\LibraryItemCreated;
use App\Models\Subject;
use App\Services\GamificationNotificationService;
use Illuminate\Support\Facades\Log;

class SendLibraryItemNotification
{
    public function __construct(
        private GamificationNotificationService $notificationService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(LibraryItemCreated $event): void
    {
        $item = $event->item;

        if (!$item->subject_id) {
            return;
        }

        try {
            $subject = Subject::find($item->subject_id);
            if (!$subject) {
                return;
            }

            $students = $subject->students()->get();

            if ($students->isEmpty()) {
                return;
            }

            $title = 'عنصر جديد في المكتبة';
            $message = "تم إضافة عنصر جديد في مكتبة مادة {$subject->name}: {$item->title}";

            foreach ($students as $student) {
                $this->notificationService->sendNotification(
                    $student,
                    'library_item',
                    $title,
                    $message,
                    [
                        'item_id' => $item->id,
                        'item_title' => $item->title,
                        'subject_id' => $subject->id,
                        'subject_name' => $subject->name,
                        'url' => route('student.library.show', $item->id),
                        'icon' => 'fe fe-book',
                        'color' => 'info',
                    ],
                    false,
                    null,
                    route('student.library.show', $item->id),
                    true,
                );
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
