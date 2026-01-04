<?php

namespace App\Services\WhatsApp;

use App\Services\WhatsApp\Providers\CustomApiProvider;
use App\Services\WhatsApp\Providers\MetaProvider;
use InvalidArgumentException;

class WhatsAppProviderFactory
{
    /**
     * Create WhatsApp provider instance
     *
     * @param string $provider Provider type (meta, custom_api)
     * @param array $config Provider configuration
     * @return WhatsAppProviderService
     */
    public static function create(string $provider, array $config): WhatsAppProviderService
    {
        return match ($provider) {
            'meta' => new MetaProvider($config),
            'custom_api' => new CustomApiProvider($config),
            default => throw new InvalidArgumentException("Unsupported WhatsApp provider: {$provider}"),
        };
    }

    /**
     * Get available providers
     *
     * @return array
     */
    public static function getAvailableProviders(): array
    {
        return [
            'meta' => 'Meta WhatsApp Cloud API',
            'custom_api' => 'Custom API Provider',
        ];
    }
}

