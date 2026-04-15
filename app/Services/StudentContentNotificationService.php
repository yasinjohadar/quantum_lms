<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class StudentContentNotificationService
{
    public function __construct(
        protected GamificationNotificationService $notificationService
    ) {}

    public function lessonVisibleToStudents(Lesson $lesson): bool
    {
        return $lesson->is_active
            && $lesson->review_status === Lesson::REVIEW_STATUS_APPROVED;
    }

    public function quizVisibleToStudents(Quiz $quiz): bool
    {
        return $quiz->is_active
            && $quiz->is_published
            && $quiz->review_status === Quiz::REVIEW_STATUS_APPROVED;
    }

    public function notifyIfLessonBecameVisible(?Lesson $before, Lesson $after, ?User $actor): void
    {
        $visibleBefore = $before && $this->lessonVisibleToStudents($before);
        $visibleAfter = $this->lessonVisibleToStudents($after);

        if ($visibleBefore || !$visibleAfter) {
            return;
        }

        try {
            $after->loadMissing('unit.section.subject', 'section.subject');
            $subject = $after->unit?->section?->subject ?? $after->section?->subject;
            if (!$subject) {
                return;
            }

            $students = $subject->students()->wherePivot('status', 'active')->get();
            if ($students->isEmpty()) {
                return;
            }

            $title = 'درس جديد متاح';
            $message = "أصبح درس «{$after->title}» متاحاً في مادة {$subject->name}.";
            $actionUrl = route('student.lessons.show', $after);

            foreach ($students as $student) {
                $this->notificationService->sendNotification(
                    $student,
                    'student_lesson_available',
                    $title,
                    $message,
                    [
                        'lesson_id' => $after->id,
                        'lesson_title' => $after->title,
                        'subject_id' => $subject->id,
                        'subject_name' => $subject->name,
                        'url' => $actionUrl,
                        'icon' => 'fe fe-play-circle',
                        'color' => 'primary',
                    ],
                    false,
                    null,
                    $actionUrl,
                    true,
                );
            }
        } catch (\Exception $e) {
            Log::error('Student lesson availability notification failed: '.$e->getMessage(), [
                'lesson_id' => $after->id,
                'exception' => $e,
            ]);
        }
    }

    public function notifyIfQuizBecameVisible(?Quiz $before, Quiz $after, ?User $actor): void
    {
        $visibleBefore = $before && $this->quizVisibleToStudents($before);
        $visibleAfter = $this->quizVisibleToStudents($after);

        if ($visibleBefore || !$visibleAfter) {
            return;
        }

        try {
            $after->loadMissing('subject');
            $subject = $after->subject;
            if (!$subject) {
                return;
            }

            $students = $subject->students()->wherePivot('status', 'active')->get();
            if ($students->isEmpty()) {
                return;
            }

            $title = 'اختبار جديد متاح';
            $message = "أصبح اختبار «{$after->title}» متاحاً في مادة {$subject->name}.";
            $actionUrl = route('student.quizzes.start', $after);

            foreach ($students as $student) {
                $this->notificationService->sendNotification(
                    $student,
                    'student_quiz_available',
                    $title,
                    $message,
                    [
                        'quiz_id' => $after->id,
                        'quiz_title' => $after->title,
                        'subject_id' => $subject->id,
                        'subject_name' => $subject->name,
                        'url' => $actionUrl,
                        'icon' => 'fe fe-edit-2',
                        'color' => 'success',
                    ],
                    false,
                    null,
                    $actionUrl,
                    true,
                );
            }
        } catch (\Exception $e) {
            Log::error('Student quiz availability notification failed: '.$e->getMessage(), [
                'quiz_id' => $after->id,
                'exception' => $e,
            ]);
        }
    }
}
