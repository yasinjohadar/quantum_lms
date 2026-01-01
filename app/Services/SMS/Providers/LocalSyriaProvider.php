<?php

namespace App\Services\SMS\Providers;

use App\Services\SMS\SMSProviderService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class LocalSyriaProvider implements SMSProviderService
{
    protected string $apiUrl;
    protected string $apiKey;
    protected string $senderId;

    public function __construct(array $config)
    {
        $this->apiUrl = $config['api_url'] ?? '';
        $this->apiKey = $config['api_key'] ?? '';
        $this->senderId = $config['sender_id'] ?? '';
    }

    /**
     * Send SMS message
     */
    public function send(string $to, string $message): array
    {
        try {
            // Clean phone number (remove + and spaces)
            $to = preg_replace('/[^0-9]/', '', $to);

            // Prepare request data
            $data = [
                'phone' => $to,
                'message' => $message,
                'sender_id' => $this->senderId,
            ];

            // Make API request
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($this->apiUrl, $data);

            if ($response->successful()) {
                $responseData = $response->json();
                
                return [
                    'success' => true,
                    'message' => 'تم إرسال الرسالة بنجاح',
                    'message_id' => $responseData['message_id'] ?? $responseData['id'] ?? null,
                ];
            } else {
                $errorMessage = $response->json()['message'] ?? $response->json()['error'] ?? 'فشل إرسال الرسالة';
                
                Log::error('SMS API Error', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'phone' => $to,
                ]);

                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'message_id' => null,
                ];
            }
        } catch (Exception $e) {
            Log::error('SMS Send Exception', [
                'message' => $e->getMessage(),
                'phone' => $to,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'خطأ في الاتصال: ' . $e->getMessage(),
                'message_id' => null,
            ];
        }
    }

    /**
     * Test connection to SMS provider
     */
    public function testConnection(): array
    {
        try {
            // Make a simple test request (you may need to adjust this based on your API)
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->get(rtrim($this->apiUrl, '/') . '/status'); // Adjust endpoint as needed

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'تم الاتصال بالخادم بنجاح',
                ];
            } else {
                // If status endpoint doesn't exist, try sending a test message to a test number
                // For now, we'll just check if we can reach the API
                return [
                    'success' => false,
                    'message' => 'فشل الاتصال: ' . ($response->json()['message'] ?? 'خطأ غير معروف'),
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'فشل الاتصال: ' . $e->getMessage(),
            ];
        }
    }
}



