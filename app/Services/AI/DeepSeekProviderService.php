<?php

namespace App\Services\AI;

use App\Models\AIModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * DeepSeek AI Provider Service
 * 
 * يوفر وصولاً لخدمات DeepSeek AI من خلال DeepSeek API
 * 
 * @see https://platform.deepseek.com
 */
class DeepSeekProviderService extends AIProviderService
{
    private const BASE_URL = 'https://api.deepseek.com/v1';

    /**
     * إرسال رسالة في محادثة
     */
    public function chat(array $messages, array $options = []): array
    {
        $url = $this->getBaseUrl() ?? self::BASE_URL;
        $endpoint = $this->getApiEndpoint() ?? '/chat/completions';

        $apiKey = $this->getApiKey();
        if (!$apiKey) {
            $error = 'API Key غير موجود. يرجى إدخال API Key في حقل "مفتاح API" وحفظ النموذج أولاً.';
            $this->setLastError($error);
            return [
                'success' => false,
                'error' => $error,
            ];
        }

        $payload = [
            'model' => $this->model->model_key,
            'messages' => $messages,
            'max_tokens' => (int) ($options['max_tokens'] ?? $this->model->max_tokens),
            'temperature' => (float) ($options['temperature'] ?? $this->model->temperature),
        ];

        // إضافة معاملات إضافية إذا كانت موجودة
        if (isset($options['top_p'])) {
            $payload['top_p'] = (float) $options['top_p'];
        }
        if (isset($options['frequency_penalty'])) {
            $payload['frequency_penalty'] = (float) $options['frequency_penalty'];
        }
        if (isset($options['presence_penalty'])) {
            $payload['presence_penalty'] = (float) $options['presence_penalty'];
        }
        if (isset($options['stream'])) {
            $payload['stream'] = (bool) $options['stream'];
        }

        try {
            $fullUrl = $url . $endpoint;
            
            Log::info('DeepSeek API Request', [
                'url' => $fullUrl,
                'model' => $this->model->model_key,
                'max_tokens' => $payload['max_tokens'],
                'temperature' => $payload['temperature'],
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . trim($apiKey),
                'Content-Type' => 'application/json',
            ])->timeout(300)->post($fullUrl, $payload);

            Log::info('DeepSeek API Response', [
                'status' => $response->status(),
                'success' => $response->successful(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'content' => $data['choices'][0]['message']['content'] ?? '',
                    'tokens_used' => $data['usage']['total_tokens'] ?? 0,
                    'prompt_tokens' => $data['usage']['prompt_tokens'] ?? 0,
                    'completion_tokens' => $data['usage']['completion_tokens'] ?? 0,
                    'model_used' => $data['model'] ?? $this->model->model_key,
                ];
            }

            // معالجة الأخطاء
            $errorData = $response->json();
            $errorMessage = $errorData['error']['message'] ?? 'خطأ غير معروف';
            $errorType = $errorData['error']['type'] ?? null;
            $errorCode = $errorData['error']['code'] ?? null;
            
            Log::error('DeepSeek API Error', [
                'status' => $response->status(),
                'error' => $errorMessage,
                'type' => $errorType,
                'code' => $errorCode,
                'response' => $errorData,
            ]);

            // رسائل خطأ واضحة بالعربية
            $friendlyMessage = $this->getFriendlyErrorMessage($response->status(), $errorMessage, $errorType);

            $this->setLastError($friendlyMessage);

            return [
                'success' => false,
                'error' => $friendlyMessage,
                'status_code' => $response->status(),
                'raw_error' => $errorMessage,
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('DeepSeek API Connection Exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            
            $error = 'خطأ في الاتصال بخادم DeepSeek. يرجى التحقق من الاتصال بالإنترنت والمحاولة مرة أخرى.';
            $this->setLastError($error);
            
            return [
                'success' => false,
                'error' => $error,
            ];
        } catch (\Exception $e) {
            Log::error('DeepSeek API Exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            
            $error = 'خطأ في الاتصال: ' . $e->getMessage();
            $this->setLastError($error);
            
            return [
                'success' => false,
                'error' => $error,
            ];
        }
    }

    /**
     * الحصول على رسالة خطأ واضحة
     */
    private function getFriendlyErrorMessage(int $statusCode, string $errorMessage, ?string $errorType = null): string
    {
        // معالجة خاصة لرسائل الخطأ الشائعة
        $lowerMessage = strtolower($errorMessage);
        if (str_contains($lowerMessage, 'insufficient balance') || str_contains($lowerMessage, 'insufficient_balance')) {
            return 'رصيد DeepSeek غير كافٍ. يرجى إضافة رصيد إلى حسابك من platform.deepseek.com.';
        }
        
        return match($statusCode) {
            400 => 'طلب غير صحيح: ' . $errorMessage,
            401 => 'API Key غير صحيح أو منتهي الصلاحية. يرجى التحقق من API Key من platform.deepseek.com.',
            402 => 'رصيد DeepSeek غير كافٍ. يرجى إضافة رصيد إلى حسابك من platform.deepseek.com.',
            403 => 'الوصول مرفوض. تحقق من صلاحيات API Key.',
            404 => 'Model Key غير صحيح أو غير متاح. تأكد من أن Model Key صحيح (مثل: deepseek-chat, deepseek-coder).',
            408 => 'انتهت مهلة الطلب. جرّب مرة أخرى.',
            429 => 'تم تجاوز حد الطلبات. انتظر قليلاً ثم جرّب مرة أخرى.',
            500, 502, 503 => 'خطأ في خادم DeepSeek. يرجى المحاولة مرة أخرى لاحقاً.',
            default => match($errorType) {
                'insufficient_quota', 'insufficient_balance' => 'رصيد DeepSeek غير كافٍ. يرجى إضافة رصيد إلى حسابك من platform.deepseek.com.',
                'invalid_request_error' => 'طلب غير صحيح: ' . $errorMessage,
                'rate_limit_error' => 'تم تجاوز حد الطلبات. يرجى الانتظار قليلاً ثم المحاولة مرة أخرى.',
                default => 'خطأ من DeepSeek: ' . $errorMessage . " (رمز: {$statusCode})",
            },
        };
    }

    /**
     * توليد نص من prompt
     */
    public function generateText(string $prompt, array $options = []): string
    {
        $messages = [
            ['role' => 'user', 'content' => $prompt]
        ];

        $result = $this->chat($messages, $options);
        
        if (!$result['success']) {
            $this->setLastError($result['error'] ?? 'خطأ غير معروف في توليد النص');
            return '';
        }
        
        return $result['content'] ?? '';
    }

    /**
     * تقدير عدد الـ tokens
     */
    public function estimateTokens(string $text): int
    {
        // تقدير تقريبي: ~4 characters per token
        // يمكن استخدام مكتبة tiktoken للحصول على تقدير أدق
        return (int) ceil(strlen($text) / 4);
    }

    /**
     * اختبار الاتصال
     */
    public function testConnection(): bool
    {
        try {
            $result = $this->chat([
                ['role' => 'user', 'content' => 'Say "OK" only.']
            ], ['max_tokens' => 10]);

            if (!$result['success']) {
                $this->setLastError($result['error'] ?? 'فشل اختبار الاتصال');
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('DeepSeek test connection failed: ' . $e->getMessage());
            $this->setLastError('فشل اختبار الاتصال: ' . $e->getMessage());
            return false;
        }
    }
}

