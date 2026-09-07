<?php

namespace App\Services\WhatsApp\Providers;

use App\DTOs\WhatsApp\SendMessageResponseDTO;
use App\Services\WhatsApp\WhatsAppProviderService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlaxxaProvider implements WhatsAppProviderService
{
    protected string $apiUrl;
    protected string $token;
    protected int $timeout;

    public function __construct(array $config)
    {
        $this->apiUrl = rtrim($config['api_url'] ?? 'https://wapi.flaxxa.com', '/');
        $this->token = $config['token'] ?? '';
        $this->timeout = (int) ($config['timeout'] ?? 30);
    }

    /**
     * Send free-form text message via Flaxxa Wapi (POST /api/v1/sendmessage).
     * Only delivers within the 24-hour customer-reply window.
     */
    public function sendText(string $to, string $text, bool $previewUrl = false): SendMessageResponseDTO
    {
        return $this->sendRequest('/api/v1/sendmessage', [
            'token' => $this->token,
            'phone' => $to,
            'message' => $text,
        ]);
    }

    /**
     * Send approved template message via Flaxxa Wapi (POST /api/v1/sendtemplatemessage).
     */
    public function sendTemplate(string $to, string $templateName, string $language = 'ar', array $components = []): SendMessageResponseDTO
    {
        $payload = [
            'token' => $this->token,
            'phone' => $to,
            'template_name' => $templateName,
            'template_language' => $language,
        ];

        if (!empty($components)) {
            $payload['components'] = $components;
        }

        return $this->sendRequest('/api/v1/sendtemplatemessage', $payload);
    }

    /**
     * List the brand's approved WhatsApp templates (GET /api/v1/getTemplates).
     * Not part of WhatsAppProviderService — used only from the admin settings controller.
     */
    public function getTemplates(): array
    {
        $response = Http::timeout($this->timeout)
            ->get($this->apiUrl . '/api/v1/getTemplates', ['token' => $this->token]);

        $data = $response->json() ?? [];

        if (!$response->successful() || ($data['status'] ?? '') === 'error') {
            $message = $data['message'] ?? 'Unknown error';
            Log::channel('whatsapp')->error('Flaxxa getTemplates error', [
                'status' => $response->status(),
                'error' => $data,
            ]);

            throw new \Exception('Flaxxa API error: ' . $message, $response->status());
        }

        return $data['templates'] ?? [];
    }

    /**
     * Fetch the real delivery status for a previously-sent message
     * (POST /api/v1/get_message_response). A "success" response from
     * send*() only means Flaxxa accepted the request — this is the
     * only way to know whether it was actually delivered by Meta.
     */
    public function getMessageStatus(int $messageId): array
    {
        $response = Http::timeout($this->timeout)
            ->asJson()
            ->post($this->apiUrl . '/api/v1/get_message_response', [
                'token' => $this->token,
                'message_id' => $messageId,
            ]);

        $data = $response->json() ?? [];

        if (!$response->successful() || ($data['status'] ?? '') === 'error') {
            $message = $data['message'] ?? $data['msg'] ?? 'Unknown error';
            throw new \Exception('Flaxxa API error: ' . $message, $response->status());
        }

        return $data['data'] ?? [];
    }

    protected function sendRequest(string $path, array $payload): SendMessageResponseDTO
    {
        try {
            $response = Http::timeout($this->timeout)
                ->asJson()
                ->post($this->apiUrl . $path, $payload);

            $data = $response->json() ?? [];

            if ($response->successful() && ($data['status'] ?? 'success') !== 'error') {
                $messageId = $data['message_wamid'] ?? $data['message_id'] ?? uniqid('wa_');

                Log::channel('whatsapp')->info('Flaxxa message sent successfully', [
                    'message_id' => $messageId,
                    'to' => $payload['phone'] ?? '',
                ]);

                return new SendMessageResponseDTO(
                    metaMessageId: (string) $messageId,
                    rawResponse: is_array($data) ? $data : []
                );
            }

            $errorMessage = $data['message'] ?? 'Unknown error';

            Log::channel('whatsapp')->error('Flaxxa API error', [
                'status' => $response->status(),
                'error' => $data,
                'to' => $payload['phone'] ?? '',
            ]);

            throw new \Exception('Flaxxa API error: ' . $errorMessage, $response->status());
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('Exception sending Flaxxa message', [
                'error' => $e->getMessage(),
                'to' => $payload['phone'] ?? '',
            ]);

            throw $e;
        }
    }

    /**
     * Test connection to Flaxxa Wapi by listing approved templates.
     */
    public function testConnection(): array
    {
        try {
            if (empty($this->apiUrl)) {
                return [
                    'success' => false,
                    'message' => 'رابط API مطلوب',
                ];
            }

            if (empty($this->token)) {
                return [
                    'success' => false,
                    'message' => 'API Token مطلوب',
                ];
            }

            $response = Http::timeout(10)
                ->get($this->apiUrl . '/api/v1/getTemplates', ['token' => $this->token]);

            $status = $response->status();
            $data = $response->json() ?? [];
            $apiMessage = $data['message'] ?? '';

            if ($status === 401 || ($data['status'] ?? '') === 'error') {
                return [
                    'success' => false,
                    'message' => 'فشل المصادقة: تحقق من API Token. ' . ($apiMessage ?: 'Invalid API Token.'),
                ];
            }

            if ($status === 403) {
                return [
                    'success' => false,
                    'message' => 'الوصول مرفوض: ' . ($apiMessage ?: 'تأكد من أن الخطة تدعم API وأن واتساب مهيّأ لهذا الحساب.'),
                ];
            }

            if ($response->successful()) {
                $count = is_array($data['templates'] ?? null) ? count($data['templates']) : 0;

                return [
                    'success' => true,
                    'message' => "تم الاتصال بنجاح. عدد القوالب المتاحة: {$count}.",
                ];
            }

            return [
                'success' => false,
                'message' => 'فشل الاتصال: ' . ($apiMessage ?: ('HTTP ' . $status)),
            ];
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('Flaxxa Provider connection test error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ];
        }
    }
}
