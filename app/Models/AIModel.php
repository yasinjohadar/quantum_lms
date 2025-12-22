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
        'local' => 'Local LLM (Ollama)',
        'custom' => 'Custom Provider',
    ];

    /**
     * القدرات المدعومة
     */
    public const CAPABILITIES = [
        'chat' => 'محادثة',
        'question_generation' => 'توليد أسئلة',
        'question_solving' => 'حل أسئلة',
        'all' => 'جميع القدرات',
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
            return null;
        }

        try {
            return Crypt::decryptString($this->api_key);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * حفظ API Key (مشفر)
     */
    public function setApiKeyAttribute($value)
    {
        if ($value) {
            $this->attributes['api_key'] = Crypt::encryptString($value);
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
