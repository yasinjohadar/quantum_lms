<?php

namespace App\Services;

use App\Models\User;
use App\Models\Subject;
use App\Services\StudentProgressService;
use App\Services\AnalyticsService;
use Carbon\Carbon;

class ChartDataService
{
    protected $progressService;
    protected $analyticsService;

    public function __construct(StudentProgressService $progressService, AnalyticsService $analyticsService)
    {
        $this->progressService = $progressService;
        $this->analyticsService = $analyticsService;
    }

    /**
     * رسم بياني لتقدم الطالب
     */
    public function getStudentProgressChart($userId, $period = 'month')
    {
        try {
            $user = User::findOrFail($userId);
            
            // الحصول على جميع الكورسات المسجلة
            $subjects = $user->subjects()->wherePivot('status', 'active')->get();
            
            if ($subjects->isEmpty()) {
                \Log::info('No subjects found for user: ' . $userId);
                return [
                    'type' => 'bar',
                    'options' => [
                        'chart' => ['type' => 'bar', 'height' => 400],
                        'title' => ['text' => 'تقدم الطالب في الكورسات'],
                        'xaxis' => ['categories' => []],
                        'series' => [
                            [
                                'name' => 'التقدم الإجمالي',
                                'data' => [],
                            ],
                        ],
                    ],
                ];
            }
            
            \Log::info('Found ' . $subjects->count() . ' subjects for user: ' . $userId);
            
            $progressData = [];
            $lessonsData = [];
            $quizzesData = [];
            $questionsData = [];
            $subjectNames = [];
            
            foreach ($subjects as $subject) {
                try {
                    $progress = $this->progressService->calculateSubjectProgress($userId, $subject->id);
                    $progressData[] = round($progress['overall_percentage'] ?? 0, 1);
                    $lessonsData[] = ($progress['lessons_total'] ?? 0) > 0 ? round((($progress['lessons_completed'] ?? 0) / $progress['lessons_total']) * 100, 1) : 0;
                    $quizzesData[] = ($progress['quizzes_total'] ?? 0) > 0 ? round((($progress['quizzes_completed'] ?? 0) / $progress['quizzes_total']) * 100, 1) : 0;
                    $questionsData[] = ($progress['questions_total'] ?? 0) > 0 ? round((($progress['questions_completed'] ?? 0) / $progress['questions_total']) * 100, 1) : 0;
                    $subjectNames[] = $subject->name;
                } catch (\Exception $e) {
                    \Log::warning('Error calculating progress for subject ' . $subject->id . ': ' . $e->getMessage());
                    continue;
                }
            }

            $chartOptions = [
                'chart' => [
                    'type' => 'bar',
                    'height' => 400,
                    'toolbar' => [
                        'show' => true,
                        'tools' => [
                            'download' => true,
                            'selection' => true,
                            'zoom' => true,
                            'zoomin' => true,
                            'zoomout' => true,
                            'pan' => true,
                            'reset' => true
                        ]
                    ]
                ],
                'title' => [
                    'text' => 'تقدم الطالب في الكورسات',
                    'align' => 'right',
                    'style' => [
                        'fontSize' => '18px',
                        'fontWeight' => 'bold'
                    ]
                ],
                'xaxis' => [
                    'categories' => $subjectNames,
                    'labels' => [
                        'style' => [
                            'fontSize' => '12px'
                        ]
                    ]
                ],
                'yaxis' => [
                    'title' => [
                        'text' => 'النسبة المئوية (%)'
                    ],
                    'max' => 100
                ],
                'series' => [
                    [
                        'name' => 'التقدم الإجمالي',
                        'data' => $progressData,
                    ],
                    [
                        'name' => 'الدروس',
                        'data' => $lessonsData,
                    ],
                    [
                        'name' => 'الاختبارات',
                        'data' => $quizzesData,
                    ],
                    [
                        'name' => 'الأسئلة',
                        'data' => $questionsData,
                    ],
                ],
                'colors' => ['#007bff', '#28a745', '#ffc107', '#17a2b8'],
                'plotOptions' => [
                    'bar' => [
                        'horizontal' => false,
                        'columnWidth' => '55%',
                        'borderRadius' => 4,
                        'dataLabels' => [
                            'position' => 'top'
                        ]
                    ]
                ],
                'dataLabels' => [
                    'enabled' => true,
                    'style' => [
                        'fontSize' => '11px',
                        'fontWeight' => 'bold'
                    ]
                ],
                'legend' => [
                    'position' => 'top',
                    'horizontalAlign' => 'right'
                ],
                'tooltip' => [
                    'shared' => true,
                    'intersect' => false
                ]
            ];

            \Log::info('Chart options generated:', [
                'subjects_count' => count($subjectNames),
                'has_series' => !empty($chartOptions['series']),
                'series_count' => count($chartOptions['series'] ?? [])
            ]);

            return [
                'type' => 'bar',
                'options' => $chartOptions,
            ];
        } catch (\Exception $e) {
            \Log::error('Error generating student progress chart: ' . $e->getMessage());
            return [
                'type' => 'bar',
                'options' => [
                    'chart' => ['type' => 'bar', 'height' => 400],
                    'title' => ['text' => 'تقدم الطالب في الكورسات'],
                    'xaxis' => ['categories' => []],
                    'series' => [],
                ],
            ];
        }
    }

