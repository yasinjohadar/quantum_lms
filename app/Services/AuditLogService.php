<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuditLogService
{
    /**
     * أسماء عربية للأحداث والعناصر تُستخدم في بناء نص الإجراء المعروض.
     */
    private const EVENT_LABELS = [
        'created' => 'إنشاء',
        'updated' => 'تعديل',
        'deleted' => 'حذف',
        'force_deleted' => 'حذف نهائي',
        'restored' => 'استعادة',
    ];

    private const SUBJECT_LABELS = [
        'stage' => 'مرحلة',
        'class' => 'صف',
        'subject' => 'مادة',
        'subject_section' => 'قسم',
        'unit' => 'وحدة',
        'lesson' => 'درس',
        'question' => 'سؤال',
        'quiz' => 'اختبار',
    ];

    /**
     * تسجيل حدث إنشاء/تعديل/حذف/استعادة على أحد نماذج المحتوى (المنهج/الأسئلة/الاختبارات).
     */
    /**
     * @param array $context لقطة "الصف/المادة" اللذين ينتمي إليهما العنصر (من CurriculumContextResolver):
     *                       class_id, class_name, curriculum_subject_id, curriculum_subject_name
     */
    public function logModelEvent(
        ?User $user,
        string $event,
        Model $model,
        string $subjectKey,
        array $old = [],
        array $new = [],
        ?\DateTimeInterface $occurredAt = null,
        array $context = []
    ): AuditLog {
        $label = $model->title ?? $model->name ?? ('#' . $model->getKey());

        return AuditLog::create([
            'user_id' => $user?->id,
            'causer_role' => $user?->getRoleNames()->first(),
            'event_type' => "{$subjectKey}.{$event}",
            'action' => $this->buildActionLabel($event, $subjectKey, $label),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'url' => request()?->fullUrl(),
            'subject_type' => $subjectKey,
            'subject_id' => $model->getKey(),
            'subject_label' => $label,
            'class_id' => $context['class_id'] ?? null,
            'class_name' => $context['class_name'] ?? null,
            'curriculum_subject_id' => $context['curriculum_subject_id'] ?? null,
            'curriculum_subject_name' => $context['curriculum_subject_name'] ?? null,
            'old_values' => $old ? $this->truncateValues($old) : null,
            'new_values' => $new ? $this->truncateValues($new) : null,
            'occurred_at' => $occurredAt ?? now(),
        ]);
    }

    private function buildActionLabel(string $event, string $subjectKey, string $label): string
    {
        $eventLabel = self::EVENT_LABELS[$event] ?? $event;
        $subjectLabel = self::SUBJECT_LABELS[$subjectKey] ?? $subjectKey;

        return "{$eventLabel} {$subjectLabel}: {$label}";
    }

    /**
     * تفادي تضخم الجدول بقصّ القيم النصية الطويلة (وصف/محتوى/روابط فيديو...).
     */
    private function truncateValues(array $values): array
    {
        return array_map(
            fn ($value) => is_string($value) ? Str::limit($value, 500) : $value,
            $values
        );
    }
    /**
     * تسجيل حدث عام
     */
    public function log(?User $user, string $eventType, ?string $action = null, array $metadata = [], ?Request $request = null): AuditLog
    {
        $request ??= request();

        return AuditLog::create([
            'user_id' => $user?->id,
            'event_type' => $eventType,
            'action' => $action,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'url' => $request?->fullUrl(),
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);
    }

    /**
     * تسجيل محاولة تسجيل دخول
     */
    public function logLoginAttempt(?User $user, bool $success, array $extra = [], ?Request $request = null): AuditLog
    {
        $event = $success ? 'login_success' : 'login_failed';
        $action = $success ? 'تسجيل دخول ناجح' : 'محاولة تسجيل دخول فاشلة';

        return $this->log($user, $event, $action, $extra, $request);
    }

    /**
     * تسجيل تغيير إعدادات حساسة
     */
    public function logSettingsChange(User $user, string $area, array $changes, ?Request $request = null): AuditLog
    {
        return $this->log(
            $user,
            'settings_changed',
            "تغيير إعدادات ({$area})",
            ['changes' => $changes],
            $request
        );
    }

    /**
     * تسجيل حدث متعلق بالاختبار
     */
    public function logQuizSecurity(User $user, string $eventType, array $data = [], ?Request $request = null): AuditLog
    {
        return $this->log(
            $user,
            $eventType,
            'سلوك متعلق بالاختبار',
            $data,
            $request
        );
    }
}


