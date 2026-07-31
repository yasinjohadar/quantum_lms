<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamificationNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'actor_id',
        'actor_name',
        'actor_role',
        'action_url',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'is_read',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'is_read' => 'boolean',
    ];

    /**
     * أنواع الإشعارات
     */
    public const TYPES = [
        'badge_earned' => 'كسب شارة',
        'achievement_unlocked' => 'فتح إنجاز',
        'level_up' => 'ترقية مستوى',
        'challenge_completed' => 'إكمال تحدٍ',
        'reward_claimed' => 'استبدال مكافأة',
        'leaderboard_update' => 'تحديث لوحة المتصدرين',
        'challenge_reminder' => 'تذكير بتحدي',
        'custom_notification' => 'إشعار مخصص',
        'lesson_review_submitted' => 'درس قيد المراجعة',
        'lesson_review_approved' => 'قبول مراجعة درس',
        'lesson_review_rejected' => 'رفض مراجعة درس',
        'quiz_review_submitted' => 'اختبار قيد المراجعة',
        'quiz_review_approved' => 'قبول مراجعة اختبار',
        'quiz_review_rejected' => 'رفض مراجعة اختبار',
        'lesson_review_submit_ack' => 'تأكيد إرسال درس للمراجعة',
        'quiz_review_submit_ack' => 'تأكيد إرسال اختبار للمراجعة',
        'student_lesson_available' => 'درس متاح للطلاب',
        'student_quiz_available' => 'اختبار متاح للطلاب',
        'staff_review' => 'مراجعة المحتوى',
        'class_enrollment_decision' => 'طلب انضمام صف',
    ];

    /**
     * العلاقات
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * حمولة للبث عبر Echo وواجهة JSON.
     */
    public function toBroadcastPayload(): array
    {
        $data = $this->data ?? [];

        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'data' => $data,
            'actor_id' => $this->actor_id,
            'actor_name' => $this->actor_name,
            'actor_role' => $this->actor_role,
            'action_url' => $this->action_url,
            'icon' => $data['icon'] ?? 'fe fe-bell',
            'color' => $data['color'] ?? 'primary',
            'timestamp' => $this->created_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'is_read' => false,
        ];
    }

    /**
     * Scopes
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Methods
     */
    public function markAsRead(): void
    {
        $this->is_read = true;
        $this->read_at = now();
        $this->save();
    }

    /**
     * Accessors
     */
    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}

