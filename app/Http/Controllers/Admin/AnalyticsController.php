<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Services\ChartDataService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    protected $analyticsService;
    protected $chartDataService;

    public function __construct(AnalyticsService $analyticsService, ChartDataService $chartDataService)
    {
        $this->analyticsService = $analyticsService;
        $this->chartDataService = $chartDataService;
    }

    /**
     * تحليلات الطالب (API)
     */
    public function student($userId)
    {
        $period = request('period', 'month');
        $analytics = $this->analyticsService->getStudentAnalytics($userId, $period);
        $chart = $this->chartDataService->getStudentProgressChart($userId, $period);

        return response()->json([
            'analytics' => $analytics,
            'chart' => $chart,
        ]);
    }

    /**
     * تحليلات الكورس (API)
     */
    public function course($subjectId)
    {
        $period = request('period', 'month');
        $analytics = $this->analyticsService->getCourseAnalytics($subjectId, $period);
        $chart = $this->chartDataService->getCourseStatisticsChart($subjectId, request('chart_type', 'overview'));

        return response()->json([
            'analytics' => $analytics,
            'chart' => $chart,
        ]);
    }

    /**
     * تحليلات النظام (API)
     */
    public function system()
    {
        $period = request('period', 'month');
        $analytics = $this->analyticsService->getSystemAnalytics($period);
        $chart = $this->chartDataService->getSystemUsageChart($period, request('chart_type', 'overview'));

        return response()->json([
            'analytics' => $analytics,
            'chart' => $chart,
        ]);
    }

    /**
     * تتبع حدث (API)
     */
    public function track(Request $request)
    {
        $request->validate([
            'event_type' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'lesson_id' => 'nullable|exists:lessons,id',
            'quiz_id' => 'nullable|exists:quizzes,id',
            'question_id' => 'nullable|exists:questions,id',
            'metadata' => 'nullable|array',
        ]);

        $event = $this->analyticsService->trackEvent(
            $request->event_type,
            $request->user_id,
            $request->only(['subject_id', 'lesson_id', 'quiz_id', 'question_id', 'metadata'])
        );

        return response()->json([
            'success' => true,
            'event' => $event,
        ]);
    }
}

