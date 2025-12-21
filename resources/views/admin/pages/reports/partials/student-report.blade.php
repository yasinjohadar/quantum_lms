<div class="report-content">
    <h4 class="mb-4">تقرير الطالب: {{ $data['student']->name }}</h4>
    
    <!-- Charts -->
    @if(isset($data['charts']['progress']))
        <div class="mb-4">
            <div id="progressChart" style="height: 350px;"></div>
        </div>
    @endif

    <!-- Progress Summary -->
    @if(isset($data['progress']))
        <div class="row mb-4">
            @foreach($data['progress'] as $item)
                <div class="col-md-4 mb-3">
                    <div class="card border">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">{{ $item['subject']->name }}</h6>
                            <h3 class="mb-0 fw-bold text-primary">{{ number_format($item['progress']['overall_percentage'], 1) }}%</h3>
                            <div class="progress mt-2" style="height: 8px;">
                                <div class="progress-bar bg-primary" style="width: {{ $item['progress']['overall_percentage'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
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
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    @if(isset($data['charts']['progress']) && isset($data['charts']['progress']['options']))
        document.addEventListener('DOMContentLoaded', function() {
            var options = @json($data['charts']['progress']['options']);
            var chart = new ApexCharts(document.querySelector("#progressChart"), options);
            chart.render();
        });
    @endif
</script>
@endpush

