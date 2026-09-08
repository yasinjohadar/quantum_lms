<?php

namespace App\Services\Gamification;

use App\Models\User;
use App\Services\PointService;
use App\Services\StudentProgressService;
use Carbon\Carbon;

/**
 * يحسب نسبة التقدم (0-100) نحو تحقيق criteria واحد لإنجاز أو شارة.
 *
 * يقرأ الشكل الفعلي المُخزَّن في achievements.criteria / badges.criteria
 * (كما تُنشئه AchievementsSeeder/BadgesSeeder): مفتاح 'type' مميّز، ثم
 * قيمة الهدف تحت أحد المفاتيح count/percentage/total/days — وليس المفاتيح
 * الوصفية القديمة (lessons_attended, points_required, ...) التي لم تكن
 * تطابق البيانات المزروعة فعلياً فتُبقي التقدم على صفر دائماً (أو تجعل
 * الشرط يتحقق دوماً في حالة الشارات).
 */
class CriteriaProgressCalculator
{
    public function __construct(
        private PointService $pointService,
        private StudentProgressService $progressService,
    ) {}

    public function progress(User $user, array $criteria): int
    {
        $type = $criteria['type'] ?? null;

        return match ($type) {
            'attendance' => $this->ratio(
                !empty($criteria['consecutive']) ? $this->streakDays($user) : $this->attendedLessonsCount($user),
                $criteria['count'] ?? 0
            ),
            'lesson_completion' => $this->ratio($this->completedLessonsCount($user), $criteria['count'] ?? 0),
            'quiz_completed' => $this->ratio($this->completedQuizzesCount($user), $criteria['count'] ?? 0),
            'quiz_score' => $this->ratio($this->bestQuizPercentage($user), $criteria['percentage'] ?? 0),
            'perfect_scores' => $this->ratio($this->perfectQuizCount($user), $criteria['count'] ?? 0),
            'questions_answered' => $this->ratio($this->answeredQuestionsCount($user), $criteria['count'] ?? 0),
            'points' => $this->ratio($this->pointService->getUserTotalPoints($user), $criteria['total'] ?? 0),
            'course_completion' => $this->ratio($this->bestCourseCompletionPercentage($user), $criteria['percentage'] ?? 0),
            'attendance_streak', 'streak' => $this->ratio($this->streakDays($user), $criteria['days'] ?? 0),
            'automatic' => 100, // شارة ترحيبية بلا شرط حقيقي، تُمنح فوراً
            default => 0, // 'manual' (تُمنح يدوياً من الأدمن) أو نوع غير معروف: لا تقدم تلقائي
        };
    }

    public function isMet(User $user, array $criteria): bool
    {
        return $this->progress($user, $criteria) >= 100;
    }

    private function ratio(int|float $current, int|float $target): int
    {
        $target = (float) $target;
        if ($target <= 0) {
            return 0;
        }

        return (int) min(100, ($current / $target) * 100);
    }

    private function attendedLessonsCount(User $user): int
    {
        return $user->lessonCompletions()->where('status', 'attended')->count();
    }

    private function completedLessonsCount(User $user): int
    {
        return $user->lessonCompletions()->where('status', 'completed')->count();
    }

    private function completedQuizzesCount(User $user): int
    {
        return $user->completedQuizAttemptsCount();
    }

    private function bestQuizPercentage(User $user): float
    {
        $regular = (float) ($user->quizAttempts()->completed()->max('percentage') ?? 0);
        $interactive = (float) ($user->learningExperienceAttempts()->max('percentage') ?? 0);

        return max($regular, $interactive);
    }

    private function perfectQuizCount(User $user): int
    {
        $regular = (int) $user->quizAttempts()->completed()
            ->where('percentage', '>=', 100)
            ->select('quiz_id')->distinct()->count('quiz_id');
        $interactive = (int) $user->learningExperienceAttempts()
            ->where('percentage', '>=', 100)
            ->select('learning_experience_id')->distinct()->count('learning_experience_id');

        return $regular + $interactive;
    }

    private function answeredQuestionsCount(User $user): int
    {
        return $user->questionAttempts()->completed()
            ->select('question_id')->distinct()->count('question_id');
    }

    /**
     * أعلى نسبة إكمال (overall_percentage) بين كل المواد المسجَّل بها الطالب حالياً.
     * لا علاقة لهذا بحالة enrollments.status (لا يوجد مسار في التطبيق يجعلها 'completed' فعلياً)،
     * بل بنسبة التقدم الفعلية المحسوبة (دروس/اختبارات/أسئلة) لكل مادة — وهي ما تطابق
     * الشرط المزروع فعلاً {'type':'course_completion','percentage':100}.
     */
    private function bestCourseCompletionPercentage(User $user): float
    {
        $progressList = $this->progressService->getAllStudentProgress($user->id);

        return collect($progressList)->max(fn ($row) => $row['progress']['overall_percentage'] ?? 0) ?? 0;
    }

    /**
     * أيام الحضور المتتالية (حتى اليوم)، بالاعتماد على تواريخ LessonCompletion.
     */
    private function streakDays(User $user): int
    {
        $completions = $user->lessonCompletions()
            ->orderBy('marked_at', 'desc')
            ->get()
            ->groupBy(fn ($item) => $item->marked_at->format('Y-m-d'));

        $streak = 0;
        $currentDate = now()->startOfDay();

        foreach ($completions as $date => $items) {
            $dateObj = Carbon::parse($date)->startOfDay();
            $diff = (int) abs($currentDate->diffInDays($dateObj));

            if ($diff === $streak) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }
}
