<div class="report-content">
    <div class="reports-index-card mb-4">
        <div class="reports-index-card__header">
            <div class="d-flex align-items-center gap-2">
                <span class="reports-index-card__header-icon"><i class="bi bi-gear"></i></span>
                <span>تقرير النظام</span>
            </div>
        </div>
    </div>

    @if(isset($data['system']))
        <div class="reports-stat-grid">
            <div class="reports-stat-card reports-stat-card--primary">
                <div class="reports-stat-card__label">إجمالي المستخدمين</div>
                <div class="reports-stat-card__value">{{ $data['system']['total_users'] ?? 0 }}</div>
            </div>
            <div class="reports-stat-card reports-stat-card--success">
                <div class="reports-stat-card__label">إجمالي الطلاب</div>
                <div class="reports-stat-card__value">{{ $data['system']['total_students'] ?? 0 }}</div>
            </div>
            <div class="reports-stat-card reports-stat-card--warning">
                <div class="reports-stat-card__label">إجمالي الكورسات</div>
                <div class="reports-stat-card__value">{{ $data['system']['total_subjects'] ?? 0 }}</div>
            </div>
            <div class="reports-stat-card reports-stat-card--info">
                <div class="reports-stat-card__label">إجمالي الدروس</div>
                <div class="reports-stat-card__value">{{ $data['system']['total_lessons'] ?? 0 }}</div>
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

    @if(isset($data['analytics']))
        <div class="reports-index-card mb-4">
            <div class="reports-index-card__header">
                <div class="d-flex align-items-center gap-2">
                    <span class="reports-index-card__header-icon"><i class="bi bi-bar-chart"></i></span>
                    <span>التحليلات</span>
                </div>
            </div>
            <div class="reports-index-card__body">
                <div class="reports-stat-grid">
                    <div class="reports-stat-card reports-stat-card--info">
                        <div class="reports-stat-card__label">إجمالي الأحداث</div>
                        <div class="reports-stat-card__value">{{ $data['analytics']['total_events'] ?? 0 }}</div>
                    </div>
                    <div class="reports-stat-card reports-stat-card--primary">
                        <div class="reports-stat-card__label">المستخدمون النشطون</div>
                        <div class="reports-stat-card__value">{{ $data['analytics']['active_users'] ?? 0 }}</div>
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
