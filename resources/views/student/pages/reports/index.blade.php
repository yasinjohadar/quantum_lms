@extends('student.layouts.master')

@section('page-title')
    تقاريري الشاملة
@stop

@push('styles')
    @include('student.partials.dashboard-widget-styles')
    @include('student.pages.lessons.partials.subject-content-breadcrumb-styles')
    @include('student.pages.progress.partials.progress-page-styles')
    @include('student.pages.reports.partials.reports-page-styles')
@endpush

@section('content')
<div class="main-content app-content">
    <div class="container-fluid pt-3">
        <nav class="student-content-breadcrumb mb-3" aria-label="مسار التنقل">
            <ol class="student-content-breadcrumb__trail">
                <li class="student-content-breadcrumb__item">
                    <a href="{{ route('student.dashboard') }}" class="student-content-breadcrumb__link">
                        <i class="bi bi-house-door-fill"></i>
                        <span>الرئيسية</span>
                    </a>
                </li>
                <li class="student-content-breadcrumb__sep" aria-hidden="true"><i class="bi bi-chevron-left"></i></li>
                <li class="student-content-breadcrumb__item" aria-current="page">
                    <span class="student-content-breadcrumb__current">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                        <span>تقاريري</span>
                    </span>
                </li>
            </ol>
            <h1 class="student-content-breadcrumb__heading">
                <i class="bi bi-file-earmark-bar-graph me-2 text-warning"></i>تقاريري الشاملة
            </h1>
            <p class="student-content-breadcrumb__meta mb-0">عرض شامل لتقدمك الدراسي وإحصائياتك</p>
        </nav>

        <div class="student-reports-toolbar mb-4">
            <select id="periodFilter" class="form-select form-select-sm student-reports-toolbar__period">
                <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>آخر أسبوع</option>
                <option value="month" {{ request('period') == 'month' || !request('period') ? 'selected' : '' }}>آخر شهر</option>
                <option value="quarter" {{ request('period') == 'quarter' ? 'selected' : '' }}>آخر 3 أشهر</option>
                <option value="year" {{ request('period') == 'year' ? 'selected' : '' }}>آخر سنة</option>
            </select>
            <div class="btn-group">
                <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-download me-1"></i>
                    تصدير
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="window.print(); return false;"><i class="bi bi-printer me-2"></i> طباعة</a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportToPDF(); return false;"><i class="bi bi-file-pdf me-2"></i> PDF</a></li>
                </ul>
            </div>
        </div>

        @if(isset($error))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>خطأ!</strong> {{ $error }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @php
            $data = $report['data'] ?? [];
            $student = $data['student'] ?? null;
            $progress = $data['progress'] ?? [];
            $analytics = $data['analytics'] ?? [];
            $charts = $data['charts'] ?? [];
            $quizzes = $data['quizzes'] ?? ['list' => [], 'statistics' => []];
            $assignments = $data['assignments'] ?? ['list' => [], 'statistics' => []];
            $grades = $data['grades'] ?? [];
            $attendance = $data['attendance'] ?? [];

            $totalSubjects = count($progress);
            $totalLessons = collect($progress)->sum(fn ($item) => $item['progress']['lessons_total'] ?? 0);
            $completedLessons = collect($progress)->sum(fn ($item) => $item['progress']['lessons_completed'] ?? 0);
            $lessonPercent = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 1) : 0;
            $totalQuizzes = $quizzes['statistics']['total'] ?? 0;
            $averageGrade = $grades['average'] ?? 0;

            $hasProgressChart = false;
            $progressChartData = null;
            if (isset($charts['progress']) && !empty($charts['progress'])) {
                $chartData = $charts['progress'];
                $chartOptions = $chartData['options'] ?? [];
                $series = $chartOptions['series'] ?? [];
                $categories = $chartOptions['xaxis']['categories'] ?? [];
                if (!empty($series) && count($series) > 0 && !empty($categories)) {
                    $hasProgressChart = true;
                    $progressChartData = [
                        'series' => $series,
                        'categories' => $categories,
                    ];
                }
            }
            if (!$hasProgressChart && count($progress) > 0) {
                $progressChartData = [
                    'series' => [[
                        'name' => 'التقدم الإجمالي',
                        'data' => collect($progress)->map(fn ($item) => round($item['progress']['overall_percentage'] ?? 0, 1))->toArray(),
                    ]],
                    'categories' => collect($progress)->map(fn ($item) => $item['subject']->name ?? 'غير محدد')->toArray(),
                ];
            }
            $hasGradesDistribution = !empty($grades['distribution']) && array_sum($grades['distribution']) > 0;
        @endphp

        @include('student.pages.reports.partials.student-hero', ['student' => $student])

        @if($totalSubjects > 0 || count($quizzes['list']) > 0)
            <div class="row g-2 g-md-3 mb-4 student-reports-stats">
                <div class="col-6 col-xl-3">
                    <div class="dashboard-stat-card dashboard-stat-card--students h-100">
                        <div class="dashboard-stat-card__body">
                            <div class="dashboard-stat-card__content">
                                <div class="dashboard-stat-card__label">المواد المسجلة</div>
                                <div class="dashboard-stat-card__value">{{ number_format($totalSubjects) }}</div>
                                <p class="dashboard-stat-card__meta">مواد نشطة</p>
                            </div>
                            <div class="dashboard-stat-card__icon"><i class="fas fa-book"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="dashboard-stat-card dashboard-stat-card--subjects h-100">
                        <div class="dashboard-stat-card__body">
                            <div class="dashboard-stat-card__content">
                                <div class="dashboard-stat-card__label">الدروس المكتملة</div>
                                <div class="dashboard-stat-card__value">{{ $completedLessons }}/{{ $totalLessons }}</div>
                                <p class="dashboard-stat-card__meta">{{ $lessonPercent }}% من الدروس</p>
                            </div>
                            <div class="dashboard-stat-card__icon"><i class="fas fa-check-circle"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="dashboard-stat-card dashboard-stat-card--quizzes h-100">
                        <div class="dashboard-stat-card__body">
                            <div class="dashboard-stat-card__content">
                                <div class="dashboard-stat-card__label">الاختبارات</div>
                                <div class="dashboard-stat-card__value">{{ number_format($totalQuizzes) }}</div>
                                <p class="dashboard-stat-card__meta">نجح {{ $quizzes['statistics']['passed'] ?? 0 }} | فشل {{ $quizzes['statistics']['failed'] ?? 0 }}</p>
                            </div>
                            <div class="dashboard-stat-card__icon"><i class="fas fa-clipboard-check"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="dashboard-stat-card dashboard-stat-card--enrollments h-100">
                        <div class="dashboard-stat-card__body">
                            <div class="dashboard-stat-card__content">
                                <div class="dashboard-stat-card__label">المتوسط العام</div>
                                <div class="dashboard-stat-card__value">{{ number_format($averageGrade, 1) }}%</div>
                                <p class="dashboard-stat-card__meta">{{ $grades['total_scores'] ?? 0 }} تقييم</p>
                            </div>
                            <div class="dashboard-stat-card__icon"><i class="fas fa-trophy"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            @if($hasProgressChart || (isset($progressChartData) && !empty($progressChartData)) || $hasGradesDistribution)
                <div class="row g-3 mb-4">
                    @if($hasProgressChart || (isset($progressChartData) && !empty($progressChartData)))
                        <div class="{{ $hasGradesDistribution ? 'col-xl-7 col-lg-12' : 'col-12' }}">
                            <div class="card dashboard-panel student-reports-panel h-100">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><i class="fe fe-trending-up me-2"></i>تقدم الطالب في المواد</h5>
                                </div>
                                <div class="card-body pt-2">
                                    <div id="progressChart" class="reports-chart-wrap"></div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if($hasGradesDistribution)
                        <div class="{{ ($hasProgressChart || (isset($progressChartData) && !empty($progressChartData))) ? 'col-xl-5 col-lg-12' : 'col-12' }}">
                            <div class="card dashboard-panel student-reports-panel h-100">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><i class="fe fe-pie-chart me-2"></i>توزيع الدرجات</h5>
                                </div>
                                <div class="card-body pt-2">
                                    <div id="gradesDistributionChart" class="reports-chart-wrap"></div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            @if(count($quizzes['list']) > 0)
                <div class="card dashboard-panel student-reports-panel mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fe fe-bar-chart-2 me-2"></i>درجات الاختبارات</h5>
                    </div>
                    <div class="card-body pt-2">
                        <div id="quizzesScoresChart" class="reports-chart-wrap"></div>
                    </div>
                </div>
            @endif

            @if(count($progress) > 0)
                <div class="card dashboard-panel student-reports-panel mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fe fe-book me-2"></i>التقدم التفصيلي في المواد</h5>
                        <p class="fs-12 text-muted mb-0">نسبة الإنجاز في كل مادة</p>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 student-progress-grid">
                            @foreach($progress as $item)
                                @if($item['subject'] ?? null)
                                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                        @include('student.pages.progress.partials.subject-progress-card', [
                                            'subject' => $item['subject'],
                                            'progress' => $item['progress'] ?? [],
                                            'detailsUrl' => route('student.progress.subject', $item['subject']->id),
                                        ])
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if(count($quizzes['list']) > 0)
                <div class="card dashboard-panel student-reports-panel mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fe fe-clipboard me-2"></i>الاختبارات الأخيرة</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 student-reports-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>الاختبار</th>
                                        <th>المادة</th>
                                        <th>الدرجة</th>
                                        <th>النسبة</th>
                                        <th>الحالة</th>
                                        <th>التاريخ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(array_slice($quizzes['list'], 0, 10) as $quiz)
                                        <tr>
                                            <td><strong>{{ $quiz['quiz']->title ?? 'غير محدد' }}</strong></td>
                                            <td>{{ $quiz['subject']->name ?? 'غير محدد' }}</td>
                                            <td><strong>{{ $quiz['score'] ?? 0 }}/{{ $quiz['max_score'] ?? 0 }}</strong></td>
                                            <td>
                                                <span class="badge bg-{{ ($quiz['percentage'] ?? 0) >= 60 ? 'success' : 'danger' }}">
                                                    {{ number_format($quiz['percentage'] ?? 0, 1) }}%
                                                </span>
                                            </td>
                                            <td>
                                                @if($quiz['passed'] ?? false)
                                                    <span class="badge bg-success-transparent text-success">نجح</span>
                                                @else
                                                    <span class="badge bg-danger-transparent text-danger">فشل</span>
                                                @endif
                                            </td>
                                            <td>{{ $quiz['finished_at'] ? $quiz['finished_at']->format('Y-m-d') : '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            @if(isset($attendance['total_sessions']) && $attendance['total_sessions'] > 0)
                <div class="card dashboard-panel student-reports-panel mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fe fe-calendar me-2"></i>الحضور</h5>
                    </div>
                    <div class="card-body">
                        <div class="student-reports-attendance">
                            <div class="student-reports-attendance__item">
                                <div class="student-reports-attendance__value text-primary">{{ $attendance['attended_sessions'] ?? 0 }}</div>
                                <div class="student-reports-attendance__label">جلسات حضرها</div>
                            </div>
                            <div class="student-reports-attendance__item">
                                <div class="student-reports-attendance__value text-danger">{{ $attendance['absent_sessions'] ?? 0 }}</div>
                                <div class="student-reports-attendance__label">جلسات غاب عنها</div>
                            </div>
                            <div class="student-reports-attendance__item">
                                <div class="student-reports-attendance__value text-success">{{ number_format($attendance['attendance_rate'] ?? 0, 1) }}%</div>
                                <div class="student-reports-attendance__label">نسبة الحضور</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if(isset($analytics) && !empty($analytics))
                <div class="card dashboard-panel student-reports-panel mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fe fe-activity me-2"></i>التحليلات والنشاط</h5>
                    </div>
                    <div class="card-body">
                        <div class="student-reports-analytics mb-3">
                            <div class="student-reports-analytics__item">
                                <i class="bi bi-activity text-primary"></i>
                                <span class="student-reports-analytics__value">{{ $analytics['total_events'] ?? 0 }}</span>
                                <span class="student-reports-analytics__label">إجمالي الأحداث</span>
                            </div>
                            <div class="student-reports-analytics__item">
                                <i class="bi bi-eye text-info"></i>
                                <span class="student-reports-analytics__value">{{ $analytics['lessons_viewed'] ?? 0 }}</span>
                                <span class="student-reports-analytics__label">دروس تم عرضها</span>
                            </div>
                            <div class="student-reports-analytics__item">
                                <i class="bi bi-check-circle text-success"></i>
                                <span class="student-reports-analytics__value">{{ $analytics['quizzes_completed'] ?? 0 }}</span>
                                <span class="student-reports-analytics__label">اختبارات مكتملة</span>
                            </div>
                            <div class="student-reports-analytics__item">
                                <i class="bi bi-calendar-event text-warning"></i>
                                <span class="student-reports-analytics__value fs-6">{{ $analytics['most_active_day'] ?? '—' }}</span>
                                <span class="student-reports-analytics__label">أكثر يوم نشاط</span>
                            </div>
                        </div>
                        @if(isset($analytics['activity_timeline']) && count($analytics['activity_timeline']) > 0)
                            <div id="activityTimelineChart" class="reports-chart-wrap"></div>
                        @endif
                    </div>
                </div>
            @endif
        @else
            <div class="card custom-card student-reports-empty">
                <div class="card-body text-center py-5">
                    <i class="bi bi-file-text fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="mb-2">لا توجد بيانات متاحة</h5>
                    <p class="text-muted mb-0">لا توجد مواد مسجلة أو أنشطة حتى الآن.</p>
                    <a href="{{ route('student.classes') }}" class="btn btn-primary mt-4">
                        <i class="bi bi-plus-circle me-1"></i>
                        تصفح الصفوف والمواد
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@stop

@push('scripts')
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script>
(function() {
    'use strict';
    
    function loadApexCharts(callback) {
        if (typeof ApexCharts !== 'undefined') {
            callback();
            return;
        }
        var script = document.createElement('script');
        script.src = '{{ asset("assets/libs/apexcharts/apexcharts.min.js") }}';
        script.onload = function () { callback(); };
        script.onerror = function () {
            console.error('Failed to load ApexCharts bundle');
        };
        document.head.appendChild(script);
    }
    
    // Function to check if ApexCharts is loaded
    function checkApexCharts() {
        if (typeof ApexCharts === 'undefined') {
            return false;
        }
        return true;
    }

    // Flag to prevent multiple renders
    var chartsRendered = false;
    var chartsInitialized = false;

    // Function to render charts with retry
    function renderCharts() {
        // Prevent multiple renders
        if (chartsRendered) {
            console.log('Charts already rendered, skipping...');
            return;
        }

        loadApexCharts(function() {
            if (!checkApexCharts()) {
                console.error('ApexCharts failed to load');
                return;
            }

            // Double check to prevent race conditions
            if (chartsRendered) {
                console.log('Charts already rendered, skipping...');
                return;
            }

            console.log('ApexCharts is loaded, rendering charts...');

        // Period Filter
        var periodFilter = document.getElementById('periodFilter');
        if (periodFilter) {
            periodFilter.addEventListener('change', function() {
                const period = this.value;
                window.location.href = '{{ route("student.reports.index") }}?period=' + period;
            });
        }

        // Period Filter (only initialize once)
        if (!chartsInitialized) {
            var periodFilter = document.getElementById('periodFilter');
            if (periodFilter) {
                periodFilter.addEventListener('change', function() {
                    const period = this.value;
                    window.location.href = '{{ route("student.reports.index") }}?period=' + period;
                });
            }
            chartsInitialized = true;
        }

        // Progress Chart
        @if(isset($progressChartData) && !empty($progressChartData))
            (function() {
                var chartElement = document.querySelector("#progressChart");
                if (!chartElement) {
                    console.error('Progress chart element not found');
                    return;
                }

                // Check if chart already rendered
                if (chartElement.hasAttribute('data-rendered') || chartElement.querySelector('svg')) {
                    console.log('Progress chart already rendered, skipping...');
                    return;
                }

                try {
                    @php
                        $seriesForJS = $progressChartData['series'] ?? [];
                        $categoriesForJS = $progressChartData['categories'] ?? [];
                    @endphp

                    var categories = @json($categoriesForJS);
                    var series = @json($seriesForJS);

                    console.log('Progress chart data:', { categories: categories, series: series });

                    if (!categories || categories.length === 0 || !series || series.length === 0) {
                        chartElement.innerHTML = '<div class="text-center py-5"><p class="text-muted">لا توجد بيانات متاحة</p></div>';
                        return;
                    }

                    var progressChart = new ApexCharts(chartElement, {
                        chart: { 
                            type: 'bar', 
                            height: 350,
                            toolbar: { show: true }
                        },
                        title: { 
                            text: 'تقدم الطالب في المواد', 
                            align: 'center',
                            style: { fontSize: '16px', fontWeight: 'bold' }
                        },
                        xaxis: { 
                            categories: categories,
                            labels: { style: { fontSize: '12px' } }
                        },
                        yaxis: { 
                            max: 100,
                            title: { text: 'النسبة المئوية (%)' }
                        },
                        series: series,
                        colors: ['#007bff', '#28a745', '#ffc107', '#17a2b8'],
                        plotOptions: { 
                            bar: { 
                                horizontal: false, 
                                columnWidth: '55%',
                                borderRadius: 4
                            } 
                        },
                        dataLabels: { 
                            enabled: true,
                            style: { fontSize: '11px', fontWeight: 'bold' }
                        },
                        legend: { 
                            position: 'top',
                            horizontalAlign: 'right'
                        },
                        tooltip: {
                            shared: true,
                            intersect: false
                        }
                    });
                    
                    progressChart.render();
                    chartElement.setAttribute('data-rendered', 'true');
                    console.log('Progress chart rendered successfully');
                } catch (error) {
                    console.error('Error rendering progress chart:', error);
                    chartElement.innerHTML = '<div class="text-center py-5"><p class="text-danger">خطأ في عرض المخطط: ' + error.message + '</p></div>';
                }
            })();
        @endif

        // Grades Distribution Chart
        @if($hasGradesDistribution)
            (function() {
                var chartElement = document.querySelector("#gradesDistributionChart");
                if (!chartElement) {
                    console.error('Grades distribution chart element not found');
                    return;
                }

                // Check if chart already rendered
                if (chartElement.hasAttribute('data-rendered') || chartElement.querySelector('svg')) {
                    console.log('Grades distribution chart already rendered, skipping...');
                    return;
                }

                try {
                    @php
                        $distribution = $grades['distribution'];
                    @endphp

                    var distribution = @json($distribution);
                    var total = distribution.excellent + distribution.very_good + distribution.good + distribution.acceptable + distribution.fail;

                    console.log('Grades distribution data:', distribution);

                    if (total === 0) {
                        chartElement.innerHTML = '<div class="text-center py-5"><p class="text-muted">لا توجد بيانات متاحة</p></div>';
                        return;
                    }

                    var distributionChart = new ApexCharts(chartElement, {
                        chart: { 
                            type: 'donut', 
                            height: 350,
                            toolbar: { show: true }
                        },
                        title: { 
                            text: 'توزيع الدرجات', 
                            align: 'center',
                            style: { fontSize: '16px', fontWeight: 'bold' }
                        },
                        series: [
                            distribution.excellent || 0,
                            distribution.very_good || 0,
                            distribution.good || 0,
                            distribution.acceptable || 0,
                            distribution.fail || 0
                        ],
                        labels: ['ممتاز (90-100)', 'جيد جداً (80-89)', 'جيد (70-79)', 'مقبول (60-69)', 'راسب (<60)'],
                        colors: ['#28a745', '#20c997', '#ffc107', '#fd7e14', '#dc3545'],
                        legend: { 
                            position: 'bottom',
                            fontSize: '12px'
                        },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '65%',
                                    labels: {
                                        show: true,
                                        total: {
                                            show: true,
                                            label: 'إجمالي التقييمات',
                                            fontSize: '14px',
                                            fontWeight: 'bold'
                                        }
                                    }
                                }
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            style: {
                                fontSize: '12px',
                                fontWeight: 'bold'
                            }
                        }
                    });
                    
                    distributionChart.render();
                    chartElement.setAttribute('data-rendered', 'true');
                    console.log('Grades distribution chart rendered successfully');
                } catch (error) {
                    console.error('Error rendering grades distribution chart:', error);
                    chartElement.innerHTML = '<div class="text-center py-5"><p class="text-danger">خطأ في عرض المخطط: ' + error.message + '</p></div>';
                }
            })();
        @endif

        // Quizzes Scores Chart
        @if(count($quizzes['list']) > 0)
            (function() {
                var chartElement = document.querySelector("#quizzesScoresChart");
                if (!chartElement) {
                    return;
                }

                // Check if chart already rendered
                if (chartElement.hasAttribute('data-rendered') || chartElement.querySelector('svg')) {
                    console.log('Quizzes scores chart already rendered, skipping...');
                    return;
                }

                try {
                    @php
                        $quizScores = collect($quizzes['list'])->take(10)->map(function($q) {
                            return $q['percentage'] ?? 0;
                        })->values()->toArray();
                        $quizNames = collect($quizzes['list'])->take(10)->map(function($q) {
                            return \Illuminate\Support\Str::limit($q['quiz']->title ?? 'غير محدد', 20);
                        })->values()->toArray();
                    @endphp

                    var quizScores = @json($quizScores);
                    var quizNames = @json($quizNames);

                    console.log('Quizzes scores data:', { names: quizNames, scores: quizScores });

                    if (quizNames.length === 0 || quizScores.length === 0) {
                        chartElement.innerHTML = '<div class="text-center py-5"><p class="text-muted">لا توجد بيانات متاحة</p></div>';
                        return;
                    }

                    var quizzesChart = new ApexCharts(chartElement, {
                        chart: { 
                            type: 'line', 
                            height: 300,
                            toolbar: { show: true }
                        },
                        title: { 
                            text: 'درجات الاختبارات الأخيرة', 
                            align: 'center',
                            style: { fontSize: '16px', fontWeight: 'bold' }
                        },
                        xaxis: { 
                            categories: quizNames,
                            labels: { 
                                style: { fontSize: '11px' },
                                rotate: -45,
                                rotateAlways: false
                            }
                        },
                        yaxis: { 
                            max: 100, 
                            min: 0,
                            title: { text: 'النسبة المئوية (%)' }
                        },
                        series: [{
                            name: 'الدرجة',
                            data: quizScores
                        }],
                        colors: ['#007bff'],
                        stroke: { curve: 'smooth', width: 3 },
                        markers: { size: 5, hover: { size: 7 } },
                        dataLabels: { enabled: true },
                        grid: {
                            borderColor: '#e7e7e7',
                            row: {
                                colors: ['#f3f3f3', 'transparent'],
                                opacity: 0.5
                            }
                        }
                    });
                    
                    quizzesChart.render();
                    chartElement.setAttribute('data-rendered', 'true');
                    console.log('Quizzes scores chart rendered successfully');
                } catch (error) {
                    console.error('Error rendering quizzes scores chart:', error);
                    chartElement.innerHTML = '<div class="text-center py-5"><p class="text-danger">خطأ في عرض المخطط: ' + error.message + '</p></div>';
                }
            })();
        @endif

        // Activity Timeline Chart
        @if(isset($analytics['activity_timeline']) && count($analytics['activity_timeline']) > 0)
            (function() {
                var chartElement = document.querySelector("#activityTimelineChart");
                if (!chartElement) {
                    return;
                }

                // Check if chart already rendered
                if (chartElement.hasAttribute('data-rendered') || chartElement.querySelector('svg')) {
                    console.log('Activity timeline chart already rendered, skipping...');
                    return;
                }

                try {
                    @php
                        $timeline = $analytics['activity_timeline'];
                    @endphp

                    var timelineData = @json($timeline);
                    var timelineDates = Object.keys(timelineData);
                    var timelineValues = Object.values(timelineData).map(function(v) {
                        return parseInt(v) || 0;
                    });

                    console.log('Activity timeline data:', { dates: timelineDates, values: timelineValues });

                    if (timelineDates.length === 0 || timelineValues.length === 0) {
                        chartElement.innerHTML = '<div class="text-center py-5"><p class="text-muted">لا توجد بيانات متاحة</p></div>';
                        return;
                    }

                    var timelineChart = new ApexCharts(chartElement, {
                        chart: { 
                            type: 'area', 
                            height: 300,
                            toolbar: { show: true }
                        },
                        title: {
                            text: 'خط زمني للنشاط',
                            align: 'center',
                            style: { fontSize: '16px', fontWeight: 'bold' }
                        },
                        series: [{ 
                            name: 'الأحداث', 
                            data: timelineValues 
                        }],
                        xaxis: { 
                            categories: timelineDates,
                            labels: { style: { fontSize: '11px' } }
                        },
                        yaxis: { 
                            title: { text: 'عدد الأحداث' }
                        },
                        colors: ['#007bff'],
                        dataLabels: { enabled: false },
                        stroke: { curve: 'smooth', width: 2 },
                        fill: { 
                            type: 'gradient', 
                            gradient: { 
                                shadeIntensity: 1, 
                                opacityFrom: 0.7, 
                                opacityTo: 0.9,
                                stops: [0, 90, 100]
                            } 
                        },
                        grid: {
                            borderColor: '#e7e7e7',
                            row: {
                                colors: ['#f3f3f3', 'transparent'],
                                opacity: 0.5
                            }
                        }
                    });
                    
                    timelineChart.render();
                    chartElement.setAttribute('data-rendered', 'true');
                    console.log('Activity timeline chart rendered successfully');
                } catch (error) {
                    console.error('Error rendering activity timeline chart:', error);
                    chartElement.innerHTML = '<div class="text-center py-5"><p class="text-danger">خطأ في عرض المخطط: ' + error.message + '</p></div>';
                }
            })();
        @endif

        // Mark charts as rendered
        chartsRendered = true;
        });
    }

    // Initialize when DOM is ready and ApexCharts is loaded (only once)
    function init() {
        // Use a single initialization approach
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    if (!chartsRendered) {
                        renderCharts();
                    }
                }, 500);
            });
        } else {
            // DOM already loaded
            setTimeout(function() {
                if (!chartsRendered) {
                    renderCharts();
                }
            }, 500);
        }
    }    // Only initialize once
    if (!window.reportsChartsInitialized) {
        window.reportsChartsInitialized = true;
        init();
    }
})();function exportToPDF() {
    window.print();
}
</script>
@endpush
