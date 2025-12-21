<div class="report-content">
    <h4 class="mb-4">تقرير الكورس: {{ $data['subject']->name }}</h4>
    
    <!-- Statistics -->
    @if(isset($data['statistics']))
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border text-center">
                    <div class="card-body">
                        <h3 class="mb-0">{{ $data['statistics']['total_students'] ?? 0 }}</h3>
                        <small class="text-muted">إجمالي الطلاب</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border text-center">
                    <div class="card-body">
                        <h3 class="mb-0">{{ $data['statistics']['total_lessons'] ?? 0 }}</h3>
                        <small class="text-muted">إجمالي الدروس</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border text-center">
                    <div class="card-body">
                        <h3 class="mb-0">{{ $data['statistics']['total_quizzes'] ?? 0 }}</h3>
                        <small class="text-muted">إجمالي الاختبارات</small>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Charts -->
    @if(isset($data['charts']['statistics']))
        <div class="mb-4">
            <div id="statisticsChart" style="height: 350px;"></div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    @if(isset($data['charts']['statistics']) && isset($data['charts']['statistics']['options']))
        document.addEventListener('DOMContentLoaded', function() {
            var options = @json($data['charts']['statistics']['options']);
            var chart = new ApexCharts(document.querySelector("#statisticsChart"), options);
            chart.render();
        });
    @endif
</script>
@endpush

