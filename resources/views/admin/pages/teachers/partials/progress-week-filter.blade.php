@php
    $filterAction = $filterAction ?? route('admin.teachers.progress.index');
    $filterMode = $filterMode ?? 'form';
    $redirectBase = $redirectBase ?? $filterAction;
    $currentWeek = $currentWeek ?? null;
@endphp
@if(isset($activeWeeks) && $activeWeeks->isNotEmpty())
    <div class="tp-week-filter mb-4">
        <i class="bi bi-calendar-week text-success"></i>
        <label class="form-label mb-0 small fw-semibold">عرض إحصائيات الأسبوع:</label>
        @if($filterMode === 'redirect')
            <select class="form-select form-select-sm" onchange="window.location.href = this.value ? '{{ rtrim($redirectBase, '/') }}?week_id=' + this.value : '{{ $redirectBase }}';">
                <option value="">الأسبوع الحالي</option>
                @foreach($activeWeeks as $w)
                    <option value="{{ $w->id }}" {{ (isset($displayWeekId) && (string) $displayWeekId === (string) $w->id) ? 'selected' : '' }}>
                        {{ $w->title ?? 'الأسبوع '.$w->week_number }}
                        ({{ $w->start_date->format('Y-m-d') }} → {{ $w->end_date->format('Y-m-d') }})
                    </option>
                @endforeach
            </select>
        @else
            <form method="GET" action="{{ $filterAction }}" class="d-flex flex-wrap gap-2 align-items-center">
                <select name="week_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">الأسبوع الحالي</option>
                    @foreach($activeWeeks as $w)
                        <option value="{{ $w->id }}" {{ (string) request('week_id') === (string) $w->id || (isset($displayWeekId) && (string) $displayWeekId === (string) $w->id) ? 'selected' : '' }}>
                            {{ $w->title ?? 'الأسبوع '.$w->week_number }}
                            ({{ $w->start_date->format('Y-m-d') }} → {{ $w->end_date->format('Y-m-d') }})
                        </option>
                    @endforeach
                </select>
            </form>
        @endif
        @if($currentWeek)
            <span class="small text-muted">
                المعروض: <strong>{{ $currentWeek->title ?? 'الأسبوع '.$currentWeek->week_number }}</strong>
                ({{ $currentWeek->start_date->format('Y-m-d') }} — {{ $currentWeek->end_date->format('Y-m-d') }})
            </span>
        @endif
    </div>
@endif
