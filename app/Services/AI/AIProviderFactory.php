<?php

namespace App\Services\AI;

use App\Models\AIModel;
use InvalidArgumentException;

class AIProviderFactory
{
    /**
     * إنشاء instance من Provider حسب نوع الموديل
     */
    public static function create(AIModel $model): AIProviderService
    {
        return match($model->provider) {
            'openai' => new OpenAIProviderService($model),
            'anthropic' => new AnthropicProviderService($model),
            'google' => new GoogleProviderService($model),
            'openrouter' => new OpenRouterProviderService($model),
            'local' => new LocalLLMProviderService($model),
            'custom' => new OpenRouterProviderService($model), // Custom يستخدم نفس بنية OpenAI
            default => throw new InvalidArgumentException("Unsupported provider: {$model->provider}"),
        };
    }
}

