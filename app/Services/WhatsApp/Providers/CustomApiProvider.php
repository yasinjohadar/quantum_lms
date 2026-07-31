<?php

namespace App\Services\WhatsApp\Providers;

use App\DTOs\WhatsApp\SendMessageResponseDTO;
use App\Services\WhatsApp\WhatsAppProviderService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CustomApiProvider implements WhatsAppProviderService
{
    protected string $apiUrl;
    protected string $apiKey;
    protected string $method;
    protected array $headers;

    public function __construct(array $config)
    {
        $this->apiUrl = $config['api_url'] ?? '';
        $this->apiKey = $config['api_key'] ?? '';
        $this->method = strtoupper($config['api_method'] ?? 'POST');
        $this->headers = $config['headers'] ?? [];
    }

    /**
     * Send text message via Custom API
     */
    public function sendText(string $to, string $text, bool $previewUrl = false): SendMessageResponseDTO
    {
        // Use 'text' field as per most API standards (wasenderapi.com uses 'text')
        $payload = [
            'to' => $to,
            'text' => $text,
        ];

        // Only add optional fields if needed
        // Some APIs might use 'message' instead of 'text', but 'text' is more standard
        // If your API requires 'message', you can add it via custom headers or modify this

        return $this->sendRequest($payload);
    }

    /**
     * Send template message via Custom API
     */
    public function sendTemplate(string $to, string $templateName, string $language = 'ar', array $components = []): SendMessageResponseDTO
    {
        $payload = [
            'to' => $to,
            'template' => $templateName,
            'language' => $language,
            'type' => 'template',
        ];

        if (!empty($components)) {
            $payload['components'] = $components;
        }

        return $this->sendRequest($payload);
    }

    /**
     * Send request to Custom API
     */
    protected function sendRequest(array $payload): SendMessageResponseDTO
    {
        try {
            $headers = array_merge([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ], $this->headers);

            $request = Http::timeout(30)->withHeaders($headers);

            if ($this->method === 'GET') {
                $response = $request->get($this->apiUrl, $payload);
            } else {
                // Use asJson() to send JSON body (matching Guzzle's 'json' option)
                $response = $request->asJson()->post($this->apiUrl, $payload);
            }

            $data = $response->json() ?? [];

            // Some providers return HTTP 200 with { success: false, message: "..." }
            if ($response->successful() && ($data['success'] ?? true) !== false) {
                $messageId = $data['message_id']
                    ?? $data['id']
                    ?? $data['sid']
                    ?? data_get($data, 'data.msgId')
                    ?? data_get($data, 'data.message_id')
                    ?? data_get($data, 'data.id')
                    ?? uniqid('wa_');

                Log::channel('whatsapp')->info('Custom API message sent successfully', [
                    'message_id' => $messageId,
                    'to' => $payload['to'] ?? '',
                ]);

                return new SendMessageResponseDTO(
                    metaMessageId: (string) $messageId,
                    rawResponse: is_array($data) ? $data : []
                );
            }

            $errorMessage = $data['message']
                ?? $data['error']
                ?? data_get($data, 'data.message')
                ?? 'Unknown error';
            $errorCode = $data['code'] ?? $response->status();

            Log::channel('whatsapp')->error('Custom API error', [
                'status' => $response->status(),
                'error' => $data,
                'to' => $payload['to'] ?? '',
            ]);

            throw new \Exception('Custom API error: '.$errorMessage, (int) $errorCode);
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('Exception sending Custom API message', [
                'error' => $e->getMessage(),
                'to' => $payload['to'] ?? '',
            ]);

            throw $e;
        }
    }

    /**
     * Test connection to Custom API
     */
    public function testConnection(): array
    {
        try {
            if (empty($this->apiUrl)) {
                return [
                    'success' => false,
                    'message' => 'API URL مطلوب',
                ];
            }

            if (empty($this->apiKey)) {
                return [
                    'success' => false,
                    'message' => 'API Key مطلوب',
                ];
            }

            $headers = array_merge([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ], $this->headers);

            $request = Http::timeout(10)->withHeaders($headers);

            // Use the configured method (POST or GET) to test connection
            // For POST endpoints, send a minimal test payload
            if ($this->method === 'POST') {
                $testPayload = [
                    'to' => '0000000000',
                    'text' => 'test',
                ];
                $response = $request->asJson()->post($this->apiUrl, $testPayload);
            } else {
                $response = $request->get($this->apiUrl);
            }

            $body = $response->json() ?? [];
            $apiMessage = $body['message'] ?? $body['error'] ?? data_get($body, 'data.message') ?? '';
            $status = $response->status();
            $apiMessageLower = mb_strtolower((string) $apiMessage);

            // Auth failures must never look like success.
            if ($status === 401 || $status === 403
                || str_contains($apiMessageLower, 'invalid api key')
                || str_contains($apiMessageLower, 'unauthorized')
                || str_contains($apiMessageLower, 'unauthenticated')) {
                return [
                    'success' => false,
                    'message' => 'فشل المصادقة: تحقق من API Key. '.($apiMessage ?: 'Unauthorized'),
                ];
            }

            if ($response->successful() && ($body['success'] ?? true) !== false) {
                return [
                    'success' => true,
                    'message' => 'تم الاتصال بنجاح',
                ];
            }

            // Validation errors (bad test number) mean the endpoint+key are reachable.
            if (in_array($status, [400, 404, 422], true)
                || str_contains($apiMessageLower, 'jid')
                || str_contains($apiMessageLower, 'phone')
                || str_contains($apiMessageLower, 'recipient')
                || str_contains($apiMessageLower, 'validation')) {
                return [
                    'success' => true,
                    'message' => 'الاتصال بالمزوّد ناجح، لكن بيانات الاختبار مرفوضة (متوقع). المفتاح والرابط يعملان.',
                ];
            }

            return [
                'success' => false,
                'message' => 'فشل الاتصال: '.($apiMessage ?: ('HTTP '.$status)),
            ];
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('Custom API connection test error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ];
        }
    }
}

