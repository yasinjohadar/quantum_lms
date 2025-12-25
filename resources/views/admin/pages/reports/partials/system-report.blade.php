<div class="report-content">
    <h4 class="mb-4">تقرير النظام</h4>
    
    <!-- System Statistics -->
    @if(isset($data['system']))
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border text-center">
                    <div class="card-body">
                        <h3 class="mb-0">{{ $data['system']['total_users'] ?? 0 }}</h3>
                        <small class="text-muted">إجمالي المستخدمين</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border text-center">
                    <div class="card-body">
                        <h3 class="mb-0">{{ $data['system']['total_students'] ?? 0 }}</h3>
                        <small class="text-muted">إجمالي الطلاب</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border text-center">
                    <div class="card-body">
                        <h3 class="mb-0">{{ $data['system']['total_subjects'] ?? 0 }}</h3>
                        <small class="text-muted">إجمالي الكورسات</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border text-center">
                    <div class="card-body">
                        <h3 class="mb-0">{{ $data['system']['total_lessons'] ?? 0 }}</h3>
                        <small class="text-muted">إجمالي الدروس</small>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Charts -->
    @if(isset($data['charts']['usage']))
        <div class="mb-4">
            <div id="usageChart" style="height: 350px;"></div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    @if(isset($data['charts']['usage']) && isset($data['charts']['usage']['options']))
        document.addEventListener('DOMContentLoaded', function() {
            var options = @json($data['charts']['usage']['options']);
            var chart = new ApexCharts(document.querySelector("#usageChart"), options);
            chart.render();
        });
    @endif
</script>
@endpush
