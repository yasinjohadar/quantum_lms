<?php

namespace App\Services;

use App\InteractiveLearning\Models\LearningExperience;
use App\Models\ClassEnrollment;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Models\Role;

class StaffNotificationService
{
    public function __construct(
        protected GamificationNotificationService $notificationService
    ) {}

    /**
     * معرفات الإدمن + المشرفين المرتبطين بالصف/المادة.
     */
    public function recipientIdsForSubjectScope(Subject $subject): array
    {
        $adminRoleExists = Role::query()
            ->where('name', 'admin')
            ->where('guard_name', 'web')
            ->exists();
        $adminIds = $adminRoleExists ? User::role('admin')->pluck('id')->all() : [];

        $supervisorRoleExists = Role::query()
            ->where('name', 'supervisor')
            ->where('guard_name', 'web')
            ->exists();
        $supervisorIds = [];
        if ($supervisorRoleExists) {
            $supervisorIds = User::role('supervisor')
                ->where(function ($q) use ($subject) {
                    $q->whereHas('assignedClassesAsSupervisor', function ($q2) use ($subject) {
                        $q2->where('classes.id', $subject->class_id);
                    })->orWhereHas('assignedSubjectsAsSupervisor', function ($q2) use ($subject) {
                        $q2->where('subjects.id', $subject->id);
                    });
                })
                ->pluck('id')
                ->all();
        }

        return array_values(array_unique(array_merge($adminIds, $supervisorIds)));
    }

    /**
     * معرفات معلمي المادة (teacher_subjects).
     */
    public function teacherIdsForSubject(Subject $subject): array
    {
        return $subject->assignedTeachers()->pluck('users.id')->all();
    }

    /**
     * معلمون مرتبطون بالمادة أو بصف المادة (teacher_subjects + teacher_classes).
     */
    public function teacherIdsForSubjectAndClass(Subject $subject): array
    {
        $ids = $this->teacherIdsForSubject($subject);

        if ($subject->class_id) {
            $class = SchoolClass::find($subject->class_id);
            if ($class) {
                $ids = array_merge($ids, $class->assignedTeachers()->pluck('users.id')->all());
            }
        }

        return array_values(array_unique($ids));
    }

    public function notifyLessonSubmittedForReview(Lesson $lesson, User $submitter): void
    {
        $lesson->loadMissing('unit.section.subject', 'section.subject');
        $subject = $lesson->unit?->section?->subject ?? $lesson->section?->subject;
        if (!$subject) {
            return;
        }

        $recipients = array_values(array_unique(array_merge(
            $this->recipientIdsForSubjectScope($subject),
            $this->teacherIdsForSubjectAndClass($subject)
        )));
        $recipients = array_values(array_diff($recipients, [$submitter->id]));

        $title = 'درس جديد قيد المراجعة';
        $messageAdmin = $submitter->name.' أرسل الدرس «'.$lesson->title.'» للمراجعة في مادة «'.$subject->name.'».';
        $messageOthers = 'تم إرسال الدرس «'.$lesson->title.'» للمراجعة في مادة «'.$subject->name.'».';
        $url = URL::route('admin.subjects.show', $subject->id);

        $data = [
            'icon' => 'fe fe-book-open',
            'color' => 'warning',
            'entity_type' => 'lesson',
            'entity_id' => $lesson->id,
            'subject_id' => $subject->id,
        ];

        $this->notificationService->sendBulkNotification(
            $recipients,
            'lesson_review_submitted',
            $title,
            $messageAdmin,
            $data,
            $submitter,
            $url,
            $messageOthers
        );

        $this->notificationService->sendNotification(
            $submitter,
            'lesson_review_submit_ack',
            'تم إرسال الدرس للمراجعة',
            'تم إرسال درسك «'.$lesson->title.'» للمراجعة في مادة «'.$subject->name.'».',
            $data,
            false,
            null,
            $url,
            true
        );
    }

    public function notifyLessonReviewOutcome(Lesson $lesson, User $reviewer, bool $approved): void
    {
        $lesson->loadMissing('unit.section.subject', 'section.subject');
        $subject = $lesson->unit?->section?->subject ?? $lesson->section?->subject;
        if (!$subject) {
            return;
        }

        $recipients = array_values(array_unique(array_merge(
            $this->recipientIdsForSubjectScope($subject),
            $this->teacherIdsForSubjectAndClass($subject)
        )));
        $recipients = array_values(array_diff($recipients, [$reviewer->id]));

        $title = $approved ? 'تم قبول مراجعة الدرس' : 'تم رفض مراجعة الدرس';
        $notesSuffix = $lesson->review_notes ? ' الملاحظات: '.$lesson->review_notes : '';
        $messageAdmin = $reviewer->name.' '.($approved ? 'وافق على' : 'رفض').' الدرس «'.$lesson->title.'» في مادة «'.$subject->name.'».'.$notesSuffix;
        $messageOthers = ($approved ? 'تم قبول الدرس «' : 'تم رفض الدرس «').$lesson->title.'» في مادة «'.$subject->name.'».'.$notesSuffix;

        $url = URL::route('admin.subjects.show', $subject->id);

        $data = [
            'icon' => $approved ? 'fe fe-check-circle' : 'fe fe-x-circle',
            'color' => $approved ? 'success' : 'danger',
            'entity_type' => 'lesson',
            'entity_id' => $lesson->id,
            'subject_id' => $subject->id,
        ];

        $type = $approved ? 'lesson_review_approved' : 'lesson_review_rejected';

        $this->notificationService->sendBulkNotification(
            $recipients,
            $type,
            $title,
            $messageAdmin,
            $data,
            $reviewer,
            $url,
            $messageOthers
        );
    }

