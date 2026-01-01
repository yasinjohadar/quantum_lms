<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Mail\ResetPasswordMail;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        // Get academy name from email settings or config
        try {
            $emailSettingsService = app(\App\Services\Email\EmailSettingsService::class);
            $settings = $emailSettingsService->getSettings();
            $academyName = $settings['mail_from_name'] ?? config('mail.from.name', 'أكاديمية كلاود سوفت');
        } catch (\Exception $e) {
            $academyName = config('mail.from.name', 'أكاديمية كلاود سوفت');
        }

        $count = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return new ResetPasswordMail($url, $academyName, $count, $notifiable->getEmailForPasswordReset());
    }
}
