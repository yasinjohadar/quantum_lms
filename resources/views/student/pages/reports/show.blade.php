@extends('student.layouts.master')

@section('page-title')
    {{ $template->name }}
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">{{ $template->name }}</h4>
                <p class="mb-0 text-muted">تقرير شامل لتقدمك الدراسي</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.reports.index') }}">تقاريري</a></li>
                    <li class="breadcrumb-item active">{{ $template->name }}</li>
                </ol>
            </nav>
        </div>
        <!-- End Page Header -->

        <!-- Export Buttons -->
        <div class="card custom-card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-end gap-2">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-download me-1"></i>
                            تصدير التقرير
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('student.reports.export', ['id' => $template->id, 'format' => 'pdf']) }}?{{ http_build_query(request()->except(['_token', '_method'])) }}">
                                    <i class="bi bi-file-pdf me-2"></i> تصدير PDF
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('student.reports.export', ['id' => $template->id, 'format' => 'excel']) }}?{{ http_build_query(request()->except(['_token', '_method'])) }}">
                                    <i class="bi bi-file-excel me-2"></i> تصدير Excel
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('student.reports.export', ['id' => $template->id, 'format' => 'print']) }}?{{ http_build_query(request()->except(['_token', '_method'])) }}" target="_blank">
                                    <i class="bi bi-printer me-2"></i> طباعة
                                </a>
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('student.reports.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-right me-1"></i>
                        العودة
                    </a>
                </div>
            </div>
        </div>

        <!-- Student Information -->
        @if(isset($report['data']['student']))
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
                            @if($report['data']['student']->photo)
                                <img src="{{ asset('storage/' . $report['data']['student']->photo) }}" 
                                     alt="{{ $report['data']['student']->name }}" 
                                     class="rounded-circle mb-2" 
                                     style="width: 100px; height: 100px; object-fit: cover;">
                            @else
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" 
                                     style="width: 100px; height: 100px; font-size: 40px;">
                                    <i class="bi bi-person"></i>
                                </div>
                            @endif
                            <h5 class="mb-0">{{ $report['data']['student']->name }}</h5>
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-envelope text-primary me-2 fs-5"></i>
                                        <div>
                                            <small class="text-muted d-block">البريد الإلكتروني</small>
                                            <strong>{{ $report['data']['student']->email }}</strong>
                                        </div>
                                    </div>
                                </div>
                                @if($report['data']['student']->phone)
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-phone text-primary me-2 fs-5"></i>
                                            <div>
                                                <small class="text-muted d-block">الهاتف</small>
                                                <strong>{{ $report['data']['student']->phone }}</strong>
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

        <!-- Overall Progress Chart -->
        @if(isset($report['data']['charts']['progress']))
            <div class="card custom-card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up me-2"></i>
                        تقدم الطالب في الكورسات
                    </h5>
                </div>
                <div class="card-body">
                    <div id="progressChart" style="height: 400px;"></div>
                </div>
            </div>
        @else
            <div class="card custom-card mb-4">
                <div class="card-body text-center py-5">
                    <i class="bi bi-graph-up fs-1 text-muted mb-3"></i>
                    <p class="text-muted">لا توجد بيانات متاحة لعرض المخططات</p>
                </div>
            </div>
        @endif

        <!-- Progress Summary by Course -->
        @if(isset($report['data']['progress']) && is_array($report['data']['progress']) && count($report['data']['progress']) > 0)
            <div class="card custom-card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-book me-2"></i>
                        التقدم التفصيلي في الكورسات
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($report['data']['progress'] as $item)
                            @php
                                $progress = $item['progress'];
                                $percentage = $progress['overall_percentage'];
                                $colorClass = $percentage >= 75 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger');
                            @endphp
                            <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                                <div class="card border h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h6 class="mb-1 fw-semibold">{{ $item['subject']->name }}</h6>
                                                @if($item['subject']->schoolClass)
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
                                                    {{ $progress['lessons_completed'] }}/{{ $progress['lessons_total'] }}
                                                </strong>
                                                <div class="progress mt-1" style="height: 4px;">
                                                    <div class="progress-bar bg-info" 
                                                         style="width: {{ $progress['lessons_total'] > 0 ? ($progress['lessons_completed'] / $progress['lessons_total']) * 100 : 0 }}%">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted d-block">الاختبارات</small>
                                                <strong class="text-success">
                                                    {{ $progress['quizzes_completed'] }}/{{ $progress['quizzes_total'] }}
                                                </strong>
                                                <div class="progress mt-1" style="height: 4px;">
                                                    <div class="progress-bar bg-success" 
                                                         style="width: {{ $progress['quizzes_total'] > 0 ? ($progress['quizzes_completed'] / $progress['quizzes_total']) * 100 : 0 }}%">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted d-block">الأسئلة</small>
                                                <strong class="text-warning">
                                                    {{ $progress['questions_completed'] }}/{{ $progress['questions_total'] }}
                                                </strong>
                                                <div class="progress mt-1" style="height: 4px;">
                                                    <div class="progress-bar bg-warning" 
                                                         style="width: {{ $progress['questions_total'] > 0 ? ($progress['questions_completed'] / $progress['questions_total']) * 100 : 0 }}%">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="card custom-card mb-4">
                <div class="card-body text-center py-5">
                    <i class="bi bi-book fs-1 text-muted mb-3"></i>
                    <p class="text-muted">لا توجد كورسات مسجلة أو لا توجد بيانات تقدم متاحة</p>
                    <a href="{{ route('student.classes') }}" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-circle me-1"></i>
                        تصفح الكورسات المتاحة
                    </a>
                </div>
            </div>
        @endif

        <!-- Detailed Statistics -->
        @if(isset($report['data']['progress']) && is_array($report['data']['progress']) && count($report['data']['progress']) > 0)
            @php
                $totalLessons = collect($report['data']['progress'])->sum(function($item) {
                    return $item['progress']['lessons_total'];
                });
                $completedLessons = collect($report['data']['progress'])->sum(function($item) {
                    return $item['progress']['lessons_completed'];
                });
                $totalQuizzes = collect($report['data']['progress'])->sum(function($item) {
                    return $item['progress']['quizzes_total'];
                });
                $completedQuizzes = collect($report['data']['progress'])->sum(function($item) {
                    return $item['progress']['quizzes_completed'];
                });
                $totalQuestions = collect($report['data']['progress'])->sum(function($item) {
                    return $item['progress']['questions_total'];
                });
                $completedQuestions = collect($report['data']['progress'])->sum(function($item) {
                    return $item['progress']['questions_completed'];
                });
                $overallAvg = collect($report['data']['progress'])->avg(function($item) {
                    return $item['progress']['overall_percentage'];
                });
            @endphp

            <div class="card custom-card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart me-2"></i>
                        الإحصائيات الشاملة
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                            <div class="card border-primary h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-book text-primary fs-1 mb-2"></i>
                                    <h3 class="mb-1 text-primary">{{ $completedLessons }}/{{ $totalLessons }}</h3>
                                    <p class="mb-0 text-muted">الدروس المكتملة</p>
                                    <div class="progress mt-2" style="height: 8px;">
                                        <div class="progress-bar bg-primary" 
                                             style="width: {{ $totalLessons > 0 ? ($completedLessons / $totalLessons) * 100 : 0 }}%">
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $totalLessons > 0 ? number_format(($completedLessons / $totalLessons) * 100, 1) : 0 }}%</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                            <div class="card border-success h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-clipboard-check text-success fs-1 mb-2"></i>
                                    <h3 class="mb-1 text-success">{{ $completedQuizzes }}/{{ $totalQuizzes }}</h3>
                                    <p class="mb-0 text-muted">الاختبارات المكتملة</p>
                                    <div class="progress mt-2" style="height: 8px;">
                                        <div class="progress-bar bg-success" 
                                             style="width: {{ $totalQuizzes > 0 ? ($completedQuizzes / $totalQuizzes) * 100 : 0 }}%">
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $totalQuizzes > 0 ? number_format(($completedQuizzes / $totalQuizzes) * 100, 1) : 0 }}%</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                            <div class="card border-warning h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-question-circle text-warning fs-1 mb-2"></i>
                                    <h3 class="mb-1 text-warning">{{ $completedQuestions }}/{{ $totalQuestions }}</h3>
                                    <p class="mb-0 text-muted">الأسئلة المكتملة</p>
                                    <div class="progress mt-2" style="height: 8px;">
                                        <div class="progress-bar bg-warning" 
                                             style="width: {{ $totalQuestions > 0 ? ($completedQuestions / $totalQuestions) * 100 : 0 }}%">
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $totalQuestions > 0 ? number_format(($completedQuestions / $totalQuestions) * 100, 1) : 0 }}%</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                            <div class="card border-info h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-trophy text-info fs-1 mb-2"></i>
                                    <h3 class="mb-1 text-info">{{ number_format($overallAvg, 1) }}%</h3>
                                    <p class="mb-0 text-muted">متوسط التقدم الإجمالي</p>
                                    <div class="progress mt-2" style="height: 8px;">
                                        <div class="progress-bar bg-info" 
                                             style="width: {{ $overallAvg }}%">
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ count($report['data']['progress']) }} كورس</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Analytics Section -->
        @if(isset($report['data']['analytics']))
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
                                <h4 class="mb-0">{{ $report['data']['analytics']['total_events'] ?? 0 }}</h4>
                                <small class="text-muted">إجمالي الأحداث</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="bi bi-eye text-info fs-1 mb-2"></i>
                                <h4 class="mb-0">{{ $report['data']['analytics']['lessons_viewed'] ?? 0 }}</h4>
                                <small class="text-muted">دروس تم عرضها</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="bi bi-check-circle text-success fs-1 mb-2"></i>
                                <h4 class="mb-0">{{ $report['data']['analytics']['quizzes_completed'] ?? 0 }}</h4>
                                <small class="text-muted">اختبارات مكتملة</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="bi bi-calendar-event text-warning fs-1 mb-2"></i>
                                <h4 class="mb-0">{{ $report['data']['analytics']['most_active_day'] ?? 'N/A' }}</h4>
                                <small class="text-muted">أكثر يوم نشاط</small>
                            </div>
                        </div>
                    </div>

                    @if(isset($report['data']['analytics']['activity_timeline']) && count($report['data']['analytics']['activity_timeline']) > 0)
                        <div class="mt-4">
                            <h6 class="mb-3">خط زمني للنشاط</h6>
                            <div id="activityTimelineChart" style="height: 300px;"></div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Export Section at Bottom -->
        <div class="card custom-card border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-download me-2"></i>
                    تصدير التقرير
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">يمكنك تصدير هذا التقرير بصيغ مختلفة للاحتفاظ به أو مشاركته</p>
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <a href="{{ route('student.reports.export', ['id' => $template->id, 'format' => 'pdf']) }}?{{ http_build_query(request()->except(['_token', '_method'])) }}" 
                       class="btn btn-danger btn-lg">
                        <i class="bi bi-file-pdf me-2"></i>
                        تصدير PDF
                    </a>
                    <a href="{{ route('student.reports.export', ['id' => $template->id, 'format' => 'excel']) }}?{{ http_build_query(request()->except(['_token', '_method'])) }}" 
                       class="btn btn-success btn-lg">
                        <i class="bi bi-file-excel me-2"></i>
                        تصدير Excel
                    </a>
                    <a href="{{ route('student.reports.export', ['id' => $template->id, 'format' => 'print']) }}?{{ http_build_query(request()->except(['_token', '_method'])) }}" 
                       target="_blank"
                       class="btn btn-info btn-lg">
                        <i class="bi bi-printer me-2"></i>
                        طباعة
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Container closed -->
</div>
<!-- main-content closed -->
@stop

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Progress Chart - بناء الخيارات بشكل كامل في JavaScript
    @if(isset($report['data']['charts']['progress']))
        @php
            $chartData = $report['data']['charts']['progress'];
            $chartOptions = $chartData['options'] ?? [];
            $series = $chartOptions['series'] ?? [];
            $categories = $chartOptions['xaxis']['categories'] ?? [];
        @endphp
        
        @if(!empty($series) && count($series) > 0 && !empty($categories))
            var chartElement = document.querySelector("#progressChart");
            if (chartElement) {
                try {
                    const rawSeries = @json($series);
                    const categories = @json($categories);

                    // تأكد أن القيم أرقام صحيحة
                    const normalizedSeries = rawSeries.map(s => ({
                        name: s.name || 'Series',
                        data: Array.isArray(s.data) ? s.data.map(v => Number(v) || 0) : []
                    }));

                    const progressOptions = {
                        chart: { type: 'bar', height: 400, toolbar: { show: false }, animations: { enabled: false } },
                        xaxis: { categories, labels: { style: { fontSize: '12px' } } },
                        yaxis: { max: 100, labels: { formatter: val => `${val}%` } },
                        series: normalizedSeries,
                        colors: ['#007bff', '#28a745', '#ffc107', '#17a2b8'],
                        plotOptions: { bar: { horizontal: false, columnWidth: '55%', borderRadius: 4 } },
                        dataLabels: { enabled: true, formatter: val => `${(val ?? 0).toFixed(1)}%`, style: { fontSize: '11px', fontWeight: 'bold' } },
                        legend: { position: 'top', horizontalAlign: 'right' },
                        tooltip: { shared: true, intersect: false, y: { formatter: val => `${(val ?? 0).toFixed(1)}%` } }
                    };
                    
                    console.log('Rendering progress chart...');
                    var progressChart = new ApexCharts(chartElement, progressOptions);
                    progressChart.render();
                    console.log('Progress chart rendered successfully');
                } catch (e) {
                    console.error('Error rendering progress chart:', e);
                    // محاولة مخطط احتياطي مبسط
                    try {
                        var fallback = new ApexCharts(chartElement, {
                            chart: { type: 'bar', height: 300 },
                            series: [{ name: 'Progress', data: [0] }],
                            xaxis: { categories: ['-'] }
                        });
                        fallback.render();
                    } catch (e2) {
                        chartElement.innerHTML = '<div class="text-center py-5"><p class="text-danger">حدث خطأ في عرض المخطط: ' + e2.message + '</p></div>';
                    }
                }
            }
        @else
            console.log('No series data for progress chart');
            var chartElement = document.querySelector("#progressChart");
            if (chartElement) {
                chartElement.innerHTML = '<div class="text-center py-5"><p class="text-muted">لا توجد بيانات متاحة لعرض المخطط</p></div>';
            }
        @endif
    @else
        console.log('No chart data in report');
    @endif

    // Activity Timeline Chart
    @if(isset($report['data']['analytics']['activity_timeline']))
        @php
            $timeline = $report['data']['analytics']['activity_timeline'];
            $hasTimeline = is_array($timeline) && count($timeline) > 0;
        @endphp
        
        @if($hasTimeline)
            var timelineElement = document.querySelector("#activityTimelineChart");
            if (timelineElement) {
                try {
                    var timelineData = @json($timeline);
                    var timelineDates = Object.keys(timelineData);
                    var timelineValues = Object.values(timelineData).map(function(v) { return parseInt(v) || 0; });
                    
                    var timelineOptions = {
                        chart: {
                            type: 'area',
                            height: 300,
                            toolbar: { show: true }
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
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.7,
                                opacityTo: 0.9,
                                stops: [0, 90, 100]
                            }
                        },
                        dataLabels: { enabled: false },
                        stroke: { curve: 'smooth', width: 2 },
                        tooltip: {
                            y: {
                                formatter: function(val) { return val + ' حدث'; }
                            }
                        }
                    };
                    
                    console.log('Rendering timeline chart...');
                    var timelineChart = new ApexCharts(timelineElement, timelineOptions);
                    timelineChart.render();
                    console.log('Timeline chart rendered successfully');
                } catch (e) {
                    console.error('Error rendering timeline chart:', e);
                    timelineElement.innerHTML = '<div class="text-center py-5"><p class="text-danger">حدث خطأ: ' + e.message + '</p></div>';
                }
            }
        @endif
    @endif
});
</script>
@endpush
