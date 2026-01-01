<?php

namespace App\Services\Email;

use App\Models\EmailLog;
use App\Mail\TemplateEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class EmailService
{
    protected EmailSettingsService $settingsService;
    protected EmailTemplateService $templateService;

    public function __construct(
        EmailSettingsService $settingsService,
        EmailTemplateService $templateService
    ) {
        $this->settingsService = $settingsService;
        $this->templateService = $templateService;
        
        // Apply settings to config before sending
        $this->settingsService->applyToConfig();
    }

    /**
     * Send email with logging
     */
    public function send(string $to, string $subject, string $body, array $options = []): bool
    {
        try {
            $settings = $this->settingsService->getSettings();
            $fromAddress = $options['from'] ?? $settings['mail_from_address'];
            $fromName = $options['from_name'] ?? $settings['mail_from_name'];
            $replyTo = $options['reply_to'] ?? $settings['mail_reply_to'];

            Mail::raw($body, function ($message) use ($to, $subject, $fromAddress, $fromName, $replyTo) {
                $message->to($to)
                    ->subject($subject)
                    ->from($fromAddress, $fromName);
                
                if ($replyTo) {
                    $message->replyTo($replyTo);
                }
            });

            $this->logEmail($to, $subject, $body, 'sent');

            return true;
        } catch (Exception $e) {
            Log::error('Error sending email: ' . $e->getMessage());
            $this->logEmail($to, $subject, $body, 'failed', $e->getMessage());
            return false;
        }
    }

    /**
     * Send email from template
     */
    public function sendTemplate(string $templateSlug, string $to, array $variables = []): bool
    {
        try {
            $rendered = $this->templateService->render($templateSlug, $variables);
            
            if (!$rendered) {
                throw new Exception('Template not found or failed to render');
            }

            $settings = $this->settingsService->getSettings();

            Mail::to($to)->send(new TemplateEmail(
                $rendered['subject'],
                $rendered['body'],
                $settings['mail_from_address'],
                $settings['mail_from_name']
            ));

            $this->logEmail($to, $rendered['subject'], $rendered['body'], 'sent');

            return true;
        } catch (Exception $e) {
            Log::error('Error sending template email: ' . $e->getMessage());
            $this->logEmail($to, $templateSlug, '', 'failed', $e->getMessage());
            return false;
        }
    }

    /**
     * Log email
     */
    public function logEmail(string $to, string $subject, string $body, string $status, ?string $error = null): EmailLog
    {
        return EmailLog::create([
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
            'status' => $status,
            'error_message' => $error,
            'sent_at' => $status === 'sent' ? now() : null,
        ]);
    }
}
