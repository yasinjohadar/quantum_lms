@php
    $status = $userReward->pivot->status ?? 'pending';
    $statusLabels = [
        'pending' => 'قيد الانتظار',
        'approved' => 'موافق عليه',
        'rejected' => 'مرفوض',
        'delivered' => 'تم التسليم',
    ];
    $statusLabel = $statusLabels[$status] ?? $status;
    $statusClass = match($status) {
        'approved', 'delivered' => 'success',
        'rejected' => 'danger',
        default => 'warning',
    };
@endphp

<div class="student-user-reward-row" style="animation-delay: {{ ($delay ?? 0) * 0.04 }}s;">
    <div class="student-user-reward-row__icon">
        <i class="bi bi-gift"></i>
    </div>
    <div class="student-user-reward-row__main">
        <h6 class="student-user-reward-row__title">{{ $userReward->name }}</h6>
        <span class="student-user-reward-row__date">
            <i class="bi bi-calendar3 me-1"></i>
            {{ $userReward->pivot->claimed_at ? \Carbon\Carbon::parse($userReward->pivot->claimed_at)->format('Y-m-d') : '-' }}
        </span>
    </div>
    <span class="student-user-reward-row__status student-user-reward-row__status--{{ $statusClass }}">
        {{ $statusLabel }}
    </span>
</div>
