<?php

namespace App\Services\WhatsApp\Providers;

use App\DTOs\WhatsApp\SendMessageResponseDTO;
use App\Exceptions\WhatsAppApiException;
use App\Services\WhatsApp\WhatsAppClient;
use App\Services\WhatsApp\WhatsAppProviderService;
use Illuminate\Support\Facades\Log;

class MetaProvider implements WhatsAppProviderService
{
    protected WhatsAppClient $client;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new WhatsAppClient(
            apiVersion: $config['api_version'] ?? null,
            phoneNumberId: $config['phone_number_id'] ?? null,
            accessToken: $config['access_token'] ?? null
        );
    }

    /**
     * Send text message via Meta WhatsApp Cloud API
     */
    public function sendText(string $to, string $text, bool $previewUrl = false): SendMessageResponseDTO
    {
        return $this->client->sendText($to, $text, $previewUrl);
    }

    /**
     * Send template message via Meta WhatsApp Cloud API
     */
    public function sendTemplate(string $to, string $templateName, string $language = 'ar', array $components = []): SendMessageResponseDTO
    {
        return $this->client->sendTemplate($to, $templateName, $language, $components);
    }

    /**
     * Test connection to Meta WhatsApp API
     */
    public function testConnection(): array
    {
        try {
            // Try to get phone number info as a connection test
            $apiVersion = config('whatsapp.api_version', 'v20.0');
            $baseUrl = config('whatsapp.base_url', 'https://graph.facebook.com');
            $phoneNumberId = config('whatsapp.phone_number_id');
            $accessToken = config('whatsapp.access_token');

            if (empty($phoneNumberId) || empty($accessToken)) {
                return [
                    'success' => false,
                    'message' => 'Phone Number ID و Access Token مطلوبان',
                ];
            }

            $url = "{$baseUrl}/{$apiVersion}/{$phoneNumberId}";

            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->withToken($accessToken)
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message' => 'تم الاتصال بنجاح. ' . ($data['display_phone_number'] ?? ''),
                ];
            } else {
                $errorData = $response->json();
                $errorMessage = $errorData['error']['message'] ?? 'فشل الاتصال';
                return [
                    'success' => false,
                    'message' => 'فشل الاتصال: ' . $errorMessage,
                ];
            }
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('Meta Provider connection test error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ];
        }
    }
}

