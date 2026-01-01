<?php

namespace App\Services\SMS;

use App\Models\SMSLog;
use App\Services\SMS\SMSProviderFactory;
use App\Services\SMS\SMSSettingsService;
use App\Services\SMS\SMSTemplateService;
use Illuminate\Support\Facades\Log;
use Exception;

class SMSService
{
    protected SMSSettingsService $settingsService;
    protected SMSTemplateService $templateService;

    public function __construct(
        SMSSettingsService $settingsService,
        SMSTemplateService $templateService
    ) {
        $this->settingsService = $settingsService;
        $this->templateService = $templateService;
    }

    /**
     * Send SMS message
     */
    public function send(string $to, string $message, array $options = []): bool
    {
        try {
            $settings = $this->settingsService->getSettings();

            // Check if SMS is enabled
            if (!$settings['sms_enabled']) {
                Log::warning('SMS is disabled, message not sent', ['to' => $to]);
                $this->logSMS($to, $message, $options['type'] ?? 'custom', 'failed', 'SMS معطل في النظام');
                return false;
            }

            // Get provider
            $provider = $settings['sms_provider'];
            $config = $this->settingsService->getProviderConfig();
            $smsProvider = SMSProviderFactory::create($provider, $config);

            // Send SMS
            $result = $smsProvider->send($to, $message);

            // Log SMS
            $this->logSMS(
                $to,
                $message,
                $options['type'] ?? 'custom',
                $result['success'] ? 'sent' : 'failed',
                $result['success'] ? null : $result['message'],
                $provider
            );

            return $result['success'];
        } catch (Exception $e) {
            Log::error('Error sending SMS: ' . $e->getMessage());
            $this->logSMS($to, $message, $options['type'] ?? 'custom', 'failed', $e->getMessage());
            return false;
        }
    }

    /**
     * Send SMS from template
     */
    public function sendTemplate(string $templateSlug, string $to, array $variables = []): bool
    {
        try {
            $message = $this->templateService->render($templateSlug, $variables);
            return $this->send($to, $message, ['type' => 'notification']);
        } catch (Exception $e) {
            Log::error('Error sending template SMS: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log SMS
     */
    public function logSMS(
        string $to,
        string $message,
        string $type,
        string $status,
        ?string $error = null,
        ?string $provider = null
    ): SMSLog {
        $settings = $this->settingsService->getSettings();
        
        return SMSLog::create([
            'to' => $to,
            'message' => $message,
            'type' => $type,
            'status' => $status,
            'provider' => $provider ?? $settings['sms_provider'] ?? null,
            'error_message' => $error,
            'sent_at' => $status === 'sent' ? now() : null,
        ]);
    }
}


