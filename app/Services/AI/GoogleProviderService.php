<?php

namespace App\Services\AI;

use App\Models\AIModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleProviderService extends AIProviderService
{
    private const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta';

    public function chat(array $messages, array $options = []): array
    {
        $url = $this->getBaseUrl() ?? self::BASE_URL;
        $endpoint = $this->getApiEndpoint() ?? '/models/' . $this->model->model_key . ':generateContent';

        // تحويل تنسيق الرسائل إلى Google Gemini
        $contents = [];
        foreach ($messages as $message) {
            if ($message['role'] !== 'system') {
                $contents[] = [
                    'role' => $message['role'] === 'assistant' ? 'model' : 'user',
                    'parts' => [['text' => $message['content']]]
                ];
            }
        }

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'maxOutputTokens' => $options['max_tokens'] ?? $this->model->max_tokens,
                'temperature' => $options['temperature'] ?? $this->model->temperature,
            ],
        ];

        try {
            $apiKey = $this->getApiKey();
            $response = Http::timeout(60)->post($url . $endpoint . '?key=' . $apiKey, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                
                return [
                    'success' => true,
                    'content' => $content,
                    'tokens_used' => $data['usageMetadata']['totalTokenCount'] ?? 0,
                    'prompt_tokens' => $data['usageMetadata']['promptTokenCount'] ?? 0,
                    'completion_tokens' => $data['usageMetadata']['candidatesTokenCount'] ?? 0,
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];
        } catch (\Exception $e) {
            Log::error('Google Gemini API Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function generateText(string $prompt, array $options = []): string
    {
        $messages = [
            ['role' => 'user', 'content' => $prompt]
        ];

        $result = $this->chat($messages, $options);
        return $result['success'] ? $result['content'] : '';
    }

    public function estimateTokens(string $text): int
    {
        // تقدير تقريبي: ~4 characters per token
        return (int) ceil(strlen($text) / 4);
    }

    public function testConnection(): bool
    {
        try {
            $result = $this->chat([
                ['role' => 'user', 'content' => 'Hello']
            ], ['max_tokens' => 5]);

            return $result['success'] ?? false;
        } catch (\Exception $e) {
            return false;
        }
    }
}

