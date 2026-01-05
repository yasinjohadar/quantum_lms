<?php

namespace App\Services;

use App\Models\ReportTemplate;
use App\Services\ReportGeneratorService;
use App\Services\ChartDataService;
use App\Services\StudentProgressService;
use App\Services\AnalyticsService;
use Illuminate\Support\Facades\Log;

class ReportBuilderService
{
    protected $reportGenerator;
    protected $chartDataService;
    protected $progressService;
    protected $analyticsService;

    public function __construct(
        ReportGeneratorService $reportGenerator, 
        ChartDataService $chartDataService,
        StudentProgressService $progressService,
        AnalyticsService $analyticsService
    ) {
        $this->reportGenerator = $reportGenerator;
        $this->chartDataService = $chartDataService;
        $this->progressService = $progressService;
        $this->analyticsService = $analyticsService;
    }

    /**
     * إنشاء تقرير بناءً على القالب
     */
    public function generateReport($templateId, $params = [])
    {
        $template = ReportTemplate::findOrFail($templateId);

        if (!$template->is_active) {
            throw new \Exception('القالب غير نشط');
        }

        // التحقق من المعاملات
        $this->validateParams($template, $params);

        // جمع البيانات حسب نوع التقرير
        $data = $this->collectData($template, $params);

        return [
            'template' => $template,
            'data' => $data,
            'params' => $params,
        ];
    }

    /**
     * الحصول على القوالب المتاحة
     */
    public function getAvailableTemplates($type = null)
    {
        $query = ReportTemplate::active();

        if ($type) {
            $query->ofType($type);
        }

        return $query->orderBy('name')
                    ->get();
    }

    /**
     * التحقق من المعاملات المطلوبة
     */
    public function validateParams($template, $params)
    {
        $requiredParams = $template->config['required_params'] ?? [];

        foreach ($requiredParams as $param) {
            if (!isset($params[$param])) {
                throw new \Exception("المعامل المطلوب مفقود: {$param}");
            }
        }

        return true;
    }

    /**
     * جمع البيانات حسب نوع التقرير
     */
    protected function collectData($template, $params)
    {
        switch ($template->type) {
            case 'student':
                return $this->collectStudentData($params);
            case 'course':
                return $this->collectCourseData($params);
            case 'system':
                return $this->collectSystemData($params);
            default:
                throw new \Exception('نوع التقرير غير معروف');
        }
    }

    /**
     * جمع بيانات تقرير الطالب
     */
    protected function collectStudentData($params)
    {
        $userId = $params['user_id'] ?? null;
        if (!$userId) {
            throw new \Exception('معرف الطالب مطلوب');
        }

        try {
            $student = \App\Models\User::findOrFail($userId);
            $progress = $this->progressService->getAllStudentProgress($userId);
            
            // محاولة جلب التحليلات
            try {
                $analytics = $this->analyticsService->getStudentAnalytics($userId, $params['period'] ?? 'month');
            } catch (\Exception $e) {
                \Log::warning('Analytics error: ' . $e->getMessage());
                $analytics = [
                    'total_events' => 0,
                    'lessons_viewed' => 0,
                    'quizzes_completed' => 0,
                    'most_active_day' => null,
                    'activity_timeline' => [],
                ];
            }
            
            // إنشاء مخططات إضافية
            try {
                $chartData = $this->chartDataService->getStudentProgressChart($userId, $params['period'] ?? 'month');
                \Log::info('Chart data generated:', [
                    'has_options' => isset($chartData['options']),
                    'has_series' => isset($chartData['options']['series']),
                    'series_count' => isset($chartData['options']['series']) ? count($chartData['options']['series']) : 0,
                ]);
                $charts = [
                    'progress' => $chartData,
                ];
            } catch (\Exception $e) {
                \Log::error('Chart error: ' . $e->getMessage(), [
                    'exception' => $e,
                    'trace' => $e->getTraceAsString()
                ]);
                $charts = [];
            }

            $data = [
                'student' => $student,
                'progress' => $progress ?? [],
                'analytics' => $analytics,
                'charts' => $charts,
            ];

            return $data;
        } catch (\Exception $e) {
            \Log::error('Error collecting student data: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * جمع بيانات تقرير الكورس
     */
    protected function collectCourseData($params)
    {
        $subjectId = $params['subject_id'] ?? null;
        if (!$subjectId) {
            throw new \Exception('معرف الكورس مطلوب');
        }

        $data = [
            'subject' => \App\Models\Subject::findOrFail($subjectId),
            'statistics' => $this->getCourseStatistics($subjectId),
            'analytics' => $this->analyticsService->getCourseAnalytics($subjectId, $params['period'] ?? 'month'),
            'charts' => [
                'statistics' => $this->chartDataService->getCourseStatisticsChart($subjectId, $params['chart_type'] ?? 'overview'),
            ],
        ];

        return $data;
    }

    /**
     * جمع بيانات تقرير النظام
     */
    protected function collectSystemData($params)
    {
        $data = [
            'system' => $this->getSystemStatistics(),
            'analytics' => $this->analyticsService->getSystemAnalytics($params['period'] ?? 'month'),
            'charts' => [
                'usage' => $this->chartDataService->getSystemUsageChart($params['period'] ?? 'month', $params['chart_type'] ?? 'overview'),
            ],
        ];

        return $data;
    }

    /**
     * الحصول على إحصائيات الكورس
     */
    protected function getCourseStatistics($subjectId)
    {
        $subject = \App\Models\Subject::with(['sections.units.lessons', 'sections.units.quizzes'])->findOrFail($subjectId);
        
        $totalStudents = $subject->students()->wherePivot('status', 'active')->count();
        $totalLessons = $subject->sections->sum(function($section) {
            return $section->units->sum(function($unit) {
                return $unit->lessons->where('is_active', true)->count();
            });
        });
        $totalQuizzes = $subject->sections->sum(function($section) {
            return $section->units->sum(function($unit) {
                return $unit->quizzes->where('is_active', true)->where('is_published', true)->count();
            });
        });

        return [
            'total_students' => $totalStudents,
            'total_lessons' => $totalLessons,
            'total_quizzes' => $totalQuizzes,
        ];
    }

    /**
     * الحصول على إحصائيات النظام
     */
    protected function getSystemStatistics()
    {
        return [
            'total_users' => \App\Models\User::count(),
            'total_students' => \App\Models\User::students()->count(),
            'total_subjects' => \App\Models\Subject::active()->count(),
            'total_lessons' => \App\Models\Lesson::where('is_active', true)->count(),
            'total_quizzes' => \App\Models\Quiz::where('is_active', true)->where('is_published', true)->count(),
            'total_questions' => \App\Models\Question::where('is_active', true)->count(),
        ];
    }
}
