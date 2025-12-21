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
use Illuminate\Support\Facades\Event;

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
    public function processEvent(User $user, string $eventType, array $metadata = []): void
    {
        // منح النقاط
        $points = $this->pointService->calculatePoints($eventType, $metadata);
        if ($points > 0) {
            $transaction = $this->pointService->awardPoints($user, $eventType, $points, null, $metadata);
            
            // إرسال Event للنقاط
            Event::dispatch(new PointsAwarded($user, $points, $eventType, [
                'total_points' => $this->pointService->getUserTotalPoints($user),
                'transaction_id' => $transaction->id,
            ]));
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
    public function processLessonAttendance(LessonCompletion $completion): void
    {
        $user = $completion->user;
        $lesson = $completion->lesson;
        
        $points = $this->pointService->calculatePoints('lesson_attended', ['lesson_id' => $completion->lesson_id]);
        
        // إرسال Event
        Event::dispatch(new LessonAttended($user, $lesson, [
            'points' => $points,
            'completion_id' => $completion->id,
        ]));
        
        $this->processEvent($user, 'lesson_attended', ['lesson_id' => $completion->lesson_id]);
    }

    /**
     * معالجة إكمال درس
     */
    public function processLessonCompletion(LessonCompletion $completion): void
    {
        $user = $completion->user;
        $lesson = $completion->lesson;
        
        $points = $this->pointService->calculatePoints('lesson_completed', ['lesson_id' => $completion->lesson_id]);
        
        // إرسال Event
        Event::dispatch(new LessonCompleted($user, $lesson, [
            'points' => $points,
            'completion_id' => $completion->id,
        ]));
        
        $this->processEvent($user, 'lesson_completed', ['lesson_id' => $completion->lesson_id]);
    }

    /**
     * معالجة إكمال اختبار
     */
    public function processQuizCompletion(QuizAttempt $attempt): void
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
        
        // إرسال Event
        Event::dispatch(new QuizCompleted($user, $quiz, [
            'score' => $attempt->score,
            'percentage' => $attempt->percentage,
            'passed' => $attempt->passed,
            'points' => $points,
            'attempt_id' => $attempt->id,
        ]));
        
        $this->processEvent($user, 'quiz_completed', [
            'quiz_id' => $attempt->quiz_id,
            'attempt_id' => $attempt->id,
            'score' => $attempt->score,
            'percentage' => $attempt->percentage,
        ]);
    }

    /**
     * معالجة إكمال سؤال
     */
    public function processQuestionCompletion(QuestionAttempt $attempt): void
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
        
        // إرسال Event
        Event::dispatch(new QuestionAnswered($user, $question, [
            'score' => $attempt->score,
            'is_correct' => $attempt->is_correct,
            'points' => $points,
            'attempt_id' => $attempt->id,
        ]));
        
        $this->processEvent($user, 'question_answered', [
            'question_id' => $attempt->question_id,
            'attempt_id' => $attempt->id,
            'score' => $attempt->score,
            'is_correct' => $attempt->is_correct,
        ]);
    }
}

