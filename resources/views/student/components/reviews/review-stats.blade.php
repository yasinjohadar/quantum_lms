@props(['stats', 'reviewable'])

<div class="card custom-card">
    <div class="card-header">
        <div class="card-title">إحصائيات التقييمات</div>
    </div>
    <div class="card-body">
        <div class="row text-center mb-3">
            <div class="col-6">
                <h3 class="mb-0">{{ number_format($stats['average'], 1) }}</h3>
                <small class="text-muted">متوسط التقييم</small>
            </div>
            <div class="col-6">
                <h3 class="mb-0">{{ $stats['total'] }}</h3>
                <small class="text-muted">إجمالي التقييمات</small>
            </div>
        </div>

        <div class="mt-3">
            @for($i = 5; $i >= 1; $i--)
                <div class="d-flex align-items-center mb-2">
                    <div class="flex-shrink-0" style="width: 60px;">
                        <small>{{ $i }} نجوم</small>
                    </div>
                    <div class="flex-grow-1 mx-2">
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-warning" role="progressbar" 
                                 style="width: {{ $stats['percentages'][$i] }}%"
                                 aria-valuenow="{{ $stats['percentages'][$i] }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                {{ $stats['percentages'][$i] }}%
                            </div>
                        </div>
                    </div>
                    <div class="flex-shrink-0" style="width: 40px; text-align: right;">
                        <small class="text-muted">({{ $stats['distribution'][$i] }})</small>
                    </div>
                </div>
            @endfor
        </div>
    </div>
</div>

