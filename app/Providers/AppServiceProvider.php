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
use App\Events\AssignmentSubmitted;
use App\Events\AssignmentGraded;
use App\Events\LibraryItemCreated;
use App\Events\EventReminderSent;
use App\Listeners\SendRealTimeNotification;
use App\Listeners\SendLibraryItemNotification;
use App\Listeners\SendEventReminderNotification;
use Illuminate\Pagination\Paginator;

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
        // Apply email settings from database
        try {
            $emailSettingsService = app(\App\Services\Email\EmailSettingsService::class);
            $emailSettingsService->initializeDefaults();
            $emailSettingsService->applyToConfig();
        } catch (\Exception $e) {
            // Silently fail if tables don't exist yet
            \Log::warning('Failed to apply email settings from DB: ' . $e->getMessage());
        }

        // Initialize SMS settings from database
        try {
            $smsSettingsService = app(\App\Services\SMS\SMSSettingsService::class);
            $smsSettingsService->initializeDefaults();
        } catch (\Exception $e) {
            // Silently fail if tables don't exist yet
            \Log::warning('Failed to initialize SMS settings: ' . $e->getMessage());
        }

        // Initialize WhatsApp settings from database
        try {
            $whatsappSettingsService = app(\App\Services\WhatsApp\WhatsAppSettingsService::class);
            $whatsappSettingsService->initializeDefaults();
        } catch (\Exception $e) {
            // Silently fail if tables don't exist yet
            \Log::warning('Failed to initialize WhatsApp settings: ' . $e->getMessage());
        }

        // Register storage helper globally
        if (!function_exists('storage_disk')) {
            function storage_disk(string $diskName) {
                return app(\App\Services\Storage\AppStorageManager::class)->getDisk($diskName);
            }
        }
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
        Event::listen(AssignmentSubmitted::class, SendRealTimeNotification::class);
        Event::listen(AssignmentGraded::class, SendRealTimeNotification::class);
        Event::listen(LibraryItemCreated::class, SendLibraryItemNotification::class);
        Event::listen(EventReminderSent::class, SendEventReminderNotification::class);
        Event::listen(EventReminderSent::class, SendRealTimeNotification::class);

        // WhatsApp Event Listeners
        Event::listen(\App\Events\WhatsAppMessageReceived::class, \App\Listeners\AutoReplyWhatsAppListener::class);

        Paginator::useBootstrap();
    }
}