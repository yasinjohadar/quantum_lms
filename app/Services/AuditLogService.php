<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogService
{
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


