<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardService;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    protected $dashboardService;

    public function __construct(AdminDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * عرض الإعدادات
     */
    public function index(Request $request)
    {
        $group = $request->input('group', 'general');
        $settings = SystemSetting::ofGroup($group)->get();
        $groups = SystemSetting::GROUPS;

        return view('admin.pages.settings.index', compact('settings', 'group', 'groups'));
    }

    /**
     * تحديث الإعدادات
     */
    public function update(Request $request, $group)
    {
        $settings = $request->except(['_token', '_method']);
        
        $this->dashboardService->updateSystemSettings($settings, $group);

        return redirect()->back()->with('success', 'تم تحديث الإعدادات بنجاح');
    }

    /**
     * إعادة تعيين الإعدادات
     */
    public function reset($group)
    {
        // TODO: إعادة تعيين للإعدادات الافتراضية
        return redirect()->back()->with('success', 'تم إعادة تعيين الإعدادات');
    }
}

