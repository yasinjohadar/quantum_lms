<?php

namespace App\Services\AI;

use App\Models\AIModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Z.ai Provider Service
 * 
 * يوفر وصولاً إلى Z.ai GLM-4.7 Model
 * متوافق مع OpenAI API format
 * 
 * @see https://z.ai/subscribe
 */
class ZaiProviderService extends AIProviderService
{
    private const BASE_URL = 'https://api.z.ai/api/coding/paas/v4';

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

        try {
            $fullUrl = $url . $endpoint;
            
            Log::info('Z.ai API Request', [
                'url' => $fullUrl,
                'model' => $this->model->model_key,
                'max_tokens' => $payload['max_tokens'],
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . trim($apiKey),
                'Content-Type' => 'application/json',
            ])->timeout(300)->post($fullUrl, $payload);

            Log::info('Z.ai API Response', [
                'status' => $response->status(),
                'success' => $response->successful(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Log the full response structure for debugging
                Log::info('Z.ai API Full Response Data', [
                    'has_choices' => isset($data['choices']),
                    'choices_count' => isset($data['choices']) ? count($data['choices']) : 0,
                    'has_message' => isset($data['choices'][0]['message']),
                    'has_content' => isset($data['choices'][0]['message']['content']),
                    'content_length' => isset($data['choices'][0]['message']['content']) ? strlen($data['choices'][0]['message']['content']) : 0,
                    'full_response_keys' => array_keys($data),
                    'full_response' => $data, // Full response for debugging
                ]);
                
                // Check if choices array exists and has at least one element
                if (!isset($data['choices']) || !is_array($data['choices']) || empty($data['choices'])) {
                    $error = 'الرد من Z.ai API لا يحتوي على choices أو choices فارغ';
                    Log::warning('Z.ai API Response Missing Choices', [
                        'response_data' => $data,
                    ]);
                    $this->setLastError($error);
                    return [
                        'success' => false,
                        'error' => $error,
                    ];
                }
                
                // Check if message exists
                if (!isset($data['choices'][0]['message'])) {
                    $error = 'الرد من Z.ai API لا يحتوي على message في choices[0]';
                    Log::warning('Z.ai API Response Missing Message', [
                        'choices_0' => $data['choices'][0] ?? null,
                    ]);
                    $this->setLastError($error);
                    return [
                        'success' => false,
                        'error' => $error,
                    ];
                }
                
                // Extract content
                $content = $data['choices'][0]['message']['content'] ?? '';
                
                // Check if content is empty
                if (empty($content)) {
                    $error = 'الرد من Z.ai API يحتوي على content فارغ';
                    Log::warning('Z.ai API Response Empty Content', [
                        'message' => $data['choices'][0]['message'],
                        'full_response' => $data,
                    ]);
                    $this->setLastError($error);
                    return [
                        'success' => false,
                        'error' => $error,
                    ];
                }
                
                Log::info('Z.ai API Content Extracted Successfully', [
                    'content_length' => strlen($content),
                    'content_preview' => substr($content, 0, 200),
                ]);
                
                return [
                    'success' => true,
                    'content' => $content,
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
            
            Log::error('Z.ai API Error', [
                'status' => $response->status(),
                'error' => $errorMessage,
                'type' => $errorType,
                'code' => $errorCode,
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
        } catch (\Exception $e) {
            Log::error('Z.ai API Exception: ' . $e->getMessage(), [
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
        if ($statusCode === 401) {
            return 'API Key غير صحيح أو منتهي الصلاحية. يرجى التحقق من API Key من Z.ai Platform.';
        } elseif ($statusCode === 404) {
            return 'Model Key غير صحيح أو غير متاح. تأكد من أن Model Key صحيح (glm-4.7).';
        } elseif ($statusCode === 429) {
            return 'تم تجاوز حد الاستخدام. يرجى الانتظار قليلاً ثم المحاولة مرة أخرى، أو التحقق من خطة Z.ai الخاصة بك.';
        } elseif ($statusCode === 500 || $statusCode === 502 || $statusCode === 503) {
            return 'خطأ في خادم Z.ai. يرجى المحاولة مرة أخرى لاحقاً.';
        } elseif ($errorType === 'insufficient_quota') {
            return 'رصيد Z.ai غير كافٍ. يرجى إضافة رصيد إلى حسابك من Z.ai Platform.';
        } elseif ($errorType === 'invalid_request_error') {
            return 'طلب غير صحيح: ' . $errorMessage;
        }

        return 'خطأ من Z.ai: ' . $errorMessage;
    }

    public function generateText(string $prompt, array $options = []): string
    {
        $messages = [
            ['role' => 'user', 'content' => $prompt]
        ];

        $result = $this->chat($messages, $options);
        
        if (!$result['success']) {
            $errorMessage = $result['error'] ?? 'خطأ غير معروف في توليد النص';
            $this->setLastError($errorMessage);
            Log::warning('Z.ai generateText failed', [
                'error' => $errorMessage,
                'result' => $result,
            ]);
            return '';
        }
        
        $content = $result['content'] ?? '';
        
        // Double check that content is not empty
        if (empty($content)) {
            $errorMessage = 'تم الحصول على رد ناجح من Z.ai API لكن المحتوى فارغ';
            $this->setLastError($errorMessage);
            Log::warning('Z.ai generateText returned empty content', [
                'result' => $result,
                'has_content_key' => isset($result['content']),
            ]);
            return '';
        }
        
        Log::debug('Z.ai generateText succeeded', [
            'content_length' => strlen($content),
        ]);
        
        return $content;
    }

    public function estimateTokens(string $text): int
    {
        // تقدير تقريبي: ~4 characters per token
        // يمكن استخدام مكتبة tiktoken للحصول على تقدير أدق
        return (int) ceil(strlen($text) / 4);
    }

    public function testConnection(): bool
    {
        try {
            $result = $this->chat([
                ['role' => 'user', 'content' => 'Say "OK" only.']
            ], ['max_tokens' => 10]);

            return $result['success'] ?? false;
        } catch (\Exception $e) {
            Log::error('Z.ai test connection failed: ' . $e->getMessage());
            $this->setLastError('فشل اختبار الاتصال: ' . $e->getMessage());
            return false;
        }
    }
}

