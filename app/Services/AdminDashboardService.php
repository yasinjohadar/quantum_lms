<?php

namespace App\Services;

use App\Models\DashboardWidget;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Subject;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Cache;

class AdminDashboardService
{
    /**
     * الحصول على بيانات لوحة التحكم
     */
    public function getDashboardData($userId = null)
    {
        $cacheKey = 'dashboard_data_' . ($userId ?? 'global');
        
        return Cache::remember($cacheKey, 300, function() use ($userId) {
            return [
                'stats' => $this->getQuickStats(),
                'widgets' => $this->getWidgets($userId),
                'recent_activities' => $this->getRecentActivities(),
            ];
        });
    }

    /**
     * حفظ إعدادات الودجت
     */
    public function saveWidgetConfig($userId, $widgets)
    {
        // حذف الودجت المخصصة الحالية للمستخدم
        DashboardWidget::forUser($userId)
            ->whereNotNull('user_id')
            ->delete();

        // إنشاء الودجت الجديدة
        foreach ($widgets as $widget) {
            DashboardWidget::create([
                'name' => $widget['name'],
                'type' => $widget['type'],
                'position' => $widget['position'] ?? null,
                'config' => $widget['config'] ?? [],
                'user_id' => $userId,
                'is_active' => true,
            ]);
        }

        // مسح الـ cache
        Cache::forget('dashboard_data_' . $userId);

        return true;
    }

    /**
     * الحصول على إعدادات النظام
     */
    public function getSystemSettings($group = null)
    {
        $query = SystemSetting::query();
        
        if ($group) {
            $query->ofGroup($group);
        }

        return $query->get()->mapWithKeys(function($setting) {
            return [$setting->key => $setting->value];
        });
    }

    /**
     * تحديث إعدادات النظام
     */
    public function updateSystemSettings($settings, $group = 'general')
    {
        foreach ($settings as $key => $value) {
            SystemSetting::set($key, $value, 'string', $group);
        }

        // مسح الـ cache
        Cache::forget('system_settings');

        return true;
    }

    /**
     * الحصول على الإحصائيات السريعة
     */
    public function getQuickStats()
    {
        $cacheKey = 'dashboard_quick_stats';
        
        return Cache::remember($cacheKey, 60, function() {
            return [
                'total_students' => User::students()->count(),
                'total_subjects' => Subject::active()->count(),
                'total_lessons' => Lesson::where('is_active', true)->count(),
                'total_quizzes' => Quiz::where('is_active', true)->where('is_published', true)->count(),
                'total_questions' => Question::where('is_active', true)->count(),
                'active_enrollments' => Enrollment::where('status', 'active')->count(),
                'pending_enrollments' => Enrollment::where('status', 'pending')->count(),
            ];
        });
    }

    /**
     * الحصول على الودجت
     */
    public function getWidgets($userId = null)
    {
        $query = DashboardWidget::active();
        
        if ($userId) {
            $query->forUser($userId);
        } else {
            $query->global();
        }

        // ترتيب حسب الموضع
        $widgets = $query->get();
        
        return $widgets->sortBy(function($widget) {
            $position = $widget->position ?? ['row' => 0, 'col' => 0];
            return ($position['row'] ?? 0) * 1000 + ($position['col'] ?? 0);
        })->values();
    }

    /**
     * الحصول على الأنشطة الأخيرة
     */
    protected function getRecentActivities()
    {
        // TODO: تنفيذ الأنشطة الأخيرة من AnalyticsEvent
        return [];
    }
}
