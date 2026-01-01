<?php

namespace App\Services\WhatsApp;

use App\Models\SystemSetting;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class WhatsAppSettingsService
{
    /**
     * Get WhatsApp settings from database
     */
    public function getSettings(): array
    {
        $settings = SystemSetting::where('group', 'whatsapp')
            ->get()
            ->keyBy('key')
            ->map(function ($setting) {
                return $setting->value;
            })
            ->toArray();

        return [
            'whatsapp_enabled' => filter_var($settings['whatsapp_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'api_version' => $settings['api_version'] ?? config('whatsapp.api_version', 'v20.0'),
            'phone_number_id' => $settings['phone_number_id'] ?? '',
            'waba_id' => $settings['waba_id'] ?? '',
            'access_token' => $this->decryptIfEncrypted($settings['access_token'] ?? ''),
            'verify_token' => $settings['verify_token'] ?? '',
            'app_secret' => $this->decryptIfEncrypted($settings['app_secret'] ?? ''),
            'default_from' => $settings['default_from'] ?? '',
            'strict_signature' => filter_var($settings['strict_signature'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'auto_reply' => filter_var($settings['auto_reply'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'auto_reply_message' => $settings['auto_reply_message'] ?? '',
        ];
    }

    /**
     * Update WhatsApp settings in database
     */
    public function updateSettings(array $newSettings): void
    {
        foreach ($newSettings as $key => $value) {
            // Encrypt sensitive fields
            if (in_array($key, ['access_token', 'app_secret']) && !empty($value)) {
                $value = Crypt::encryptString($value);
            }

            SystemSetting::set(
                $key,
                $value,
                'string',
                'whatsapp'
            );
        }
    }

    /**
     * Initialize default settings if not exists
     */
    public function initializeDefaults(): void
    {
        $defaults = [
            'whatsapp_enabled' => false,
            'api_version' => config('whatsapp.api_version', 'v20.0'),
            'phone_number_id' => '',
            'waba_id' => '',
            'access_token' => '',
            'verify_token' => '',
            'app_secret' => '',
            'default_from' => '',
            'strict_signature' => true,
            'auto_reply' => false,
            'auto_reply_message' => 'شكراً لك، تم استلام رسالتك. سنرد عليك قريباً.',
        ];

        foreach ($defaults as $key => $value) {
            if (!SystemSetting::byKey($key)->ofGroup('whatsapp')->exists()) {
                SystemSetting::set($key, $value, 'string', 'whatsapp');
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
}




