@php
    $canAfford = $totalPoints >= $reward->points_cost;
    $typeIcons = [
        'discount' => 'bi-percent',
        'badge' => 'bi-award',
        'points' => 'bi-star-fill',
        'access' => 'bi-key-fill',
    ];
    $typeIcon = $typeIcons[$reward->type] ?? 'bi-gift';
@endphp

<div class="student-reward-card {{ $canAfford ? 'student-reward-card--available' : 'student-reward-card--locked' }}"
     style="animation-delay: {{ ($delay ?? 0) * 0.04 }}s;">
    <div class="student-reward-card__head">
        <div class="student-reward-card__icon">
            <i class="bi {{ $typeIcon }}"></i>
        </div>
        <span class="student-reward-card__type">{{ $reward->type_name }}</span>
    </div>

    <h3 class="student-reward-card__title">{{ $reward->name }}</h3>
    @if($reward->description)
        <p class="student-reward-card__desc">{{ $reward->description }}</p>
    @endif

    <div class="student-reward-card__footer">
        <span class="student-reward-card__cost">
            <i class="bi bi-star-fill me-1"></i>{{ number_format($reward->points_cost) }} نقطة
        </span>

        @if($canAfford)
            <form action="{{ route('student.gamification.rewards.claim', $reward->id) }}" method="POST" class="mb-0">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-bag-check me-1"></i>استبدال
                </button>
            </form>
        @else
            <span class="student-reward-card__locked">
                <i class="bi bi-lock me-1"></i>نقاط غير كافية
            </span>
        @endif
    </div>
</div>
