<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AIModel;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;

class AIModelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();

        // OpenAI GPT-4
        AIModel::updateOrCreate(
            ['model_key' => 'gpt-4'],
            [
                'name' => 'GPT-4',
                'provider' => 'openai',
                'model_key' => 'gpt-4',
                'api_key' => null, // يجب إدخاله يدوياً
                'max_tokens' => 4000,
                'temperature' => 0.7,
                'is_active' => false,
                'is_default' => false,
                'priority' => 10,
                'cost_per_1k_tokens' => 0.03,
                'capabilities' => ['all'],
                'created_by' => $admin?->id,
            ]
        );

        // OpenAI GPT-3.5 Turbo
        AIModel::updateOrCreate(
            ['model_key' => 'gpt-3.5-turbo'],
            [
                'name' => 'GPT-3.5 Turbo',
                'provider' => 'openai',
                'model_key' => 'gpt-3.5-turbo',
                'api_key' => null,
                'max_tokens' => 4000,
                'temperature' => 0.7,
                'is_active' => false,
                'is_default' => false,
                'priority' => 8,
                'cost_per_1k_tokens' => 0.002,
                'capabilities' => ['all'],
                'created_by' => $admin?->id,
            ]
        );

        // Anthropic Claude 3 Opus
        AIModel::updateOrCreate(
            ['model_key' => 'claude-3-opus-20240229'],
            [
                'name' => 'Claude 3 Opus',
                'provider' => 'anthropic',
                'model_key' => 'claude-3-opus-20240229',
                'api_key' => null,
                'max_tokens' => 4096,
                'temperature' => 0.7,
                'is_active' => false,
                'is_default' => false,
                'priority' => 9,
                'cost_per_1k_tokens' => 0.015,
                'capabilities' => ['all'],
                'created_by' => $admin?->id,
            ]
        );

        // Google Gemini Pro
        AIModel::updateOrCreate(
            ['model_key' => 'gemini-pro'],
            [
                'name' => 'Gemini Pro',
                'provider' => 'google',
                'model_key' => 'gemini-pro',
                'api_key' => null,
                'max_tokens' => 2048,
                'temperature' => 0.7,
                'is_active' => false,
                'is_default' => false,
                'priority' => 7,
                'cost_per_1k_tokens' => 0.0005,
                'capabilities' => ['all'],
                'created_by' => $admin?->id,
            ]
        );

        // Local LLM (Ollama) - Llama 2
        AIModel::updateOrCreate(
            ['model_key' => 'llama2'],
            [
                'name' => 'Llama 2 (Local)',
                'provider' => 'local',
                'model_key' => 'llama2',
                'api_key' => null,
                'base_url' => 'http://localhost:11434',
                'api_endpoint' => '/api/chat',
                'max_tokens' => 2048,
                'temperature' => 0.7,
                'is_active' => false,
                'is_default' => false,
                'priority' => 5,
                'cost_per_1k_tokens' => 0,
                'capabilities' => ['all'],
                'created_by' => $admin?->id,
            ]
        );

        $this->command->info('تم إنشاء موديلات AI بنجاح!');
        $this->command->warn('ملاحظة: يجب إدخال API Keys يدوياً من لوحة التحكم.');
    }
}
