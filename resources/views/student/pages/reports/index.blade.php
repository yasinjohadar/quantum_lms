@extends('student.layouts.master')

@section('page-title')
    تقاريري الشاملة
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">تقاريري الشاملة</h4>
                <p class="mb-0 text-muted">عرض شامل لتقدمك الدراسي وإحصائياتك</p>
            </div>
            <div class="d-flex gap-2">
                <select id="periodFilter" class="form-select form-select-sm" style="width: auto;">
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
        </div>
        <!-- End Page Header -->

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
        @endphp

        <!-- Student Information -->
        @if($student)
            <div class="card custom-card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-circle me-2"></i>
                        معلومات الطالب
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-3">
                            @if($student->photo)
                                <img src="{{ asset('storage/' . $student->photo) }}" 
                                     alt="{{ $student->name }}" 
                                     class="rounded-circle mb-2" 
                                     style="width: 100px; height: 100px; object-fit: cover;">
                            @else
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" 
                                     style="width: 100px; height: 100px; font-size: 40px;">
                                    <i class="bi bi-person"></i>
                                </div>
                            @endif
                            <h5 class="mb-0">{{ $student->name }}</h5>
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-envelope text-primary me-2 fs-5"></i>
                                        <div>
                                            <small class="text-muted d-block">البريد الإلكتروني</small>
                                            <strong>{{ $student->email }}</strong>
                                        </div>
                                    </div>
                                </div>
                                @if($student->phone)
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-phone text-primary me-2 fs-5"></i>
                                            <div>
                                                <small class="text-muted d-block">الهاتف</small>
                                                <strong>{{ $student->phone }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-calendar text-primary me-2 fs-5"></i>
                                        <div>
                                            <small class="text-muted d-block">تاريخ التقرير</small>
                                            <strong>{{ now()->format('Y-m-d H:i') }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="row mb-4">
            @php
                $totalSubjects = count($progress);
                $totalLessons = collect($progress)->sum(function($item) {
                    return $item['progress']['lessons_total'] ?? 0;
                });
                $completedLessons = collect($progress)->sum(function($item) {
                    return $item['progress']['lessons_completed'] ?? 0;
                });
                $totalQuizzes = $quizzes['statistics']['total'] ?? 0;
                $totalAssignments = $assignments['statistics']['total'] ?? 0;
                $averageGrade = $grades['average'] ?? 0;
            @endphp

            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="card border-primary h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-book text-primary fs-1 mb-2"></i>
                        <h3 class="mb-1 text-primary">{{ $totalSubjects }}</h3>
                        <p class="mb-0 text-muted">المواد المسجلة</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="card border-success h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-check-circle text-success fs-1 mb-2"></i>
                        <h3 class="mb-1 text-success">{{ $completedLessons }}/{{ $totalLessons }}</h3>
                        <p class="mb-0 text-muted">الدروس المكتملة</p>
                        <div class="progress mt-2" style="height: 8px;">
                            <div class="progress-bar bg-success" 
                                 style="width: {{ $totalLessons > 0 ? ($completedLessons / $totalLessons) * 100 : 0 }}%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="card border-info h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-clipboard-check text-info fs-1 mb-2"></i>
                        <h3 class="mb-1 text-info">{{ $totalQuizzes }}</h3>
                        <p class="mb-0 text-muted">الاختبارات المكتملة</p>
                        @if($totalQuizzes > 0)
                            <small class="text-muted">
                                نجح: {{ $quizzes['statistics']['passed'] ?? 0 }} | 
                                فشل: {{ $quizzes['statistics']['failed'] ?? 0 }}
                            </small>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="card border-warning h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-trophy text-warning fs-1 mb-2"></i>
                        <h3 class="mb-1 text-warning">{{ number_format($averageGrade, 1) }}%</h3>
                        <p class="mb-0 text-muted">المتوسط العام</p>
                        <small class="text-muted">{{ $grades['total_scores'] ?? 0 }} تقييم</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <!-- Progress Chart -->
            @php
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
                
                // إذا لم تكن هناك بيانات من ChartDataService، أنشئها من $progress
                if (!$hasProgressChart && count($progress) > 0) {
                    $progressChartData = [
                        'series' => [[
                            'name' => 'التقدم الإجمالي',
                            'data' => collect($progress)->map(function($item) {
                                return round($item['progress']['overall_percentage'] ?? 0, 1);
                            })->toArray(),
                        ]],
                        'categories' => collect($progress)->map(function($item) {
                            return $item['subject']->name ?? 'غير محدد';
                        })->toArray(),
                    ];
                }
            @endphp
            @if($hasProgressChart || (isset($progressChartData) && !empty($progressChartData)))
                <div class="col-xl-6 col-lg-12 mb-4">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-graph-up me-2"></i>
                                تقدم الطالب في المواد
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="progressChart" style="height: 350px;"></div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Grades Distribution Chart -->
            @php
                $hasGradesDistribution = !empty($grades['distribution']) && array_sum($grades['distribution']) > 0;
            @endphp
            @if($hasGradesDistribution)
                <div class="col-xl-6 col-lg-12 mb-4">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-pie-chart me-2"></i>
                                توزيع الدرجات
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="gradesDistributionChart" style="height: 350px;"></div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Quizzes Scores Chart -->
        @if(count($quizzes['list']) > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-graph-up-arrow me-2"></i>
                                درجات الاختبارات
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="quizzesScoresChart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Progress by Subject -->
        @if(count($progress) > 0)
            <div class="card custom-card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-book me-2"></i>
                        التقدم التفصيلي في المواد
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($progress as $item)
                            @php
                                $progressData = $item['progress'] ?? [];
                                $percentage = $progressData['overall_percentage'] ?? 0;
                                $colorClass = $percentage >= 75 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger');
                            @endphp
                            <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                                <div class="card border h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h6 class="mb-1 fw-semibold">{{ $item['subject']->name ?? 'غير محدد' }}</h6>
                                                @if(isset($item['subject']->schoolClass))
                                                    <small class="text-muted">{{ $item['subject']->schoolClass->name }}</small>
                                                @endif
                                            </div>
                                            <span class="badge bg-{{ $colorClass }} fs-6">
                                                {{ number_format($percentage, 1) }}%
                                            </span>
                                        </div>
                                        
                                        <div class="progress mb-3" style="height: 10px;">
                                            <div class="progress-bar bg-{{ $colorClass }}" 
                                                 style="width: {{ $percentage }}%"
                                                 role="progressbar">
                                            </div>
                                        </div>

                                        <div class="row text-center">
                                            <div class="col-4">
                                                <small class="text-muted d-block">الدروس</small>
                                                <strong class="text-primary">
                                                    {{ $progressData['lessons_completed'] ?? 0 }}/{{ $progressData['lessons_total'] ?? 0 }}
                                                </strong>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted d-block">الاختبارات</small>
                                                <strong class="text-success">
                                                    {{ $progressData['quizzes_completed'] ?? 0 }}/{{ $progressData['quizzes_total'] ?? 0 }}
                                                </strong>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted d-block">الأسئلة</small>
                                                <strong class="text-warning">
                                                    {{ $progressData['questions_completed'] ?? 0 }}/{{ $progressData['questions_total'] ?? 0 }}
                                                </strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Recent Quizzes -->
        @if(count($quizzes['list']) > 0)
            <div class="card custom-card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-clipboard-check me-2"></i>
                        الاختبارات الأخيرة
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
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
                                        <td>
                                            <strong>{{ $quiz['quiz']->title ?? 'غير محدد' }}</strong>
                                        </td>
                                        <td>
                                            {{ $quiz['subject']->name ?? 'غير محدد' }}
                                        </td>
                                        <td>
                                            <strong>{{ $quiz['score'] ?? 0 }}/{{ $quiz['max_score'] ?? 0 }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ ($quiz['percentage'] ?? 0) >= 60 ? 'success' : 'danger' }}">
                                                {{ number_format($quiz['percentage'] ?? 0, 1) }}%
                                        </td>
                                        <td>
                                            @if($quiz['passed'] ?? false)
                                                <span class="badge bg-success">نجح</span>
                                            @else
                                                <span class="badge bg-danger">فشل</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $quiz['finished_at'] ? $quiz['finished_at']->format('Y-m-d') : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Recent Assignments -->
        @if(count($assignments['list']) > 0)
            <div class="card custom-card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-file-text me-2"></i>
                        الواجبات الأخيرة
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>الواجب</th>
                                    <th>المادة</th>
                                    <th>الدرجة</th>
                                    <th>الحالة</th>
                                    <th>تاريخ التسليم</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($assignments['list'], 0, 10) as $assignment)
                                    <tr>
                                        <td>
                                            <strong>{{ $assignment['assignment']->title ?? 'غير محدد' }}</strong>
                                        </td>
                                        <td>
                                            {{ $assignment['subject']->name ?? 'غير محدد' }}
                                        </td>
                                        <td>
                                            @if($assignment['grade'])
                                                <strong>{{ $assignment['score'] ?? 0 }}/{{ $assignment['max_score'] ?? 0 }}</strong>
                                            @else
                                                <span class="text-muted">لم يتم التقييم</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($assignment['status'] == 'submitted')
                                                <span class="badge bg-info">تم التسليم</span>
                                            @else
                                                <span class="badge bg-warning">قيد الانتظار</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $assignment['submitted_at'] ? $assignment['submitted_at']->format('Y-m-d') : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Attendance (if available) -->
        @if(isset($attendance['total_sessions']) && $attendance['total_sessions'] > 0)
            <div class="card custom-card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-check me-2"></i>
                        الحضور
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h4 class="mb-0 text-primary">{{ $attendance['attended_sessions'] ?? 0 }}</h4>
                                <small class="text-muted">جلسات حضرها</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h4 class="mb-0 text-danger">{{ $attendance['absent_sessions'] ?? 0 }}</h4>
                                <small class="text-muted">جلسات غاب عنها</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h4 class="mb-0 text-success">{{ number_format($attendance['attendance_rate'] ?? 0, 1) }}%</h4>
                                <small class="text-muted">نسبة الحضور</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Analytics Section -->
        @if(isset($analytics) && !empty($analytics))
            <div class="card custom-card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up-arrow me-2"></i>
                        التحليلات والنشاط
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="bi bi-activity text-primary fs-1 mb-2"></i>
                                <h4 class="mb-0">{{ $analytics['total_events'] ?? 0 }}</h4>
                                <small class="text-muted">إجمالي الأحداث</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="bi bi-eye text-info fs-1 mb-2"></i>
                                <h4 class="mb-0">{{ $analytics['lessons_viewed'] ?? 0 }}</h4>
                                <small class="text-muted">دروس تم عرضها</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="bi bi-check-circle text-success fs-1 mb-2"></i>
                                <h4 class="mb-0">{{ $analytics['quizzes_completed'] ?? 0 }}</h4>
                                <small class="text-muted">اختبارات مكتملة</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="bi bi-calendar-event text-warning fs-1 mb-2"></i>
                                <h4 class="mb-0">{{ $analytics['most_active_day'] ?? 'N/A' }}</h4>
                                <small class="text-muted">أكثر يوم نشاط</small>
                            </div>
                        </div>
                    </div>

                    @if(isset($analytics['activity_timeline']) && count($analytics['activity_timeline']) > 0)
                        <div class="mt-4">
                            <h6 class="mb-3">خط زمني للنشاط</h6>
                            <div id="activityTimelineChart" style="height: 300px;"></div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Empty State -->
        @if($totalSubjects == 0 && count($quizzes['list']) == 0 && count($assignments['list']) == 0)
            <div class="card custom-card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-file-text fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="mb-2">لا توجد بيانات متاحة</h5>
                    <p class="text-muted">لا توجد مواد مسجلة أو أنشطة حتى الآن.</p>
                    <a href="{{ route('student.classes') }}" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-circle me-1"></i>
                        تصفح المواد المتاحة
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
<!-- End::app-content -->
@stop

@push('scripts')
<style>
    /* تحسين CSS للمخططات */
    #progressChart,
    #gradesDistributionChart,
    #quizzesScoresChart,
    #activityTimelineChart {
        min-height: 300px;
        width: 100%;
    }
    
    /* منع التكرار */
    .card.custom-card {
        position: relative;
    }
    
    /* تحسين responsive */
    @media (max-width: 768px) {
        #progressChart,
        #gradesDistributionChart {
            height: 300px !important;
        }
    }
</style>
<script>
(function() {
    'use strict';
    
    // Load ApexCharts if not already loaded
    function loadApexCharts(callback) {
        // Wait a bit for ApexCharts to be available (it might be loading from footer-scripts)
        var attempts = 0;
        var maxAttempts = 20;
        
        function checkApexChartsLoaded() {
            if (typeof ApexCharts !== 'undefined') {
                callback();
                return;
            }
            
            attempts++;
            if (attempts < maxAttempts) {
                setTimeout(checkApexChartsLoaded, 100);
            } else {
                // Try to load from CDN as fallback
                console.log('ApexCharts not found in footer, loading from CDN...');
                var script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/apexcharts';
                script.onload = function() {
                    console.log('ApexCharts loaded from CDN');
                    callback();
                };
                script.onerror = function() {
                    console.error('Failed to load ApexCharts from CDN');
                };
                document.head.appendChild(script);
            }
        }
        
        checkApexChartsLoaded();
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
    }

    // Only initialize once
    if (!window.reportsChartsInitialized) {
        window.reportsChartsInitialized = true;
        init();
    }
})();

function exportToPDF() {
    window.print();
}
</script>
@endpush