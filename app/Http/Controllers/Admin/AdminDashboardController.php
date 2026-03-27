<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(AdminDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
        $this->middleware(['permission:dashboard-view'])->only(['dashboard', 'widgets', 'saveWidgets']);
    }

    /**
     * لوحة التحكم الرئيسية
     */
    public function dashboard()
    {
        $user = Auth::user();
        $data = $this->dashboardService->getDashboardData(Auth::id());
        $data['greetingUserName'] = $user?->name ?? 'مستخدم';
        $data['greetingPrimaryRoleLabel'] = $user?->primary_role_label ?? 'مستخدم';

        return view('admin.dashboard', $data);
    }

    /**
     * الحصول على الودجت
     */
    public function widgets()
    {
        $widgets = $this->dashboardService->getWidgets(Auth::id());
        return response()->json([
            'widgets' => $widgets,
            'stats' => $this->dashboardService->getQuickStats(),
        ]);
    }

    /**
     * حفظ إعدادات الودجت
     */
    public function saveWidgets(Request $request)
    {
        $request->validate([
            'widgets' => 'required|array',
        ]);

        $this->dashboardService->saveWidgetConfig(Auth::id(), $request->widgets);

        return response()->json(['success' => true]);
    }
}
