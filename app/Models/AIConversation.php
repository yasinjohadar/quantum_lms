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
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'is_active' => 'boolean',
        'message_count' => 'integer',
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
}
