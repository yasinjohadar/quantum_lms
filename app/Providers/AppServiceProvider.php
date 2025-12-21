<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\LessonAttended;
use App\Events\LessonCompleted;
use App\Events\QuizStarted;
use App\Events\QuizCompleted;
use App\Events\QuestionAnswered;
use App\Events\TaskCompleted;
use App\Events\PointsAwarded;
use App\Events\BadgeEarned;
use App\Events\AchievementUnlocked;
use App\Events\LevelUp;
use App\Events\ChallengeCompleted;
use App\Events\RewardClaimed;
use App\Events\CustomNotificationSent;
use App\Events\ReviewCreated;
use App\Events\ReviewApproved;
use App\Events\ReviewRejected;
use App\Listeners\SendRealTimeNotification;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // تسجيل Event Listeners
        Event::listen(LessonAttended::class, SendRealTimeNotification::class);
        Event::listen(LessonCompleted::class, SendRealTimeNotification::class);
        Event::listen(QuizStarted::class, SendRealTimeNotification::class);
        Event::listen(QuizCompleted::class, SendRealTimeNotification::class);
        Event::listen(QuestionAnswered::class, SendRealTimeNotification::class);
        Event::listen(TaskCompleted::class, SendRealTimeNotification::class);
        Event::listen(PointsAwarded::class, SendRealTimeNotification::class);
        Event::listen(BadgeEarned::class, SendRealTimeNotification::class);
        Event::listen(AchievementUnlocked::class, SendRealTimeNotification::class);
        Event::listen(LevelUp::class, SendRealTimeNotification::class);
        Event::listen(ChallengeCompleted::class, SendRealTimeNotification::class);
        Event::listen(RewardClaimed::class, SendRealTimeNotification::class);
        Event::listen(CustomNotificationSent::class, SendRealTimeNotification::class);
        Event::listen(ReviewCreated::class, SendRealTimeNotification::class);
        Event::listen(ReviewApproved::class, SendRealTimeNotification::class);
        Event::listen(ReviewRejected::class, SendRealTimeNotification::class);
    }
}
