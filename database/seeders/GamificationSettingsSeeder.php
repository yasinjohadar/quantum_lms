<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class GamificationSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // قواعد النقاط
            [
                'key' => 'gamification_points_attendance',
                'value' => '10',
                'type' => 'integer',
                'group' => 'gamification',
                'description' => 'نقاط الحضور',
            ],
            [
                'key' => 'gamification_points_lesson_completed',
                'value' => '15',
                'type' => 'integer',
                'group' => 'gamification',
                'description' => 'نقاط إكمال الدرس',
            ],
            [
                'key' => 'gamification_points_quiz_completed',
                'value' => '25',
                'type' => 'integer',
                'group' => 'gamification',
                'description' => 'نقاط إكمال الاختبار',
            ],
            [
                'key' => 'gamification_points_question_answered',
                'value' => '5',
                'type' => 'integer',
                'group' => 'gamification',
                'description' => 'نقاط الإجابة على السؤال',
            ],
            [
                'key' => 'gamification_points_quiz_perfect_score',
                'value' => '50',
                'type' => 'integer',
                'group' => 'gamification',
                'description' => 'نقاط إضافية للحصول على 100% في الاختبار',
            ],
            [
                'key' => 'gamification_points_course_completed',
                'value' => '100',
                'type' => 'integer',
                'group' => 'gamification',
                'description' => 'نقاط إكمال الكورس',
            ],
            
            // إعدادات الشارات
            [
                'key' => 'gamification_badges_auto_award',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'gamification',
                'description' => 'منح الشارات تلقائياً عند استيفاء الشروط',
            ],
            
            // إعدادات الإنجازات
            [
                'key' => 'gamification_achievements_auto_check',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'gamification',
                'description' => 'فحص الإنجازات تلقائياً عند الأحداث',
            ],
            
            // إعدادات المستويات
            [
                'key' => 'gamification_levels_auto_upgrade',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'gamification',
                'description' => 'ترقية المستويات تلقائياً عند الوصول للنقاط المطلوبة',
            ],
            
            // إعدادات المهام
            [
                'key' => 'gamification_tasks_daily_reset_time',
                'value' => '00:00',
                'type' => 'string',
                'group' => 'gamification',
                'description' => 'وقت إعادة تعيين المهام اليومية (HH:MM)',
            ],
            [
                'key' => 'gamification_tasks_weekly_reset_day',
                'value' => '1',
                'type' => 'integer',
                'group' => 'gamification',
                'description' => 'يوم إعادة تعيين المهام الأسبوعية (1=Monday, 7=Sunday)',
            ],
            
            // إعدادات لوحة المتصدرين
            [
                'key' => 'gamification_leaderboard_auto_refresh',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'gamification',
                'description' => 'تحديث لوحة المتصدرين تلقائياً',
            ],
            [
                'key' => 'gamification_leaderboard_refresh_interval',
                'value' => '60',
                'type' => 'integer',
                'group' => 'gamification',
                'description' => 'فترة تحديث لوحة المتصدرين بالدقائق',
            ],
            
            // إعدادات الإشعارات
            [
                'key' => 'gamification_notifications_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'gamification',
                'description' => 'تفعيل إشعارات نظام التحفيز',
            ],
            [
                'key' => 'gamification_notifications_email',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'gamification',
                'description' => 'إرسال إشعارات التحفيز عبر البريد الإلكتروني',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}

