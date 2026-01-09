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

            // جمع بيانات إضافية
            $quizzes = $this->getStudentQuizzes($userId);
            $assignments = $this->getStudentAssignments($userId);
            $grades = $this->getStudentGrades($userId);
            $attendance = $this->getStudentAttendance($userId);

            $data = [
                'student' => $student,
                'progress' => $progress ?? [],
                'analytics' => $analytics,
                'charts' => $charts,
                'quizzes' => $quizzes,
                'assignments' => $assignments,
                'grades' => $grades,
                'attendance' => $attendance,
            ];

            return $data;
        } catch (\Exception $e) {
            \Log::error('Error collecting student data: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * الحصول على جميع الاختبارات مع النتائج للطالب
     */
    public function getStudentQuizzes($userId)
    {
        try {
            $attempts = \App\Models\QuizAttempt::where('user_id', $userId)
                ->with(['quiz' => function($query) {
                    $query->with(['unit.section.subject']);
                }])
                ->whereIn('status', ['completed', 'timed_out'])
                ->orderBy('finished_at', 'desc')
                ->get();

            $quizzes = [];
            foreach ($attempts as $attempt) {
                if ($attempt->quiz) {
                    $quizzes[] = [
                        'quiz' => $attempt->quiz,
                        'attempt' => $attempt,
                        'score' => $attempt->score,
                        'max_score' => $attempt->max_score,
                        'percentage' => $attempt->percentage,
                        'passed' => $attempt->passed,
                        'finished_at' => $attempt->finished_at,
                        'subject' => $attempt->quiz->unit->section->subject ?? null,
                    ];
                }
            }

            // إحصائيات الاختبارات
            $totalQuizzes = count($quizzes);
            $passedQuizzes = collect($quizzes)->where('passed', true)->count();
            $averageScore = $totalQuizzes > 0 ? collect($quizzes)->avg('percentage') : 0;
            $highestScore = $totalQuizzes > 0 ? collect($quizzes)->max('percentage') : 0;
            $lowestScore = $totalQuizzes > 0 ? collect($quizzes)->min('percentage') : 0;

            return [
                'list' => $quizzes,
                'statistics' => [
                    'total' => $totalQuizzes,
                    'passed' => $passedQuizzes,
                    'failed' => $totalQuizzes - $passedQuizzes,
                    'average_score' => round($averageScore, 2),
                    'highest_score' => round($highestScore, 2),
                    'lowest_score' => round($lowestScore, 2),
                    'pass_rate' => $totalQuizzes > 0 ? round(($passedQuizzes / $totalQuizzes) * 100, 2) : 0,
                ],
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting student quizzes: ' . $e->getMessage());
            return [
                'list' => [],
                'statistics' => [
                    'total' => 0,
                    'passed' => 0,
                    'failed' => 0,
                    'average_score' => 0,
                    'highest_score' => 0,
                    'lowest_score' => 0,
                    'pass_rate' => 0,
                ],
            ];
        }
    }

    /**
     * الحصول على جميع الواجبات مع الحالة للطالب
     */
    public function getStudentAssignments($userId)
    {
        try {
            $submissions = \App\Models\AssignmentSubmission::where('user_id', $userId)
                ->with(['assignment' => function($query) {
                    $query->with(['subject', 'unit.section.subject']);
                }, 'grade'])
                ->orderBy('submitted_at', 'desc')
                ->get();

            $assignments = [];
            foreach ($submissions as $submission) {
                if ($submission->assignment) {
                    $assignments[] = [
                        'assignment' => $submission->assignment,
                        'submission' => $submission,
                        'grade' => $submission->grade,
                        'status' => $submission->status,
                        'submitted_at' => $submission->submitted_at,
                        'graded_at' => $submission->grade ? $submission->grade->graded_at : null,
                        'score' => $submission->grade ? $submission->grade->score : null,
                        'max_score' => $submission->grade ? $submission->grade->max_score : $submission->assignment->total_points,
                        'subject' => $submission->assignment->subject ?? $submission->assignment->unit->section->subject ?? null,
                    ];
                }
            }

            // إحصائيات الواجبات
            $totalAssignments = count($assignments);
            $submittedAssignments = collect($assignments)->where('status', 'submitted')->count();
            $gradedAssignments = collect($assignments)->whereNotNull('grade')->count();
            $averageScore = $gradedAssignments > 0 
                ? collect($assignments)->whereNotNull('grade')->avg(function($item) {
                    return $item['max_score'] > 0 ? ($item['score'] / $item['max_score']) * 100 : 0;
                }) 
                : 0;

            return [
                'list' => $assignments,
                'statistics' => [
                    'total' => $totalAssignments,
                    'submitted' => $submittedAssignments,
                    'graded' => $gradedAssignments,
                    'pending' => $totalAssignments - $submittedAssignments,
                    'average_score' => round($averageScore, 2),
                ],
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting student assignments: ' . $e->getMessage());
            return [
                'list' => [],
                'statistics' => [
                    'total' => 0,
                    'submitted' => 0,
                    'graded' => 0,
                    'pending' => 0,
                    'average_score' => 0,
                ],
            ];
        }
    }

    /**
     * الحصول على الدرجات والإحصائيات للطالب
     */
    public function getStudentGrades($userId)
    {
        try {
            $quizzes = $this->getStudentQuizzes($userId);
            $assignments = $this->getStudentAssignments($userId);

            $allScores = [];
            
            // إضافة درجات الاختبارات
            foreach ($quizzes['list'] as $quiz) {
                if ($quiz['percentage'] !== null) {
                    $allScores[] = $quiz['percentage'];
                }
            }

            // إضافة درجات الواجبات
            foreach ($assignments['list'] as $assignment) {
                if ($assignment['grade'] && $assignment['max_score'] > 0) {
                    $percentage = ($assignment['score'] / $assignment['max_score']) * 100;
                    $allScores[] = $percentage;
                }
            }

            $totalScores = count($allScores);
            $averageGrade = $totalScores > 0 ? array_sum($allScores) / $totalScores : 0;
            $highestGrade = $totalScores > 0 ? max($allScores) : 0;
            $lowestGrade = $totalScores > 0 ? min($allScores) : 0;

            // توزيع الدرجات
            $gradeDistribution = [
                'excellent' => 0, // 90-100
                'very_good' => 0, // 80-89
                'good' => 0, // 70-79
                'acceptable' => 0, // 60-69
                'fail' => 0, // <60
            ];

            foreach ($allScores as $score) {
                if ($score >= 90) {
                    $gradeDistribution['excellent']++;
                } elseif ($score >= 80) {
                    $gradeDistribution['very_good']++;
                } elseif ($score >= 70) {
                    $gradeDistribution['good']++;
                } elseif ($score >= 60) {
                    $gradeDistribution['acceptable']++;
                } else {
                    $gradeDistribution['fail']++;
                }
            }

            return [
                'average' => round($averageGrade, 2),
                'highest' => round($highestGrade, 2),
                'lowest' => round($lowestGrade, 2),
                'total_scores' => $totalScores,
                'distribution' => $gradeDistribution,
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting student grades: ' . $e->getMessage());
            return [
                'average' => 0,
                'highest' => 0,
                'lowest' => 0,
                'total_scores' => 0,
                'distribution' => [
                    'excellent' => 0,
                    'very_good' => 0,
                    'good' => 0,
                    'acceptable' => 0,
                    'fail' => 0,
                ],
            ];
        }
    }

    /**
     * الحصول على بيانات الحضور للطالب
     */
    public function getStudentAttendance($userId)
    {
        try {
            if (!class_exists(\App\Models\AttendanceLog::class)) {
                return [
                    'total_sessions' => 0,
                    'attended_sessions' => 0,
                    'absent_sessions' => 0,
                    'attendance_rate' => 0,
                    'recent_logs' => [],
                ];
            }

            $logs = \App\Models\AttendanceLog::where('user_id', $userId)
                ->orderBy('attendance_date', 'desc')
                ->limit(30)
                ->get();

            $totalSessions = $logs->count();
            $attendedSessions = $logs->where('status', 'present')->count();
            $absentSessions = $logs->where('status', 'absent')->count();
            $attendanceRate = $totalSessions > 0 ? ($attendedSessions / $totalSessions) * 100 : 0;

            return [
                'total_sessions' => $totalSessions,
                'attended_sessions' => $attendedSessions,
                'absent_sessions' => $absentSessions,
                'attendance_rate' => round($attendanceRate, 2),
                'recent_logs' => $logs->take(10)->values(),
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting student attendance: ' . $e->getMessage());
            return [
                'total_sessions' => 0,
                'attended_sessions' => 0,
                'absent_sessions' => 0,
                'attendance_rate' => 0,
                'recent_logs' => [],
            ];
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

    /**
     * جمع بيانات الطالب مباشرة بدون template (لصفحة index)
     */
    public function collectStudentDataDirectly($params)
    {
        return $this->collectStudentData($params);
    }
}
