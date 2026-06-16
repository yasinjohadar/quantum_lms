<div class="report-content">
    <div class="reports-index-card mb-4">
        <div class="reports-index-card__header">
            <div class="d-flex align-items-center gap-2">
                <span class="reports-index-card__header-icon"><i class="bi bi-book"></i></span>
                <span>تقرير الكورس: {{ $data['subject']->name }}</span>
            </div>
        </div>
    </div>

    @if(isset($data['statistics']))
        <div class="reports-stat-grid">
            <div class="reports-stat-card reports-stat-card--primary">
                <div class="reports-stat-card__label">إجمالي الطلاب</div>
                <div class="reports-stat-card__value">{{ $data['statistics']['total_students'] ?? 0 }}</div>
            </div>
            <div class="reports-stat-card reports-stat-card--success">
                <div class="reports-stat-card__label">إجمالي الدروس</div>
                <div class="reports-stat-card__value">{{ $data['statistics']['total_lessons'] ?? 0 }}</div>
            </div>
            <div class="reports-stat-card reports-stat-card--warning">
                <div class="reports-stat-card__label">إجمالي الاختبارات</div>
                <div class="reports-stat-card__value">{{ $data['statistics']['total_quizzes'] ?? 0 }}</div>
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
                        <div class="reports-stat-card__label">دروس تم عرضها</div>
                        <div class="reports-stat-card__value">{{ $data['analytics']['lessons_viewed'] ?? 0 }}</div>
                    </div>
                    <div class="reports-stat-card reports-stat-card--success">
                        <div class="reports-stat-card__label">اختبارات مكتملة</div>
                        <div class="reports-stat-card__value">{{ $data['analytics']['quizzes_completed'] ?? 0 }}</div>
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
