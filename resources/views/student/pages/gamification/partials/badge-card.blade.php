@php
    $badgeColor = $badge->color ?? '#6366f1';
    $isEarned = $earned ?? false;
    $iconClass = $badge->icon ?? 'fe fe-award';
@endphp

<div class="student-badge-card {{ $isEarned ? 'student-badge-card--earned' : 'student-badge-card--locked' }}"
     style="--badge-color: {{ $badgeColor }}; animation-delay: {{ ($delay ?? 0) * 0.04 }}s;">
    <div class="student-badge-card__glow" aria-hidden="true"></div>

    <div class="student-badge-card__icon-wrap">
        @if(!$isEarned)
            <span class="student-badge-card__lock" aria-hidden="true"><i class="bi bi-lock-fill"></i></span>
        @endif
        <i class="{{ $iconClass }} student-badge-card__icon"></i>
    </div>

    <h3 class="student-badge-card__title">{{ $badge->name }}</h3>
    <p class="student-badge-card__desc">{{ $badge->description }}</p>

    <div class="student-badge-card__footer">
        @if($isEarned && !empty($earnedAt))
            <span class="student-badge-card__meta student-badge-card__meta--earned">
                <i class="bi bi-calendar-check me-1"></i>
                حصلت عليها: {{ \Carbon\Carbon::parse($earnedAt)->format('Y-m-d') }}
            </span>
        @elseif(!$isEarned && ($badge->points_required ?? 0) > 0)
            <span class="student-badge-card__meta student-badge-card__meta--points">
                <i class="bi bi-star-fill me-1"></i>
                مطلوب: {{ number_format($badge->points_required) }} نقطة
            </span>
        @else
            <span class="student-badge-card__meta student-badge-card__meta--locked">
                <i class="bi bi-hourglass-split me-1"></i>
                لم تُحقَّق بعد
            </span>
        @endif
    </div>

    @if($isEarned)
        <span class="student-badge-card__ribbon">مكتسبة</span>
    @endif
</div>
