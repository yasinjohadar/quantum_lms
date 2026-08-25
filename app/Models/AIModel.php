<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class AIModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ai_models';

    protected $fillable = [
        'name',
        'provider',
        'model_key',
        'api_key',
        'api_endpoint',
        'base_url',
        'max_tokens',
        'temperature',
        'is_active',
        'is_default',
        'priority',
        'cost_per_1k_tokens',
        'capabilities',
        'settings',
        'created_by',
    ];

    protected $casts = [
        'max_tokens' => 'integer',
        'temperature' => 'decimal:2',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'priority' => 'integer',
        'cost_per_1k_tokens' => 'decimal:6',
        'capabilities' => 'array',
        'settings' => 'array',
    ];

    /**
     * المزودون المدعومون
     */
    public const PROVIDERS = [
        'openai' => 'OpenAI',
        'anthropic' => 'Anthropic (Claude)',
        'google' => 'Google (Gemini)',
        'openrouter' => 'OpenRouter (موصى به - متعدد الموديلات)',
        'zai' => 'Z.ai (GLM)',
        'local' => 'Local LLM (Ollama)',
        'manus' => 'Manus AI',
        'deepseek' => 'DeepSeek',
        'custom' => 'Custom Provider',
    ];

    /**
     * القدرات المدعومة
     */
    public const CAPABILITIES = [
        'chat' => 'محادثة',
        'question_generation' => 'توليد أسئلة',
        'question_solving' => 'حل أسئلة',
        'vision' => 'تحليل الصور (رؤية)',
        'all' => 'جميع القدرات',
    ];

    /**
     * الموديلات المدعومة لكل مزود
     */
    public const SUPPORTED_MODELS = [
        'openai' => [
            'gpt-4' => 'GPT-4',
            'gpt-4-turbo' => 'GPT-4 Turbo',
            'gpt-4o' => 'GPT-4o',
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
        ],
        'anthropic' => [
            'claude-3-opus-20240229' => 'Claude 3 Opus',
            'claude-3-sonnet-20240229' => 'Claude 3 Sonnet',
            'claude-3-haiku-20240307' => 'Claude 3 Haiku',
        ],
        'google' => [
            // الموديلات الجديدة (2024-2025)
            'gemini-2.0-flash' => 'Gemini 2.0 Flash (موصى به)',
            'gemini-2.5-flash' => 'Gemini 2.5 Flash',
            'gemini-2.5-pro' => 'Gemini 2.5 Pro',
            'gemini-flash-latest' => 'Gemini Flash Latest',
            'gemini-pro-latest' => 'Gemini Pro Latest',
            'gemini-2.0-flash-lite' => 'Gemini 2.0 Flash-Lite',
        ],
        'local' => [
            'llama2' => 'Llama 2',
            'llama3' => 'Llama 3',
            'mistral' => 'Mistral',
        ],
        'openrouter' => [
            // موديلات مجانية (Free) - متاحة فعلياً
            'google/gemini-2.0-flash-exp:free' => '🆓 Gemini 2.0 Flash (مجاني - موصى به)',
            'allenai/olmo-3.1-32b-think:free' => '🆓 OLMo 3.1 32B Think (مجاني)',
            'xiaomi/mimo-v2-flash:free' => '🆓 Xiaomi MiMo v2 Flash (مجاني)',
            'nvidia/nemotron-3-nano-30b-a3b:free' => '🆓 NVIDIA Nemotron 3 (مجاني)',
            'mistralai/devstral-2512:free' => '🆓 Mistral Devstral (مجاني)',
            'nex-agi/deepseek-v3.1-nex-n1:free' => '🆓 DeepSeek v3.1 (مجاني)',
            'google/gemma-3-27b-it:free' => '🆓 Gemma 3 27B (مجاني)',
            'microsoft/phi-4:free' => '🆓 Microsoft Phi-4 (مجاني)',
            'qwen/qwen-2.5-72b-instruct:free' => '🆓 Qwen 2.5 72B (مجاني)',
            // موديلات مدفوعة (رخيصة)
            'anthropic/claude-3.5-sonnet' => '💰 Claude 3.5 Sonnet',
            'openai/gpt-4o' => '💰 GPT-4o',
            'google/gemini-2.5-pro-preview' => '💰 Gemini 2.5 Pro',
        ],
        'manus' => [
            'manus-v1' => 'Manus v1',
            'manus-chat' => 'Manus Chat',
            // إضافة موديلات أخرى حسب الوثائق الرسمية
        ],
        'deepseek' => [
            'deepseek-chat' => 'DeepSeek Chat',
            'deepseek-coder' => 'DeepSeek Coder',
            'deepseek-reasoner' => 'DeepSeek Reasoner',
            'deepseek-v2' => 'DeepSeek V2',
            'deepseek-v2.5' => 'DeepSeek V2.5',
            'deepseek-v3' => 'DeepSeek V3',
            // إضافة موديلات أخرى حسب الوثائق الرسمية
        ],
    ];

    /**
     * العلاقة مع منشئ الموديل
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * العلاقة مع المحادثات
     */
    public function conversations()
    {
        return $this->hasMany(AIConversation::class, 'ai_model_id');
    }

    /**
     * العلاقة مع طلبات توليد الأسئلة
     */
    public function questionGenerations()
    {
        return $this->hasMany(AIQuestionGeneration::class, 'ai_model_id');
    }

    /**
     * العلاقة مع حلول الأسئلة
     */
    public function solutions()
    {
        return $this->hasMany(AIQuestionSolution::class, 'ai_model_id');
    }

    /**
     * نطاق الموديلات النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * نطاق الموديل الافتراضي
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * نطاق الموديلات حسب المزود
     */
    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * نطاق الموديلات حسب القدرة
     */
    public function scopeByCapability($query, string $capability)
    {
        return $query->where(function($q) use ($capability) {
            $q->whereJsonContains('capabilities', $capability)
              ->orWhereJsonContains('capabilities', 'all');
        });
    }

    /**
     * التحقق من قدرة الموديل على معالجة مهمة معينة
     */
    public function canHandle(string $capability): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $capabilities = $this->capabilities ?? [];
        return in_array($capability, $capabilities) || in_array('all', $capabilities);
    }

    /**
     * دعم تحليل الصور (رؤية) — لا يُستدل عليه من 'all' كباقي القدرات، لأن معظم
     * الموديلات النشطة حالياً مُعلَّمة 'all' دون أن تكون فعلياً متعددة الوسائط.
     * يجب تفعيل 'vision' صراحةً لكل موديل يدعمها بالفعل.
     */
    public function supportsVision(): bool
    {
        return in_array('vision', $this->capabilities ?? [], true);
    }

    /**
     * حساب التكلفة
     */
    public function getCost(int $tokens): float
    {
        if (!$this->cost_per_1k_tokens) {
            return 0;
        }

        return ($tokens / 1000) * $this->cost_per_1k_tokens;
    }

    /**
     * الحصول على API Key (مفكوك)
     */
    public function getDecryptedApiKey(): ?string
    {
        if (!$this->api_key) {
            \Log::debug('API Key is empty in database', ['model_id' => $this->id]);
            return null;
        }

        try {
            $decrypted = Crypt::decryptString($this->api_key);
            \Log::debug('API Key decrypted successfully', ['model_id' => $this->id, 'key_length' => strlen($decrypted)]);
            return $decrypted;
        } catch (\Exception $e) {
            \Log::error('Failed to decrypt API Key', [
                'model_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * حفظ API Key (مشفر)
     */
    public function setApiKeyAttribute($value)
    {
        if (!empty($value) && trim($value) !== '') {
            // إذا كانت القيمة غير فارغة، قم بتشفيرها
            $encrypted = Crypt::encryptString(trim($value));
            $this->attributes['api_key'] = $encrypted;
            \Log::debug('API Key encrypted and set', [
                'model_id' => $this->id ?? 'new',
                'encrypted_length' => strlen($encrypted)
            ]);
        } else {
            // إذا كانت فارغة، لا تقم بتحديث القيمة (احتفظ بالقيمة الحالية)
            unset($this->attributes['api_key']);
            \Log::debug('API Key not updated (empty value)', ['model_id' => $this->id ?? 'new']);
        }
    }

    /**
     * اختبار الاتصال
     */
    public function testConnection(): array
    {
        // سيتم تنفيذ هذا في Service
        return [
            'success' => false,
            'message' => 'Not implemented yet',
        ];
    }
}
