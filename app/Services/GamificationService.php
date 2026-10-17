<?php

namespace App\Services;

use App\Models\User;
use App\Models\LessonCompletion;
use App\Models\QuizAttempt;
use App\Models\QuestionAttempt;
use App\Events\LessonAttended;
use App\Events\LessonCompleted;
use App\Events\QuizCompleted;
use App\Events\QuestionAnswered;
use App\Events\PointsAwarded;
use App\Services\Gamification\NotificationClassContext;
use App\Support\SafeEvent;

class GamificationService
{
    public function __construct(
        private PointService $pointService,
        private BadgeService $badgeService,
        private AchievementService $achievementService,
        private LevelService $levelService,
        private TaskService $taskService
    ) {}

    /**
     * معالجة حدث
     */
    private function handleEvent(User $user, string $eventType, array $metadata = [], $source = null): void
    {
        // منح النقاط
        $points = $this->pointService->calculatePoints($eventType, $metadata);
        if ($points > 0) {
            $transaction = $this->pointService->awardPoints($user, $eventType, $points, $source, $metadata);

            // إن كانت المعاملة موجودة مسبقاً (منح متكرّر لنفس المصدر) فلا نعيد إطلاق
            // حدث "منح نقاط" — نتفادى إشعاراً مكرّراً ونقاطاً وهمية.
            if ($transaction->wasRecentlyCreated) {
                SafeEvent::dispatch(new PointsAwarded($user, $points, $eventType, [
                    'total_points' => $this->pointService->getUserTotalPoints($user),
                    'transaction_id' => $transaction->id,
                ]));
            }
        }

        // فحص المهام
        $this->taskService->checkTaskCompletion($user, $eventType, $metadata);

        // فحص الشارات
        $this->badgeService->checkAndAwardBadges($user);

        // فحص الإنجازات
        $this->achievementService->checkAndUnlockAchievements($user, $eventType);

        // فحص ترقية المستوى
        $this->levelService->checkLevelUp($user);
    }

    /**
     * معالجة حضور درس
     */
    private function handleLessonAttendance(LessonCompletion $completion): void
    {
        $user = $completion->user;
        $lesson = $completion->lesson;

        $points = $this->pointService->calculatePoints('lesson_attended', ['lesson_id' => $completion->lesson_id]);

        SafeEvent::dispatch(new LessonAttended($user, $lesson, [
            'points' => $points,
            'completion_id' => $completion->id,
        ]));

        $this->handleEvent($user, 'lesson_attended', ['lesson_id' => $completion->lesson_id], $completion);
    }

    /**
     * معالجة إكمال درس
     */
    private function handleLessonCompletion(LessonCompletion $completion): void
    {
        $user = $completion->user;
        $lesson = $completion->lesson;

        $points = $this->pointService->calculatePoints('lesson_completed', ['lesson_id' => $completion->lesson_id]);

        SafeEvent::dispatch(new LessonCompleted($user, $lesson, [
            'points' => $points,
            'completion_id' => $completion->id,
        ]));

        $this->handleEvent($user, 'lesson_completed', ['lesson_id' => $completion->lesson_id], $completion);
    }

    /**
     * معالجة إكمال اختبار
     */
    private function handleQuizCompletion(QuizAttempt $attempt): void
    {
        if ($attempt->status !== 'completed') {
            return;
        }

        $user = $attempt->user;
        $quiz = $attempt->quiz;

        $points = $this->pointService->calculatePoints('quiz_completed', [
            'quiz_id' => $attempt->quiz_id,
            'percentage' => $attempt->percentage,
        ]);

        SafeEvent::dispatch(new QuizCompleted($user, $quiz, [
            'score' => $attempt->score,
            'percentage' => $attempt->percentage,
            'passed' => $attempt->passed,
            'points' => $points,
            'attempt_id' => $attempt->id,
        ]));

        $this->handleEvent($user, 'quiz_completed', [
            'quiz_id' => $attempt->quiz_id,
            'attempt_id' => $attempt->id,
            'score' => $attempt->score,
            'percentage' => $attempt->percentage,
        ], $attempt);
    }

