<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Subject;
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

    /**
     * رابط فتح الدرس للطالب: صفحة الوحدة داخل المادة، لا صفحة الدرس المفردة.
     *
     * الطالب يتنقّل عبر شجرة المادة (مادة ← قسم ← وحدة) فيرى الدرس في سياقه
     * مع بقية دروس الوحدة، بدل القفز إلى مشغّل درس منفصل.
     *
     * التسلسل: درس ← وحدة ← قسم، ولو كان الدرس معلّقاً على قسم بلا وحدة
     * (114 درساً في البيانات الحالية) نرجع لصفحة القسم، ثم لصفحة المادة.
     */
    public function lessonBrowseUrl(Lesson $lesson, ?Subject $subject = null): string
    {
        $lesson->loadMissing('unit.section', 'section');

        $unit = $lesson->unit;
        $section = $unit?->section ?? $lesson->section;
        $subject = $subject
            ?? $unit?->section?->subject
            ?? $lesson->section?->subject;

        if ($subject && $section && $unit) {
            return route('student.subjects.folders.unit', [
                'subject' => $subject->id,
                'section' => $section->id,
                'unit' => $unit->id,
            ]);
        }

        if ($subject && $section) {
            return route('student.subjects.folders.section', [
                'subject' => $subject->id,
                'section' => $section->id,
            ]);
        }

        if ($subject) {
            return route('student.subjects.folders', ['subject' => $subject->id]);
        }

        // آخر الاحتياطات: صفحة الدرس المفردة
        return route('student.lessons.show', $lesson);
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
            $actionUrl = $this->lessonBrowseUrl($after, $subject);

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

    /**
     * رابط فتح الاختبار للطالب: صفحة الوحدة داخل المادة.
     *
     * حرج: لا يجوز أن يشير الإشعار إلى student.quizzes.start لأن ذلك المسار
     * **يُنشئ محاولة فوراً** عند فتحه بـ GET، فمجرّد نقرة على الإشعار كانت
     * تستهلك محاولة من max_attempts قبل أن يقرأ الطالب السؤال الأول.
     * الطالب يبدأ الاختبار بإرادته من صفحة الوحدة.
     */
    public function quizBrowseUrl(Quiz $quiz, ?Subject $subject = null): string
    {
        $quiz->loadMissing('unit.section', 'section', 'subject');

        $unit = $quiz->unit;
        $section = $unit?->section ?? $quiz->section;
        $subject = $subject ?? $quiz->subject;

        if ($subject && $section && $unit) {
            return route('student.subjects.folders.unit', [
                'subject' => $subject->id,
                'section' => $section->id,
                'unit' => $unit->id,
            ]);
        }

        if ($subject && $section) {
            return route('student.subjects.folders.section', [
                'subject' => $subject->id,
                'section' => $section->id,
            ]);
        }

        if ($subject) {
            return route('student.subjects.folders', ['subject' => $subject->id]);
        }

        // قائمة الاختبارات — وليس بدء الاختبار
        return route('student.quizzes.index');
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
            $actionUrl = $this->quizBrowseUrl($after, $subject);

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
