<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointTransaction;
use App\Models\Badge;
use App\Models\Achievement;
use App\Models\Level;
use App\Models\User;
use App\Models\SystemSetting;
use App\Services\GamificationService;
use Illuminate\Http\Request;

class GamificationController extends Controller
{
    public function __construct(
        private GamificationService $gamificationService
    ) {}

    /**
     * لوحة التحكم الرئيسية
     */
    public function index()
    {
        $stats = [
            'total_points' => PointTransaction::sum('points'),
            'total_badges' => Badge::active()->count(),
            'total_achievements' => Achievement::active()->count(),
            'total_levels' => Level::count(),
            'total_users_with_points' => PointTransaction::distinct('user_id')->count(),
        ];

        return view('admin.pages.gamification.index', compact('stats'));
    }

    /**
     * إعدادات النظام
     */
    public function settings()
    {
        $settings = SystemSetting::ofGroup('gamification')->get()->keyBy('key');
        
        return view('admin.pages.gamification.settings', [
            'settings' => $settings,
        ]);
    }

    /**
     * حفظ الإعدادات
     */
    public function saveSettings(Request $request)
    {
        $request->validate([
            'points.*' => 'nullable|integer|min:0',
            'badges.*' => 'nullable|boolean',
            'achievements.*' => 'nullable|boolean',
            'levels.*' => 'nullable|boolean',
            'tasks.*' => 'nullable',
            'leaderboard.*' => 'nullable',
            'notifications.*' => 'nullable|boolean',
        ]);

        // حفظ قواعد النقاط
        foreach ($request->input('points', []) as $key => $value) {
            SystemSetting::set("gamification_points_{$key}", $value ?? 0, 'integer', 'gamification');
        }

        // حفظ إعدادات الشارات
        foreach ($request->input('badges', []) as $key => $value) {
            SystemSetting::set("gamification_badges_{$key}", $value ? 'true' : 'false', 'boolean', 'gamification');
        }

        // حفظ إعدادات الإنجازات
        foreach ($request->input('achievements', []) as $key => $value) {
            SystemSetting::set("gamification_achievements_{$key}", $value ? 'true' : 'false', 'boolean', 'gamification');
        }

        // حفظ إعدادات المستويات
        foreach ($request->input('levels', []) as $key => $value) {
            SystemSetting::set("gamification_levels_{$key}", $value ? 'true' : 'false', 'boolean', 'gamification');
        }

        // حفظ إعدادات المهام
        foreach ($request->input('tasks', []) as $key => $value) {
            $type = in_array($key, ['daily_reset_time']) ? 'string' : 'integer';
            SystemSetting::set("gamification_tasks_{$key}", $value, $type, 'gamification');
        }

        // حفظ إعدادات لوحة المتصدرين
        foreach ($request->input('leaderboard', []) as $key => $value) {
            $type = in_array($key, ['auto_refresh']) ? 'boolean' : 'integer';
            SystemSetting::set("gamification_leaderboard_{$key}", $value, $type, 'gamification');
        }

        // حفظ إعدادات الإشعارات
        foreach ($request->input('notifications', []) as $key => $value) {
            SystemSetting::set("gamification_notifications_{$key}", $value ? 'true' : 'false', 'boolean', 'gamification');
        }

        return redirect()->back()->with('success', 'تم حفظ الإعدادات بنجاح');
    }

    /**
     * إعادة تعيين الإعدادات
     */
    public function resetSettings()
    {
        \Artisan::call('db:seed', ['--class' => 'GamificationSettingsSeeder']);
        
        return redirect()->back()->with('success', 'تم إعادة تعيين الإعدادات إلى القيم الافتراضية');
    }

    /**
     * قواعد النقاط والشارات
     */
    public function rules()
    {
        return view('admin.pages.gamification.rules');
    }
}