    /**
     * رسم بياني لإحصائيات الكورس
     */
    public function getCourseStatisticsChart($subjectId, $type = 'overview')
    {
        $subject = Subject::findOrFail($subjectId);
        
        switch ($type) {
            case 'overview':
                return $this->getCourseOverviewChart($subject);
            case 'students':
                return $this->getCourseStudentsChart($subject);
            case 'performance':
                return $this->getCoursePerformanceChart($subject);
            default:
                return $this->getCourseOverviewChart($subject);
        }
    }

    /**
     * رسم بياني لاستخدام النظام
     */
    public function getSystemUsageChart($period = 'month', $type = 'overview')
    {
        $analytics = $this->analyticsService->getSystemAnalytics($period);
        
        // تحويل daily_active_users إلى صيغة مناسبة للرسم البياني
        $dailyData = $analytics['daily_active_users'] ?? [];
        
        // تحويل Collection إلى array إذا لزم الأمر
        if ($dailyData instanceof \Illuminate\Support\Collection) {
            $dailyData = $dailyData->toArray();
        }
        
        // ترتيب البيانات حسب التاريخ
        ksort($dailyData);
        
        $dates = array_keys($dailyData);
        $values = array_values($dailyData);
        
        // تحويل التواريخ إلى صيغة أكثر قابلية للقراءة
        $formattedDates = array_map(function($date) {
            try {
                return \Carbon\Carbon::parse($date)->format('d/m/Y');
            } catch (\Exception $e) {
                return $date;
            }
        }, $dates);
        
        return [
            'type' => 'area',
            'options' => [
                'chart' => [
                    'type' => 'area',
                    'height' => 350,
                    'toolbar' => [
                        'show' => true
                    ],
                    'zoom' => [
                        'enabled' => true
                    ]
                ],
                'title' => [
                    'text' => 'استخدام النظام - المستخدمون النشطون يومياً',
                    'align' => 'right',
                    'style' => [
                        'fontSize' => '16px',
                        'fontWeight' => 'bold'
                    ]
                ],
                'xaxis' => [
                    'categories' => $formattedDates,
                    'labels' => [
                        'rotate' => -45,
                        'style' => [
                            'fontSize' => '12px'
                        ]
                    ]
                ],
                'yaxis' => [
                    'title' => [
                        'text' => 'عدد المستخدمين'
                    ]
                ],
                'series' => [
                    [
                        'name' => 'المستخدمون النشطون',
                        'data' => $values,
                    ],
                ],
                'stroke' => [
                    'curve' => 'smooth',
                    'width' => 2
                ],
                'fill' => [
                    'type' => 'gradient',
                    'gradient' => [
                        'shadeIntensity' => 1,
                        'opacityFrom' => 0.7,
                        'opacityTo' => 0.3,
                        'stops' => [0, 90, 100]
                    ]
                ],
                'colors' => ['#3b82f6'],
                'tooltip' => [
                    'enabled' => true,
                    'y' => [
                        'formatter' => function($val) {
                            return $val + ' مستخدم';
                        }
                    ]
                ],
                'dataLabels' => [
                    'enabled' => false
                ],
            ],
        ];
    }

    /**
     * رسم بياني للمقارنة
     */
    public function getComparisonChart($type, $params)
    {
        switch ($type) {
            case 'students':
                return $this->getStudentsComparisonChart($params);
            case 'courses':
                return $this->getCoursesComparisonChart($params);
            default:
                throw new \Exception('نوع المقارنة غير معروف');
        }
    }

    /**
     * Helper Methods
     */
    protected function getDateRange($period)
    {
        $end = Carbon::now();
        
        switch ($period) {
            case 'week':
                $start = $end->copy()->subWeek();
                break;
            case 'month':
                $start = $end->copy()->subMonth();
                break;
            case 'year':
                $start = $end->copy()->subYear();
                break;
            default:
                $start = $end->copy()->subMonth();
        }

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    protected function getCourseOverviewChart($subject)
    {
        $students = $subject->students()->wherePivot('status', 'active')->get();
        
        $progressData = [];
        $studentNames = [];
        foreach ($students as $student) {
            $progress = $this->progressService->calculateSubjectProgress($student->id, $subject->id);
            $progressData[] = round($progress['overall_percentage'], 1);
            $studentNames[] = $student->name;
        }

        return [
            'type' => 'bar',
            'options' => [
                'chart' => [
                    'type' => 'bar',
                    'height' => 350,
                ],
                'title' => [
                    'text' => 'نظرة عامة على الكورس',
                ],
                'xaxis' => [
                    'categories' => $studentNames,
                ],
                'series' => [
                    [
                        'name' => 'التقدم (%)',
                        'data' => $progressData,
                    ],
                ],
            ],
        ];
    }

    protected function getCourseStudentsChart($subject)
    {
        // TODO: تنفيذ رسم بياني للطلاب
        return [];
    }

    protected function getCoursePerformanceChart($subject)
    {
        // TODO: تنفيذ رسم بياني للأداء
        return [];
    }

    protected function getStudentsComparisonChart($params)
    {
        // TODO: تنفيذ مقارنة الطلاب
        return [];
    }

    protected function getCoursesComparisonChart($params)
    {
        // TODO: تنفيذ مقارنة الكورسات
        return [];
    }
}
