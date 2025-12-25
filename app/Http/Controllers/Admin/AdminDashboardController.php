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
    }

    /**
     * لوحة التحكم الرئيسية
     */
    public function dashboard()
    {
        $data = $this->dashboardService->getDashboardData(Auth::id());
        return view('admin.pages.dashboard', $data);
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
