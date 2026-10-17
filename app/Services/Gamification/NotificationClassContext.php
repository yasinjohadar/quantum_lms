<?php

namespace App\Services\Gamification;

use App\InteractiveLearning\Models\LearningExperience;
use App\InteractiveLearning\Models\LearningExperienceAttempt;
use App\Models\ClassEnrollment;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Models\Question;
use App\Models\QuestionAttempt;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * يحدد الصف (والمادة) الذي يخصّه الإشعار.
 *
 * نظام التحفيز (نقاط/شارات/إنجازات/مستويات) عام على مستوى الطالب، لكن الطالب قد
 * يكون مشتركاً في أكثر من صف؛ لذلك نربط كل إشعار بالصف الذي وقع فيه النشاط
 * المسبِّب (الدرس/الاختبار/السؤال). يُفعَّل السياق أثناء معالجة الحدث عبر run()
 * ثم يضيفه GamificationNotificationService تلقائياً إلى data لكل إشعار يُرسل خلاله.
 */
class NotificationClassContext
{
    /** @var array<int, array|null> */
    private array $stack = [];

    public function run(?array $context, callable $callback): mixed
    {
        $this->stack[] = $context;

        try {
            return $callback();
        } finally {
            array_pop($this->stack);
        }
    }

    public function current(): ?array
    {
        return $this->stack === [] ? null : end($this->stack);
    }

    /**
     * إضافة class_id/class_name (وsubject عند غيابها) إلى بيانات الإشعار.
     */
    public function enrich(array $data, ?User $user = null): array
    {
        if (! empty($data['class_name'])) {
            return $data;
        }

        $context = $this->current();

        if (! $context && ! empty($data['subject_id'])) {
            $context = self::fromSubject(Subject::with('schoolClass')->find($data['subject_id']));
        }

        // لا مصدر (مثل منح شارة من إعادة احتساب عامة): إن كان الطالب في صف واحد فقط فهو صف الإشعار
        if (! $context && $user) {
            $context = self::singleClassForUser($user->id);
        }

        return $context ? $data + $context : $data;
    }

    /**
     * صف الطالب إن كان منتسباً لصف واحد فقط (عبر انضمام صف معتمد أو تسجيل مواد نشط).
     */
    public static function singleClassForUser(int $userId): ?array
    {
        $classIds = ClassEnrollment::where('user_id', $userId)
            ->where('status', 'approved')
            ->pluck('class_id')
            ->merge(
                Enrollment::where('user_id', $userId)
                    ->where('status', 'active')
                    ->join('subjects', 'subjects.id', '=', 'enrollments.subject_id')
                    ->pluck('subjects.class_id')
            )
            ->filter()
            ->unique();

        if ($classIds->count() !== 1) {
            return null;
        }

        $class = SchoolClass::find($classIds->first());

        return $class ? ['class_id' => $class->id, 'class_name' => $class->name] : null;
    }

    /**
     * سياق الصف لمصدر حدث تحفيزي (إكمال درس، محاولة اختبار، ...).
     */
    public function forSource(User $user, mixed $source): ?array
    {
        try {
            $subjectIds = array_values(array_unique(array_filter($this->subjectIdsForSource($source))));
            if ($subjectIds === []) {
                return null;
            }

            // المحتوى قد يكون مرتبطاً بأكثر من مادة — نفضّل المادة التي يدرسها الطالب فعلاً
            $enrolled = Enrollment::where('user_id', $user->id)
                ->where('status', 'active')
                ->whereIn('subject_id', $subjectIds)
                ->pluck('subject_id')
                ->all();
            $subjectId = collect($subjectIds)->first(fn ($id) => in_array($id, $enrolled)) ?? $subjectIds[0];

            return self::fromSubject(Subject::with('schoolClass')->find($subjectId));
        } catch (\Throwable $e) {
            Log::warning('Resolving notification class context failed: '.$e->getMessage());

            return null;
        }
    }

    public static function fromSubject(?Subject $subject): ?array
    {
        if (! $subject) {
            return null;
        }

        $context = [
            'subject_id' => $subject->id,
            'subject_name' => $subject->name,
        ];

        if ($subject->schoolClass) {
            $context['class_id'] = $subject->schoolClass->id;
            $context['class_name'] = $subject->schoolClass->name;
        }

        return $context;
    }

    private function subjectIdsForSource(mixed $source): array
    {
        return match (true) {
            $source instanceof LessonCompletion => $this->subjectIdsForLesson($source->lesson),
            $source instanceof QuizAttempt => $this->subjectIdsForQuiz($source->quiz),
            $source instanceof QuestionAttempt => $this->subjectIdsForQuestion($source->question),
            $source instanceof LearningExperienceAttempt => $this->subjectIdsForExperience($source->experience),
            $source instanceof Lesson => $this->subjectIdsForLesson($source),
            $source instanceof Quiz => $this->subjectIdsForQuiz($source),
            $source instanceof Question => $this->subjectIdsForQuestion($source),
            $source instanceof LearningExperience => $this->subjectIdsForExperience($source),
            default => [],
        };
    }

    private function subjectIdsForLesson(?Lesson $lesson): array
    {
        if (! $lesson) {
            return [];
        }

        $ids = $this->subjectIdsForUnit($lesson->unit);
        $ids[] = $lesson->section?->subject_id;
        foreach ($lesson->linkedUnits as $unit) {
            $ids = array_merge($ids, $this->subjectIdsForUnit($unit));
        }

        return $ids;
    }

    private function subjectIdsForQuiz(?Quiz $quiz): array
    {
        if (! $quiz) {
            return [];
        }

        return array_merge(
            [$quiz->subject_id],
            $this->subjectIdsForUnit($quiz->unit),
            [$quiz->section?->subject_id]
        );
    }

    private function subjectIdsForQuestion(?Question $question): array
    {
        if (! $question) {
            return [];
        }

        $ids = [$question->subject_id];
        foreach ($question->units as $unit) {
            $ids = array_merge($ids, $this->subjectIdsForUnit($unit));
        }

        return $ids;
    }

    private function subjectIdsForExperience(?LearningExperience $experience): array
    {
        if (! $experience) {
            return [];
        }

        return array_merge([$experience->subject_id], $this->subjectIdsForUnit($experience->unit));
    }

    /**
     * مادة الوحدة: من قسمها (مع الصعود للوحدة الأب عند الحاجة) + الأقسام المرتبطة بها.
     */
    private function subjectIdsForUnit(?Unit $unit): array
    {
        $ids = [];
        $depth = 0;

        while ($unit && $depth++ < 10) {
            if ($unit->section_id) {
                $ids[] = $unit->section?->subject_id;
                foreach ($unit->mirroredInSections as $section) {
                    $ids[] = $section->subject_id;
                }
                break;
            }
            $unit = $unit->parent;
        }

        return $ids;
    }
}
