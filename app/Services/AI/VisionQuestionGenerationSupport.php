<?php

namespace App\Services\AI;

/**
 * بناء رسائل الدردشة متعددة الوسائط (نص + صورة) بتنسيق OpenAI،
 * وتحويلها لتنسيقات مزوّدين آخرين عند الحاجة.
 */
final class VisionQuestionGenerationSupport
{
    /**
     * رسائل جاهزة لـ chat/completions (OpenAI، OpenRouter، Z.ai، إلخ).
     *
     * @return array<int, array{role: string, content: array<int, mixed>|string}>
     */
    public static function buildOpenAiStyleMessages(string $textPrompt, string $mimeType, string $binaryImage): array
    {
        $b64 = base64_encode($binaryImage);
        $mimeType = self::normalizeMime($mimeType);

        return [[
            'role' => 'user',
            'content' => [
                ['type' => 'text', 'text' => $textPrompt],
                [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => 'data:'.$mimeType.';base64,'.$b64,
                    ],
                ],
            ],
        ]];
    }

    public static function normalizeMime(string $mime): string
    {
        $mime = strtolower(trim($mime));
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        return in_array($mime, $allowed, true) ? $mime : 'image/jpeg';
    }

    /**
     * تحويل رسائل تحتوي محتوى مستخدم من نوع OpenAI vision إلى تنسيق Anthropic Messages API.
     *
     * @param  array<int, array<string, mixed>>  $messages
     * @return array<int, array<string, mixed>>
     */
    public static function adaptMessagesForAnthropic(array $messages): array
    {
        $out = [];
        foreach ($messages as $message) {
            if (($message['role'] ?? '') === 'system') {
                $out[] = $message;

                continue;
            }
            $content = $message['content'] ?? '';
            if (! is_array($content)) {
                $out[] = $message;

                continue;
            }
            $blocks = [];
            foreach ($content as $part) {
                $type = $part['type'] ?? '';
                if ($type === 'text') {
                    $blocks[] = [
                        'type' => 'text',
                        'text' => (string) ($part['text'] ?? ''),
                    ];
                } elseif ($type === 'image_url') {
                    $url = (string) (($part['image_url']['url'] ?? $part['image_url'] ?? ''));
                    if (preg_match('#^data:([^;]+);base64,(.+)$#', $url, $m)) {
                        $blocks[] = [
                            'type' => 'image',
                            'source' => [
                                'type' => 'base64',
                                'media_type' => self::normalizeMime($m[1]),
                                'data' => $m[2],
                            ],
                        ];
                    }
                }
            }
            if ($blocks === []) {
                $out[] = $message;
            } else {
                $out[] = [
                    'role' => $message['role'],
                    'content' => $blocks,
                ];
            }
        }

        return $out;
    }

    /**
     * تحويل رسائل OpenAI-style (مع صورة) إلى هيكل contents الخاص بـ Gemini.
     *
     * @param  array<int, array<string, mixed>>  $messages
     * @return array<int, array{role: string, parts: array<int, mixed>}>
     */
    public static function adaptMessagesToGeminiContents(array $messages): array
    {
        $contents = [];
        foreach ($messages as $message) {
            if (($message['role'] ?? '') === 'system') {
                continue;
            }
            $role = ($message['role'] ?? 'user') === 'assistant' ? 'model' : 'user';
            $content = $message['content'] ?? '';
            $parts = [];
            if (is_string($content)) {
                $parts[] = ['text' => $content];
            } elseif (is_array($content)) {
                foreach ($content as $part) {
                    $type = $part['type'] ?? '';
                    if ($type === 'text') {
                        $parts[] = ['text' => (string) ($part['text'] ?? '')];
                    } elseif ($type === 'image_url') {
                        $url = (string) (($part['image_url']['url'] ?? ''));
                        if (preg_match('#^data:([^;]+);base64,(.+)$#', $url, $m)) {
                            $parts[] = [
                                'inline_data' => [
                                    'mime_type' => self::normalizeMime($m[1]),
                                    'data' => $m[2],
                                ],
                            ];
                        }
                    }
                }
            }
            if ($parts !== []) {
                $contents[] = ['role' => $role, 'parts' => $parts];
            }
        }

        return $contents;
    }

    public static function providerUsesOpenAiVisionFormat(string $provider): bool
    {
        return in_array($provider, ['openai', 'openrouter', 'custom', 'zai'], true);
    }

    public static function providerSupportsVisionConversion(string $provider): bool
    {
        return in_array($provider, ['openai', 'openrouter', 'custom', 'zai', 'anthropic', 'google'], true);
    }
}