    public function notifyQuizSubmittedForReview(Quiz $quiz, User $submitter): void
    {
        $quiz->loadMissing('subject');
        $subject = $quiz->subject;
        if (!$subject) {
            return;
        }

        $recipients = array_values(array_unique(array_merge(
            $this->recipientIdsForSubjectScope($subject),
            $this->teacherIdsForSubjectAndClass($subject)
        )));
        $recipients = array_values(array_diff($recipients, [$submitter->id]));

        $title = 'اختبار قيد المراجعة';
        $messageAdmin = $submitter->name.' أرسل الاختبار «'.$quiz->title.'» للمراجعة في مادة «'.$subject->name.'».';
        $messageOthers = 'تم إرسال الاختبار «'.$quiz->title.'» للمراجعة في مادة «'.$subject->name.'».';
        $url = URL::route('admin.subjects.show', $subject->id);

        $data = [
            'icon' => 'fe fe-edit-3',
            'color' => 'warning',
            'entity_type' => 'quiz',
            'entity_id' => $quiz->id,
            'subject_id' => $subject->id,
        ];

        $this->notificationService->sendBulkNotification(
            $recipients,
            'quiz_review_submitted',
            $title,
            $messageAdmin,
            $data,
            $submitter,
            $url,
            $messageOthers
        );

        $this->notificationService->sendNotification(
            $submitter,
            'quiz_review_submit_ack',
            'تم إرسال الاختبار للمراجعة',
            'تم إرسال اختبارك «'.$quiz->title.'» للمراجعة في مادة «'.$subject->name.'».',
            $data,
            false,
            null,
            $url,
            true
        );
    }

    public function notifyQuizReviewOutcome(Quiz $quiz, User $reviewer, bool $approved): void
    {
        $quiz->loadMissing('subject');
        $subject = $quiz->subject;
        if (!$subject) {
            return;
        }

        $recipients = array_values(array_unique(array_merge(
            $this->recipientIdsForSubjectScope($subject),
            $this->teacherIdsForSubjectAndClass($subject)
        )));
        $recipients = array_values(array_diff($recipients, [$reviewer->id]));

        $title = $approved ? 'تم قبول مراجعة الاختبار' : 'تم رفض مراجعة الاختبار';
        $notesSuffix = $quiz->review_notes ? ' الملاحظات: '.$quiz->review_notes : '';
        $messageAdmin = $reviewer->name.' '.($approved ? 'وافق على نشر' : 'رفض نشر').' الاختبار «'.$quiz->title.'» في مادة «'.$subject->name.'».'.$notesSuffix;
        $messageOthers = ($approved ? 'تم قبول نشر الاختبار «' : 'تم رفض نشر الاختبار «').$quiz->title.'» في مادة «'.$subject->name.'».'.$notesSuffix;

        $url = URL::route('admin.subjects.show', $subject->id);

        $data = [
            'icon' => $approved ? 'fe fe-check-circle' : 'fe fe-x-circle',
            'color' => $approved ? 'success' : 'danger',
            'entity_type' => 'quiz',
            'entity_id' => $quiz->id,
            'subject_id' => $subject->id,
        ];

        $type = $approved ? 'quiz_review_approved' : 'quiz_review_rejected';

        $this->notificationService->sendBulkNotification(
            $recipients,
            $type,
            $title,
            $messageAdmin,
            $data,
            $reviewer,
            $url,
            $messageOthers
        );
    }

    public function notifyLearningExperienceSubmittedForReview(LearningExperience $experience, User $submitter): void
    {
        $experience->loadMissing('subject');
        $subject = $experience->subject;
        if (! $subject) {
            return;
        }

        $recipients = array_values(array_unique(array_merge(
            $this->recipientIdsForSubjectScope($subject),
            $this->teacherIdsForSubjectAndClass($subject)
        )));
        $recipients = array_values(array_diff($recipients, [$submitter->id]));

        $title = 'اختبار تفاعلي قيد المراجعة';
        $messageAdmin = $submitter->name.' أرسل الاختبار التفاعلي «'.$experience->title.'» للمراجعة في مادة «'.$subject->name.'».';
        $messageOthers = 'تم إرسال الاختبار التفاعلي «'.$experience->title.'» للمراجعة في مادة «'.$subject->name.'».';
        $url = URL::route('admin.subjects.show', $subject->id);

        $data = [
            'icon' => 'fe fe-edit-3',
            'color' => 'warning',
            'entity_type' => 'learning_experience',
            'entity_id' => $experience->id,
            'subject_id' => $subject->id,
        ];

        $this->notificationService->sendBulkNotification(
            $recipients,
            'learning_experience_review_submitted',
            $title,
            $messageAdmin,
            $data,
            $submitter,
            $url,
            $messageOthers
        );

        $this->notificationService->sendNotification(
            $submitter,
            'learning_experience_review_submit_ack',
            'تم إرسال الاختبار التفاعلي للمراجعة',
            'تم إرسال اختبارك التفاعلي «'.$experience->title.'» للمراجعة في مادة «'.$subject->name.'».',
            $data,
            false,
            null,
            $url,
            true
        );
    }

