@php
    $weekly = $weekly ?? ['target' => 0, 'completed' => 0, 'percentage' => null, 'current_week' => null];
    $pct = $weekly['percentage'] ?? null;
    $pctClass = $pct === null ? 'muted' : ($pct >= 100 ? 'success' : ($pct >= 50 ? 'info' : 'warning'));
    $barW = $pct !== null ? min(100, $pct) : 0;
@endphp
<div class="tp-weekly-panel">
    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="tp-card__header-icon" style="width:30px;height:30px;font-size:0.9rem;"><i class="bi bi-calendar-week"></i></span>
        <div>
            <div class="fw-bold small">الدروس الأسبوعية</div>
            @if(!empty($weekly['current_week']))
                <div class="text-muted" style="font-size:0.72rem;">
                    {{ $weekly['current_week']->title ?? 'أسبوع '.$weekly['current_week']->week_number }}
                </div>
            @endif
        </div>
    </div>
    <div class="tp-weekly-row">
        <span class="text-muted">الهدف الأسبوعي</span>
        <strong>{{ ($weekly['target'] ?? 0) > 0 ? $weekly['target'] : '—' }}</strong>
    </div>
    <div class="tp-weekly-row">
        <span class="text-muted">المنجز في الفترة</span>
        <strong class="text-success">{{ $weekly['completed'] ?? 0 }}</strong>
    </div>
    <div class="tp-weekly-row">
        <span class="text-muted">النسبة</span>
        @if($pct !== null)
            <span class="tp-pct tp-pct--{{ $pctClass }}">{{ number_format($pct, 1) }}%</span>
        @else
            <span class="text-muted">—</span>
        @endif
    </div>
    @if($pct !== null)
        <div class="tp-progress mt-2">
            <div class="tp-progress__bar tp-progress__bar--{{ $pctClass === 'success' ? 'success' : ($pctClass === 'info' ? 'info' : 'warning') }}" style="width: {{ $barW }}%;"></div>
        </div>
    @endif
</div>