    /**
     * معالجة إكمال تجربة تفاعلية (اختبار تفاعلي).
     *
     * نُعيد استخدام نوع الحدث 'quiz_completed' نفسه حتى تسري تلقائياً إعدادات
     * النقاط والمهام والشارات والإنجازات والمستويات وشاشة «النقاط حسب النوع»
     * بلا أي إعداد إضافي.
     *
     * ملاحظة: لا نُطلق حدث QuizCompleted لأنه مقيّد بنوع Quiz ولا يوجد Quiz هنا،
     * لذا لا يظهر إشعار «أكملت اختباراً» الفوري للتجارب التفاعلية.
     */
    private function handleInteractiveExperienceCompletion(\App\InteractiveLearning\Models\LearningExperienceAttempt $attempt): void
    {
        $user = $attempt->user;
        if (! $user) {
            return;
        }

        $this->handleEvent($user, 'quiz_completed', [
            'learning_experience_id' => $attempt->learning_experience_id,
            'attempt_id' => $attempt->id,
            'score' => $attempt->score,
            'percentage' => $attempt->percentage,
            'kind' => 'interactive',
        ], $attempt);
    }

    /**
     * معالجة إكمال سؤال
     */
    private function handleQuestionCompletion(QuestionAttempt $attempt): void
    {
        if ($attempt->status !== 'completed') {
            return;
        }

        $user = $attempt->user;
        $question = $attempt->question;

        $points = $this->pointService->calculatePoints('question_answered', [
            'question_id' => $attempt->question_id,
            'is_correct' => $attempt->is_correct,
        ]);

        SafeEvent::dispatch(new QuestionAnswered($user, $question, [
            'score' => $attempt->score,
            'is_correct' => $attempt->is_correct,
            'points' => $points,
            'attempt_id' => $attempt->id,
        ]));

        $this->handleEvent($user, 'question_answered', [
            'question_id' => $attempt->question_id,
            'attempt_id' => $attempt->id,
            'score' => $attempt->score,
            'is_correct' => $attempt->is_correct,
        ], $attempt);
    }

    /**
     * تشغيل المعالجة داخل سياق صف المصدر حتى تحمل كل الإشعارات الناتجة اسم الصف.
     */
    private function inClassContext(?User $user, $source, \Closure $callback): void
    {
        $context = app(NotificationClassContext::class);
        $resolved = $context->current() ?? ($user ? $context->forSource($user, $source) : null);
        $context->run($resolved, $callback);
    }

    /**
     * معالجة حدث
     */
    public function processEvent(User $user, string $eventType, array $metadata = [], $source = null): void
    {
        $this->inClassContext($user, $source, fn () => $this->handleEvent($user, $eventType, $metadata, $source));
    }

    public function processLessonAttendance(LessonCompletion $completion): void
    {
        $this->inClassContext($completion->user, $completion, fn () => $this->handleLessonAttendance($completion));
    }

    public function processLessonCompletion(LessonCompletion $completion): void
    {
        $this->inClassContext($completion->user, $completion, fn () => $this->handleLessonCompletion($completion));
    }

    public function processQuizCompletion(QuizAttempt $attempt): void
    {
        $this->inClassContext($attempt->user, $attempt, fn () => $this->handleQuizCompletion($attempt));
    }

    public function processInteractiveExperienceCompletion(\App\InteractiveLearning\Models\LearningExperienceAttempt $attempt): void
    {
        $this->inClassContext($attempt->user, $attempt, fn () => $this->handleInteractiveExperienceCompletion($attempt));
    }

    public function processQuestionCompletion(QuestionAttempt $attempt): void
    {
        $this->inClassContext($attempt->user, $attempt, fn () => $this->handleQuestionCompletion($attempt));
    }
}
