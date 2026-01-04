<div class="report-content">
    <h4 class="mb-4">تقرير النظام</h4>
    
    <!-- System Statistics -->
    @if(isset($data['system']))
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">إجمالي المستخدمين</h6>
                        <h3 class="mb-0 fw-bold text-primary">{{ $data['system']['total_users'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">إجمالي الطلاب</h6>
                        <h3 class="mb-0 fw-bold text-success">{{ $data['system']['total_students'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">إجمالي الكورسات</h6>
                        <h3 class="mb-0 fw-bold text-warning">{{ $data['system']['total_subjects'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">إجمالي الدروس</h6>
                        <h3 class="mb-0 fw-bold text-info">{{ $data['system']['total_lessons'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- System Usage Chart -->
    @if(isset($data['charts']['usage']) && !empty($data['charts']['usage']))
        <div class="card custom-card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-graph-up me-2"></i>
                    استخدام النظام
                </h5>
            </div>
            <div class="card-body">
                <div id="systemUsageChart" style="height: 400px;"></div>
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

    <!-- Analytics -->
    @if(isset($data['analytics']))
        <div class="card border mb-4">
            <div class="card-header">
                <h6 class="mb-0">التحليلات</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <h4 class="mb-0">{{ $data['analytics']['total_events'] ?? 0 }}</h4>
                        <small class="text-muted">إجمالي الأحداث</small>
                    </div>
                    <div class="col-md-3 text-center">
                        <h4 class="mb-0">{{ $data['analytics']['active_users'] ?? 0 }}</h4>
                        <small class="text-muted">المستخدمون النشطون</small>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // System Usage Chart
    @if(isset($data['charts']['usage']) && !empty($data['charts']['usage']))
        @php
            $chartData = $data['charts']['usage'];
            $chartOptions = $chartData['options'] ?? [];
            $series = $chartOptions['series'] ?? [];
            $categories = $chartOptions['xaxis']['categories'] ?? [];
        @endphp
        
        @if(!empty($series) && is_array($series) && count($series) > 0 && !empty($categories) && is_array($categories) && count($categories) > 0)
            var chartElement = document.querySelector("#systemUsageChart");
            
            if (chartElement) {
                setTimeout(function() {
                    try {
                        if (typeof ApexCharts === 'undefined') {
                            chartElement.innerHTML = '<div class="text-center py-5"><p class="text-danger">مكتبة الرسوم البيانية غير محملة</p></div>';
                            return;
                        }
                        
                        var rawSeries = @json($series);
                        var categories = @json($categories);

                        // تأكد أن القيم أرقام صحيحة
                        var normalizedSeries = [];
                        for (var i = 0; i < rawSeries.length; i++) {
                            var s = rawSeries[i];
                            var dataArr = [];
                            if (s.data && Array.isArray(s.data)) {
                                for (var j = 0; j < s.data.length; j++) {
                                    dataArr.push(parseFloat(s.data[j]) || 0);
                                }
                            }
                            normalizedSeries.push({
                                name: s.name || 'Series',
                                data: dataArr
                            });
                        }

                        var chartOptions = {
                            chart: {
                                type: 'area',
                                height: 400,
                                toolbar: {
                                    show: true
                                },
                                zoom: {
                                    enabled: true
                                }
                            },
                            title: {
                                text: 'استخدام النظام - المستخدمون النشطون يومياً',
                                align: 'center'
                            },
                            xaxis: {
                                categories: categories,
                                labels: {
                                    rotate: -45,
                                    style: {
                                        fontSize: '12px'
                                    }
                                }
                            },
                            yaxis: {
                                title: {
                                    text: 'عدد المستخدمين'
                                }
                            },
                            series: normalizedSeries,
                            colors: ['#3b82f6'],
                            stroke: {
                                curve: 'smooth',
                                width: 2
                            },
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shadeIntensity: 1,
                                    opacityFrom: 0.7,
                                    opacityTo: 0.3,
                                    stops: [0, 90, 100]
                                }
                            },
                            dataLabels: {
                                enabled: false
                            },
                            legend: {
                                position: 'top'
                            },
                            tooltip: {
                                enabled: true,
                                y: {
                                    formatter: function(val) {
                                        return val + ' مستخدم';
                                    }
                                }
                            }
                        };
                        
                        var systemChart = new ApexCharts(chartElement, chartOptions);
                        systemChart.render();
                    } catch (e) {
                        console.error('System chart error:', e);
                        if (chartElement) {
                            chartElement.innerHTML = '<div class="text-center py-5"><p class="text-danger">خطأ: ' + e.message + '</p></div>';
                        }
                    }
                }, 300);
            }
        @else
            console.log('No series data for system usage chart');
            var chartElement = document.querySelector("#systemUsageChart");
            if (chartElement) {
                chartElement.innerHTML = '<div class="text-center py-5"><p class="text-muted">لا توجد بيانات متاحة لعرض المخطط</p></div>';
            }
        @endif
    @endif
});
</script>
@endpush
