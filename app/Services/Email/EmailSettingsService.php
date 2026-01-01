<?php

namespace App\Services\Email;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class EmailSettingsService
{
    /**
     * Get email settings from database
     */
    public function getSettings(): array
    {
        $settings = SystemSetting::where('group', 'email')
            ->get()
            ->keyBy('key')
            ->map(function ($setting) {
                return $setting->value;
            })
            ->toArray();

        return [
            'mail_driver' => $settings['mail_driver'] ?? 'smtp',
            'smtp_host' => $settings['smtp_host'] ?? '',
            'smtp_port' => $settings['smtp_port'] ?? '587',
            'smtp_encryption' => $settings['smtp_encryption'] ?? 'tls',
            'smtp_username' => $settings['smtp_username'] ?? '',
            'smtp_password' => $settings['smtp_password'] ?? '',
            'mail_from_address' => $settings['mail_from_address'] ?? '',
            'mail_from_name' => $settings['mail_from_name'] ?? '',
            'mail_reply_to' => $settings['mail_reply_to'] ?? '',
        ];
    }

    /**
     * Update email settings
     */
    public function updateSettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            SystemSetting::set(
                $key,
                $value,
                'string',
                'email'
            );
        }

        // Apply settings to config
        $this->applyToConfig();
    }

    /**
     * Get mail configuration array
     */
    public function getConfig(): array
    {
        $settings = $this->getSettings();
        return $this->buildConfigFromSettings($settings);
    }

    /**
     * Apply email settings to Laravel config
     */
    public function applyToConfig(?array $settings = null): void
    {
        try {
            // If settings provided, use them, otherwise get from database
            if ($settings === null) {
                $settings = $this->getSettings();
            }
            
            $config = $this->buildConfigFromSettings($settings);

            Config::set('mail.default', $config['default']);
            
            // Update SMTP config
            $smtpConfig = $config['mailers']['smtp'];
            foreach ($smtpConfig as $key => $value) {
                Config::set("mail.mailers.smtp.{$key}", $value);
            }
            
            Config::set('mail.from', $config['from']);

            // Clear mail cache
            try {
                if (function_exists('app')) {
                    app()->forgetInstance('mail.manager');
                    app()->forgetInstance('swift.mailer');
                    if (class_exists('Illuminate\Mail\MailManager')) {
                        app()->forgetInstance(\Illuminate\Mail\MailManager::class);
                    }
                }
            } catch (\Exception $e) {
                // Ignore cache clearing errors
            }
        } catch (Exception $e) {
            Log::error('Error applying email config: ' . $e->getMessage());
        }
    }

    /**
     * Build config array from settings array
     */
    private function buildConfigFromSettings(array $settings): array
    {
        $mailerConfig = [
            'transport' => $settings['mail_driver'] === 'smtp' ? 'smtp' : $settings['mail_driver'],
        ];

        if ($settings['mail_driver'] === 'smtp') {
            $mailerConfig['host'] = $settings['smtp_host'];
            $mailerConfig['port'] = (int) $settings['smtp_port'];
            $mailerConfig['username'] = $settings['smtp_username'];
            $mailerConfig['password'] = $settings['smtp_password'];

            // Set encryption
            if ($settings['smtp_encryption'] === 'ssl') {
                $mailerConfig['encryption'] = 'ssl';
            } elseif ($settings['smtp_encryption'] === 'tls') {
                $mailerConfig['encryption'] = 'tls';
            }

            $mailerConfig['timeout'] = null;
            $mailerConfig['local_domain'] = parse_url(config('app.url', 'http://localhost'), PHP_URL_HOST);
        }

        return [
            'default' => $settings['mail_driver'],
            'mailers' => [
                'smtp' => $mailerConfig,
            ],
            'from' => [
                'address' => $settings['mail_from_address'],
                'name' => $settings['mail_from_name'],
            ],
        ];
    }

    /**
     * Test SMTP connection
     */
    public function testConnection(): array
    {
        try {
            $settings = $this->getSettings();

            if ($settings['mail_driver'] !== 'smtp') {
                return [
                    'success' => false,
                    'message' => 'اختبار الاتصال متاح فقط لـ SMTP',
                ];
            }

            // Apply settings to config first
            $this->applyToConfig();

            // Try to send a test email to verify connection
            // We'll use a simple connection test by trying to connect to SMTP server
            $connection = @fsockopen(
                $settings['smtp_host'],
                (int) $settings['smtp_port'],
                $errno,
                $errstr,
                10
            );

            if (!$connection) {
                return [
                    'success' => false,
                    'message' => "فشل الاتصال بالخادم: {$errstr} ({$errno})",
                ];
            }

            fclose($connection);

            return [
                'success' => true,
                'message' => 'تم الاتصال بالخادم بنجاح',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'فشل الاتصال: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Initialize default settings if not exists
     */
    public function initializeDefaults(): void
    {
        $defaults = [
            'mail_driver' => 'log',
            'smtp_host' => '',
            'smtp_port' => '587',
            'smtp_encryption' => 'tls',
            'smtp_username' => '',
            'smtp_password' => '',
            'mail_from_address' => config('mail.from.address', 'hello@example.com'),
            'mail_from_name' => config('mail.from.name', config('app.name')),
            'mail_reply_to' => '',
        ];

        foreach ($defaults as $key => $value) {
            $existing = SystemSetting::where('key', $key)->where('group', 'email')->first();
            if (!$existing) {
                SystemSetting::set($key, $value, 'string', 'email');
            }
        }
    }
}
