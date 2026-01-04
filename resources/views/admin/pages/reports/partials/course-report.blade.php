<div class="report-content">
    <h4 class="mb-4">تقرير الكورس: {{ $data['subject']->name }}</h4>
    
    <!-- Statistics Cards -->
    @if(isset($data['statistics']))
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card border">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">إجمالي الطلاب</h6>
                        <h3 class="mb-0 fw-bold text-primary">{{ $data['statistics']['total_students'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">إجمالي الدروس</h6>
                        <h3 class="mb-0 fw-bold text-success">{{ $data['statistics']['total_lessons'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">إجمالي الاختبارات</h6>
                        <h3 class="mb-0 fw-bold text-warning">{{ $data['statistics']['total_quizzes'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Course Statistics Chart -->
    @if(isset($data['charts']['statistics']) && !empty($data['charts']['statistics']))
        <div class="card custom-card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-graph-up me-2"></i>
                    إحصائيات الكورس
                </h5>
            </div>
            <div class="card-body">
                <div id="courseStatisticsChart" style="height: 400px;"></div>
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
                        <h4 class="mb-0">{{ $data['analytics']['lessons_viewed'] ?? 0 }}</h4>
                        <small class="text-muted">دروس تم عرضها</small>
                    </div>
                    <div class="col-md-3 text-center">
                        <h4 class="mb-0">{{ $data['analytics']['quizzes_completed'] ?? 0 }}</h4>
                        <small class="text-muted">اختبارات مكتملة</small>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Course Statistics Chart
    @if(isset($data['charts']['statistics']) && !empty($data['charts']['statistics']))
        @php
            $chartData = $data['charts']['statistics'];
            $chartOptions = $chartData['options'] ?? [];
            $series = $chartOptions['series'] ?? [];
            $categories = $chartOptions['xaxis']['categories'] ?? [];
        @endphp
        
        @if(!empty($series) && is_array($series) && count($series) > 0 && !empty($categories) && is_array($categories) && count($categories) > 0)
            var chartElement = document.querySelector("#courseStatisticsChart");
            
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
                                type: 'bar',
                                height: 400
                            },
                            title: {
                                text: 'إحصائيات الكورس',
                                align: 'center'
                            },
                            xaxis: {
                                categories: categories
                            },
                            yaxis: {
                                title: {
                                    text: 'القيمة'
                                }
                            },
                            series: normalizedSeries,
                            colors: ['#007bff', '#28a745', '#ffc107', '#17a2b8'],
                            plotOptions: {
                                bar: {
                                    horizontal: false,
                                    columnWidth: '55%'
                                }
                            },
                            dataLabels: {
                                enabled: false
                            },
                            legend: {
                                position: 'top'
                            }
                        };
                        
                        var courseChart = new ApexCharts(chartElement, chartOptions);
                        courseChart.render();
                    } catch (e) {
                        console.error('Course chart error:', e);
                        if (chartElement) {
                            chartElement.innerHTML = '<div class="text-center py-5"><p class="text-danger">خطأ: ' + e.message + '</p></div>';
                        }
                    }
                }, 300);
            }
        @else
            console.log('No series data for course statistics chart');
            var chartElement = document.querySelector("#courseStatisticsChart");
            if (chartElement) {
                chartElement.innerHTML = '<div class="text-center py-5"><p class="text-muted">لا توجد بيانات متاحة لعرض المخطط</p></div>';
            }
        @endif
    @endif
});
</script>
@endpush
