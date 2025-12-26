@extends('admin.layouts.master')

@section('page-title')
    لوحة تحكم التحليلات
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">لوحة تحكم التحليلات</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">لوحة تحكم التحليلات</li>
                    </ol>
                </nav>
            </div>
            <form method="GET" class="d-flex gap-2">
                <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="week" {{ $period === 'week' ? 'selected' : '' }}>آخر أسبوع</option>
                    <option value="month" {{ $period === 'month' ? 'selected' : '' }}>آخر شهر</option>
                    <option value="year" {{ $period === 'year' ? 'selected' : '' }}>آخر سنة</option>
                </select>
            </form>
        </div>
        <!-- End Page Header -->

        <!-- Summary Cards -->
        <div class="row">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card overflow-hidden bg-primary-gradient">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-fixed-white">إجمالي الأحداث المسجلة</p>
                                <h4 class="mb-0 text-fixed-white">{{ number_format($systemAnalytics['total_events'] ?? 0) }}</h4>
                            </div>
                            <div class="avatar avatar-md bg-white text-primary">
                                <i class="bi bi-activity"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card overflow-hidden bg-success-gradient">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-fixed-white">المستخدمون النشطون</p>
                                <h4 class="mb-0 text-fixed-white">{{ number_format($systemAnalytics['unique_users'] ?? 0) }}</h4>
                            </div>
                            <div class="avatar avatar-md bg-white text-success">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card overflow-hidden bg-warning-gradient">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-fixed-white">أنواع الأحداث</p>
                                <h4 class="mb-0 text-fixed-white">{{ isset($systemAnalytics['event_breakdown']) ? count($systemAnalytics['event_breakdown']) : 0 }}</h4>
                            </div>
                            <div class="avatar avatar-md bg-white text-warning">
                                <i class="bi bi-diagram-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card overflow-hidden bg-info-gradient">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-fixed-white">أعلى 10 مستخدمين نشاطاً</p>
                                <h4 class="mb-0 text-fixed-white">{{ is_countable($topActiveUsers) ? count($topActiveUsers) : 0 }}</h4>
                            </div>
                            <div class="avatar avatar-md bg-white text-info">
                                <i class="bi bi-trophy"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- System Usage Chart -->
            <div class="col-xl-8 col-lg-12">
                <div class="card custom-card">
                    <div class="card-header border-bottom-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">منحنى استخدام النظام</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="system-usage-chart" style="min-height: 350px;"></div>
                    </div>
                </div>
            </div>

            <!-- Event Breakdown -->
            <div class="col-xl-4 col-lg-12">
                <div class="card custom-card">
                    <div class="card-header border-bottom-0">
                        <h5 class="card-title mb-0">تفصيل أنواع الأحداث</h5>
                    </div>
                    <div class="card-body">
                        @if(!empty($systemAnalytics['event_breakdown']))
                            <ul class="list-group list-group-flush">
                                @foreach($systemAnalytics['event_breakdown'] as $type => $count)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span class="text-muted">{{ $type }}</span>
                                        <span class="badge bg-primary rounded-pill">{{ $count }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-center text-muted mb-0">لا توجد بيانات كافية لعرضها.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Active Users -->
        <div class="row">
            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-header border-bottom-0">
                        <h5 class="card-title mb-0">أكثر المستخدمين نشاطاً</h5>
                    </div>
                    <div class="card-body">
                        @if(!empty($topActiveUsers))
                            <div class="table-responsive">
                                <table class="table table-vcenter text-nowrap mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>الطالب</th>
                                            <th>البريد</th>
                                            <th>عدد الأحداث</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($topActiveUsers as $index => $item)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $item['user']?->name ?? 'غير معروف' }}</td>
                                                <td>{{ $item['user']?->email ?? '-' }}</td>
                                                <td><span class="badge bg-success">{{ $item['count'] }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-center text-muted mb-0">لا توجد بيانات كافية لعرض قائمة النشطين.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartConfig = @json($systemUsageChart);
            const chartElement = document.getElementById('system-usage-chart');

            if (!chartElement) {
                console.error('Chart element not found');
                return;
            }

            if (!chartConfig || !chartConfig.options) {
                console.error('Chart config is missing');
                return;
            }

            const options = chartConfig.options;

            // التحقق من وجود البيانات
            if (!options.series || !options.series.length || !options.series[0].data || options.series[0].data.length === 0) {
                chartElement.innerHTML = '<div class="text-center text-muted p-5"><p>لا توجد بيانات كافية لعرض المخطط</p></div>';
                return;
            }

            const chartOptions = {
                chart: {
                    type: 'area',
                    height: 350,
                    toolbar: {
                        show: true
                    },
                    zoom: {
                        enabled: true
                    }
                },
                series: options.series || [],
                xaxis: options.xaxis || {},
                title: options.title || {},
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                colors: ['#3b82f6'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.3,
                        stops: [0, 90, 100]
                    }
                },
                tooltip: {
                    enabled: true
                },
                grid: {
                    borderColor: '#e7e7e7',
                    strokeDashArray: 4
                }
            };

            try {
                const chart = new ApexCharts(chartElement, chartOptions);
                chart.render();
            } catch (error) {
                console.error('Error rendering chart:', error);
                chartElement.innerHTML = '<div class="text-center text-danger p-5"><p>خطأ في تحميل المخطط: ' + error.message + '</p></div>';
            }
        });
    </script>
@stop

