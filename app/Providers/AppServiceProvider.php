<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
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
use App\Events\LibraryItemCreated;
use App\Events\EventReminderSent;
use App\Listeners\SendRealTimeNotification;
use App\Listeners\SendLibraryItemNotification;
use App\Models\SchoolClass;
use App\Models\SystemSetting;
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
        // Frontend footer data (dynamic classes list + contact info)
        View::composer('frontend.layouts.footer', function ($view) {
            try {
                $footerClasses = SchoolClass::query()
                    ->active()
                    ->ordered()
                    ->limit(6)
                    ->get(['id', 'name', 'slug']);
            } catch (\Exception $e) {
                $footerClasses = collect();
            }

            $footerContactAddress = trim((string) SystemSetting::get('contact_address', ''));
            $footerContactPhone   = trim((string) SystemSetting::get('contact_phone', ''));
            $footerContactEmail   = trim((string) SystemSetting::get('contact_email', ''));

            $view->with('footerClasses', $footerClasses)
                ->with('footerContactAddress', $footerContactAddress)
                ->with('footerContactPhone', $footerContactPhone)
                ->with('footerContactEmail', $footerContactEmail);
        });

        // Social links for frontend header and footer (dynamic from social_links table)
        View::composer(['frontend.layouts.navbar', 'frontend.layouts.footer'], function ($view) {
            try {
                $socialLinks = \App\Models\SocialLink::active()->ordered()->get();
                $view->with('socialLinks', $socialLinks);
            } catch (\Exception $e) {
                $view->with('socialLinks', collect());
            }
        });

        // WhatsApp floating button settings for frontend
        View::composer('frontend.layouts.master', function ($view) {
            try {
                $whatsappContactNumber = SystemSetting::get('whatsapp_contact_number', '');
                $whatsappFloatButtonEnabled = (bool) SystemSetting::get('whatsapp_float_button_enabled', false);
                $view->with(compact('whatsappContactNumber', 'whatsappFloatButtonEnabled'));
            } catch (\Exception $e) {
                $view->with('whatsappContactNumber', '')->with('whatsappFloatButtonEnabled', false);
            }
        });

        // نقاط الطالب في الهيدر (لوحة التحفيز)
        View::composer('student.layouts.main-header', function ($view) {
            $student_total_points = 0;
            if (\Illuminate\Support\Facades\Auth::check()) {
                try {
                    $pointService = app(\App\Services\PointService::class);
                    $student_total_points = $pointService->getUserTotalPoints(\Illuminate\Support\Facades\Auth::user());
                } catch (\Exception $e) {
                    $student_total_points = 0;
                }
            }
            $view->with('student_total_points', $student_total_points);
        });

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

        // Register helper (namespaced; function_exists must match the declared name)
        if (!function_exists(__NAMESPACE__ . '\\storage_disk')) {
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
        Event::listen(LibraryItemCreated::class, SendLibraryItemNotification::class);
        Event::listen(EventReminderSent::class, SendRealTimeNotification::class);

        // WhatsApp Event Listeners
        Event::listen(\App\Events\WhatsAppMessageReceived::class, \App\Listeners\AutoReplyWhatsAppListener::class);

        Paginator::useBootstrap();
    }
}