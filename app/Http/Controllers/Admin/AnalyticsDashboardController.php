<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Services\ChartDataService;
use Illuminate\Http\Request;

class AnalyticsDashboardController extends Controller
{
    protected AnalyticsService $analyticsService;
    protected ChartDataService $chartDataService;

    public function __construct(AnalyticsService $analyticsService, ChartDataService $chartDataService)
    {
        $this->middleware(['auth', 'check.user.active', 'admin']);
        $this->analyticsService = $analyticsService;
        $this->chartDataService = $chartDataService;
    }

    /**
     * لوحة تحكم Analytics الموحدة
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');

        // إحصائيات النظام العامة
        $systemAnalytics = $this->analyticsService->getSystemAnalytics($period);
        $systemUsageChart = $this->chartDataService->getSystemUsageChart($period);

        // أفضل الطلاب نشاطاً
        $topActiveUsers = $systemAnalytics['most_active_users'] ?? [];

        return view('admin.pages.analytics.dashboard', compact(
            'period',
            'systemAnalytics',
            'systemUsageChart',
            'topActiveUsers'
        ));
    }
}
