<?php

namespace App\Services\SMS;

use App\Models\SystemSetting;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class SMSSettingsService
{
    /**
     * Get SMS settings from database
     */
    public function getSettings(): array
    {
        $settings = SystemSetting::where('group', 'sms')
            ->get()
            ->keyBy('key')
            ->map(function ($setting) {
                return $setting->value;
            })
            ->toArray();

        return [
            'sms_enabled' => filter_var($settings['sms_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'sms_provider' => $settings['sms_provider'] ?? 'local_syria',
            'local_api_url' => $settings['local_api_url'] ?? '',
            'local_api_key' => $this->decryptIfEncrypted($settings['local_api_key'] ?? ''),
            'local_sender_id' => $settings['local_sender_id'] ?? '',
            'twilio_account_sid' => $settings['twilio_account_sid'] ?? '',
            'twilio_auth_token' => $this->decryptIfEncrypted($settings['twilio_auth_token'] ?? ''),
            'twilio_from_number' => $settings['twilio_from_number'] ?? '',
        ];
    }

    /**
     * Update SMS settings in database
     */
    public function updateSettings(array $newSettings): void
    {
        foreach ($newSettings as $key => $value) {
            // Encrypt sensitive fields
            if (in_array($key, ['local_api_key', 'twilio_auth_token']) && !empty($value)) {
                $value = Crypt::encryptString($value);
            }

            SystemSetting::set(
                $key,
                $value,
                'string',
                'sms'
            );
        }
    }

    /**
     * Get provider configuration
     */
    public function getProviderConfig(): array
    {
        $settings = $this->getSettings();
        $provider = $settings['sms_provider'];

        if ($provider === 'twilio') {
            return [
                'account_sid' => $settings['twilio_account_sid'],
                'auth_token' => $settings['twilio_auth_token'],
                'from_number' => $settings['twilio_from_number'],
            ];
        }

        // Default to local_syria
        return [
            'api_url' => $settings['local_api_url'],
            'api_key' => $settings['local_api_key'],
            'sender_id' => $settings['local_sender_id'],
        ];
    }

    /**
     * Initialize default settings if not exists
     */
    public function initializeDefaults(): void
    {
        $defaults = [
            'sms_enabled' => false,
            'sms_provider' => 'local_syria',
            'local_api_url' => '',
            'local_api_key' => '',
            'local_sender_id' => '',
            'twilio_account_sid' => '',
            'twilio_auth_token' => '',
            'twilio_from_number' => '',
        ];

        foreach ($defaults as $key => $value) {
            if (!SystemSetting::byKey($key)->ofGroup('sms')->exists()) {
                SystemSetting::set($key, $value, 'string', 'sms');
            }
        }
    }

    /**
     * Decrypt value if it's encrypted
     */
    protected function decryptIfEncrypted(?string $value): string
    {
        if (empty($value)) {
            return '';
        }

        try {
            return Crypt::decryptString($value);
        } catch (Exception $e) {
            // If decryption fails, it might be plain text (during first setup)
            return $value;
        }
    }

    /**
     * Decrypt Twilio auth token if encrypted
     */
    protected function decryptTwilioToken(?string $value): string
    {
        return $this->decryptIfEncrypted($value);
    }
}

