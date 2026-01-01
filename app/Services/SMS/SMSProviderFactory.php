<?php

namespace App\Services\SMS;

use App\Services\SMS\Providers\LocalSyriaProvider;
use App\Services\SMS\Providers\TwilioProvider;
use InvalidArgumentException;

class SMSProviderFactory
{
    /**
     * Create SMS provider instance
     */
    public static function create(string $provider, array $config): SMSProviderService
    {
        return match ($provider) {
            'local_syria' => new LocalSyriaProvider($config),
            'twilio' => new TwilioProvider($config),
            // Add more providers here in the future
            // 'nexmo' => new NexmoProvider($config),
            default => throw new InvalidArgumentException("Unsupported SMS provider: {$provider}"),
        };
    }

    /**
     * Get available providers
     */
    public static function getAvailableProviders(): array
    {
        return [
            'local_syria' => 'مخدم SMS محلي (سوريا)',
            'twilio' => 'Twilio (مجاني للتجريب)',
            // 'nexmo' => 'Vonage (Nexmo)',
        ];
    }
}

