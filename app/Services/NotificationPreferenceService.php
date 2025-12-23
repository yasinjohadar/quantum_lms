<?php

namespace App\Services;

use App\Models\NotificationPreference;
use App\Models\User;

class NotificationPreferenceService
{
    /**
     * الحصول على تفضيلات مستخدم معين مع قيم افتراضية
     */
    public function getUserPreferences(User $user): array
    {
        $defaults = $this->getDefaultTypes();

        $dbPrefs = NotificationPreference::where('user_id', $user->id)
            ->get()
            ->keyBy('type');

        $result = [];
        foreach ($defaults as $type => $default) {
            $pref = $dbPrefs->get($type);
            $result[$type] = [
                'type' => $type,
                'label' => $default['label'],
                'via_database' => $pref->via_database ?? $default['via_database'],
                'via_email' => $pref->via_email ?? $default['via_email'],
                'via_sms' => $pref->via_sms ?? $default['via_sms'],
                'muted' => $pref->muted ?? $default['muted'],
            ];
        }

        return $result;
    }

    /**
     * حفظ تفضيلات المستخدم
     */
    public function saveUserPreferences(User $user, array $data): void
    {
        foreach ($data as $type => $values) {
            NotificationPreference::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'type' => $type,
                ],
                [
                    'via_database' => !empty($values['via_database']),
                    'via_email' => !empty($values['via_email']),
                    'via_sms' => !empty($values['via_sms']),
                    'muted' => !empty($values['muted']),
                ]
            );
        }
    }

    /**
     * التحقق هل مسموح إرسال إشعار من نوع معين لهذا المستخدم
     */
    public function isAllowed(User $user, string $type, string $channel = 'database'): bool
    {
        /** @var NotificationPreference|null $pref */
        $pref = NotificationPreference::where('user_id', $user->id)
            ->where('type', $type)
            ->first();

        if (!$pref) {
            $defaults = $this->getDefaultTypes();
            $default = $defaults[$type] ?? ['muted' => false, 'via_database' => true, 'via_email' => false, 'via_sms' => false];
            if ($default['muted']) {
                return false;
            }
            return $default['via_' . $channel] ?? false;
        }

        if ($pref->muted) {
            return false;
        }

        return $pref->{'via_' . $channel} ?? false;
    }

    /**
     * أنواع الإشعارات الافتراضية
     */
    public function getDefaultTypes(): array
    {
        return [
            'system' => [
                'label' => 'إشعارات النظام العامة',
                'via_database' => true,
                'via_email' => false,
                'via_sms' => false,
                'muted' => false,
            ],
            'assignment' => [
                'label' => 'الواجبات',
                'via_database' => true,
                'via_email' => true,
                'via_sms' => false,
                'muted' => false,
            ],
            'quiz' => [
                'label' => 'الاختبارات',
                'via_database' => true,
                'via_email' => true,
                'via_sms' => false,
                'muted' => false,
            ],
            'library' => [
                'label' => 'المكتبة الرقمية',
                'via_database' => true,
                'via_email' => false,
                'via_sms' => false,
                'muted' => false,
            ],
            'gamification' => [
                'label' => 'نظام التحفيز (نقاط، إنجازات، شارات)',
                'via_database' => true,
                'via_email' => false,
                'via_sms' => false,
                'muted' => false,
            ],
            'calendar' => [
                'label' => 'التقويم والتذكيرات',
                'via_database' => true,
                'via_email' => true,
                'via_sms' => false,
                'muted' => false,
            ],
            'custom' => [
                'label' => 'إشعارات مخصصة من الإدارة/المعلم',
                'via_database' => true,
                'via_email' => false,
                'via_sms' => false,
                'muted' => false,
            ],
        ];
    }
}


