<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AIConversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ai_conversations';

    protected $fillable = [
        'user_id',
        'subject_id',
        'lesson_id',
        'conversation_type',
        'title',
        'ai_model_id',
        'message_count',
        'last_message_at',
        'is_active',
        'settings',
        'context_history',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'is_active' => 'boolean',
        'message_count' => 'integer',
        'settings' => 'array',
        'context_history' => 'array',
    ];

    /**
     * أنواع المحادثات
     */
    public const TYPES = [
        'general' => 'عام',
        'subject' => 'خاص بمادة',
        'lesson' => 'خاص بدرس',
    ];

    /**
     * العلاقة مع المستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * العلاقة مع المادة
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * العلاقة مع الدرس
     */
    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    /**
     * العلاقة مع الموديل
     */
    public function model()
    {
        return $this->belongsTo(AIModel::class, 'ai_model_id');
    }

    /**
     * العلاقة مع الرسائل
     */
    public function messages()
    {
        return $this->hasMany(AIMessage::class, 'conversation_id')->orderBy('created_at');
    }

    /**
     * نطاق المحادثات النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * نطاق محادثات مستخدم معين
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * نطاق محادثات مادة معينة
     */
    public function scopeForSubject($query, int $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * نطاق محادثات درس معين
     */
    public function scopeForLesson($query, int $lessonId)
    {
        return $query->where('lesson_id', $lessonId);
    }

    /**
     * إضافة رسالة
     */
    public function addMessage(string $role, string $content, array $metadata = []): AIMessage
    {
        $message = $this->messages()->create([
            'role' => $role,
            'content' => $content,
            'metadata' => $metadata,
        ]);

        $this->increment('message_count');
        $this->update(['last_message_at' => now()]);

        return $message;
    }

    /**
     * الحصول على السياق للمحادثة
     */
    public function getContext(): string
    {
        $context = [];

        if ($this->subject) {
            $context[] = "المادة: {$this->subject->name}";
            if ($this->subject->description) {
                $context[] = "وصف المادة: {$this->subject->description}";
            }
        }

        if ($this->lesson) {
            $context[] = "الدرس: {$this->lesson->title}";
            if ($this->lesson->description) {
                $context[] = "وصف الدرس: {$this->lesson->description}";
            }
        }

        return implode("\n", $context);
    }

    /**
     * الحصول على عدد الـ Tokens
     */
    public function getTokenCount(): int
    {
        return $this->messages()->sum('tokens_used') ?? 0;
    }

    /**
     * تحديث السياق
     */
    public function updateContext(?Subject $subject = null, ?Lesson $lesson = null): void
    {
        $oldContext = [
            'subject_id' => $this->subject_id,
            'lesson_id' => $this->lesson_id,
            'conversation_type' => $this->conversation_type,
            'changed_at' => now()->toIso8601String(),
        ];

        // تحديث السياق
        $this->subject_id = $subject?->id;
        $this->lesson_id = $lesson?->id;

        // تحديث نوع المحادثة
        if ($lesson) {
            $this->conversation_type = 'lesson';
            if (!$this->title || str_starts_with($this->title, 'محادثة حول:')) {
                $this->title = "محادثة حول: {$lesson->title}";
            }
        } elseif ($subject) {
            $this->conversation_type = 'subject';
            if (!$this->title || str_starts_with($this->title, 'محادثة حول:')) {
                $this->title = "محادثة حول: {$subject->name}";
            }
        } else {
            $this->conversation_type = 'general';
            if (!$this->title || str_starts_with($this->title, 'محادثة حول:')) {
                $this->title = 'محادثة عامة';
            }
        }

        // حفظ تاريخ التغيير
        $contextHistory = $this->context_history ?? [];
        $contextHistory[] = $oldContext;
        $this->context_history = $contextHistory;

        $this->save();

        // تحديث رسالة النظام - إعادة تحميل العلاقات أولاً
        $this->load(['subject', 'lesson']);
        $systemMessage = $this->messages()->where('role', 'system')->first();
        if ($systemMessage) {
            $promptService = app(\App\Services\AI\AIPromptService::class);
            $newPrompt = $promptService->getChatbotPrompt($this);
            $systemMessage->update(['content' => $newPrompt]);
        }
    }

    /**
     * الحصول على الإعدادات
     */
    public function getSettings(): array
    {
        return $this->settings ?? [];
    }

    /**
     * تحديث الإعدادات
     */
    public function updateSettings(array $settings): void
    {
        $currentSettings = $this->settings ?? [];
        $this->settings = array_merge($currentSettings, $settings);
        $this->save();
    }
}