    public function notifyLearningExperienceReviewOutcome(LearningExperience $experience, User $reviewer, bool $approved): void
    {
        $experience->loadMissing('subject');
        $subject = $experience->subject;
        if (! $subject) {
            return;
        }

        $recipients = array_values(array_unique(array_merge(
            $this->recipientIdsForSubjectScope($subject),
            $this->teacherIdsForSubjectAndClass($subject)
        )));
        $recipients = array_values(array_diff($recipients, [$reviewer->id]));

        $title = $approved ? 'تم قبول مراجعة الاختبار التفاعلي' : 'تم رفض مراجعة الاختبار التفاعلي';
        $notesSuffix = $experience->review_notes ? ' الملاحظات: '.$experience->review_notes : '';
        $messageAdmin = $reviewer->name.' '.($approved ? 'وافق على نشر' : 'رفض نشر').' الاختبار التفاعلي «'.$experience->title.'» في مادة «'.$subject->name.'».'.$notesSuffix;
        $messageOthers = ($approved ? 'تم قبول نشر الاختبار التفاعلي «' : 'تم رفض نشر الاختبار التفاعلي «').$experience->title.'» في مادة «'.$subject->name.'».'.$notesSuffix;

        $url = URL::route('admin.subjects.show', $subject->id);

        $data = [
            'icon' => $approved ? 'fe fe-check-circle' : 'fe fe-x-circle',
            'color' => $approved ? 'success' : 'danger',
            'entity_type' => 'learning_experience',
            'entity_id' => $experience->id,
            'subject_id' => $subject->id,
        ];

        $type = $approved ? 'learning_experience_review_approved' : 'learning_experience_review_rejected';

        $this->notificationService->sendBulkNotification(
            $recipients,
            $type,
            $title,
            $messageAdmin,
            $data,
            $reviewer,
            $url,
            $messageOthers
        );
    }

    /**
     * إشعار الطالب وطاقم الإدارة/المشرفين عند قبول/رفض انضمام للصف.
     */
    public function notifyClassEnrollmentDecision(ClassEnrollment $classEnrollment, User $actor, bool $approved): void
    {
        $classEnrollment->loadMissing('user');
        $class = SchoolClass::find($classEnrollment->class_id);
        if (!$class) {
            return;
        }

        $student = $classEnrollment->user;
        if (!$student) {
            return;
        }

        $subject = $class->subjects()->first();
        $subjectScope = $subject ? $this->recipientIdsForSubjectScope($subject) : User::role('admin')->pluck('id')->all();

        $title = $approved ? 'تم قبول طلب الانضمام للصف' : 'تم رفض طلب الانضمام للصف';
        $messageAdmin = $actor->name.' '.($approved ? 'قبل' : 'رفض').' طلب انضمام الطالب '.$student->name.' إلى الصف «'.$class->name.'».';
        $messageStudent = $approved
            ? 'تم قبول طلب انضمامك إلى الصف «'.$class->name.'».'
            : 'تم رفض طلب انضمامك إلى الصف «'.$class->name.'».';
        $messageStaff = ($approved ? 'تم قبول' : 'تم رفض').' طلب انضمام طالب إلى الصف «'.$class->name.'».';

        $data = [
            'icon' => $approved ? 'fe fe-check' : 'fe fe-x',
            'color' => $approved ? 'success' : 'danger',
            'entity_type' => 'class_enrollment',
            'entity_id' => $classEnrollment->id,
        ];

        $url = $subject
            ? URL::route('admin.subjects.index')
            : URL::route('admin.classes.index');

        $recipients = array_values(array_unique(array_merge(
            [$student->id],
            $subjectScope
        )));

        foreach ($recipients as $recipientId) {
            $recipient = User::find($recipientId);
            if (!$recipient) {
                continue;
            }
            if ((int) $recipient->id === (int) $student->id) {
                $body = $messageStudent;
            } elseif ($recipient->hasRole('admin')) {
                $body = $messageAdmin;
            } else {
                $body = $messageStaff;
            }
            $this->notificationService->sendNotification(
                $recipient,
                'class_enrollment_decision',
                $title,
                $body,
                $data,
                false,
                $actor,
                $url,
                true
            );
        }
    }
}
