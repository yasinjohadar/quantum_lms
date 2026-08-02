@php
    $typeIcons = [
        'attendance' => 'bi-calendar-check',
        'lesson_completion' => 'bi-journal-check',
        'quiz' => 'bi-pencil-square',
        'question' => 'bi-chat-quote',
    ];
    $progress = $userTask ? (int) $userTask->progress : 0;
    $requiredCount = max(1, (int) ($task->criteria['count'] ?? 1));
    $percentage = min(($progress / $requiredCount) * 100, 100);
    $status = $userTask->status ?? 'in_progress';
    $isDone = $status === 'completed';
    $isExpired = $status === 'expired';
    if ($isDone) {
        $percentage = 100;
    }
    $icon = $typeIcons[$task->type] ?? 'bi-lightning-charge';
    $statusClass = $isDone ? 'is-done' : ($isExpired ? 'is-expired' : 'is-progress');
    $statusLabel = $isDone ? 'مكتملة' : ($isExpired ? 'منتهية' : 'قيد التنفيذ');
    $statusIcon = $isDone ? 'bi-check-circle-fill' : ($isExpired ? 'bi-x-circle-fill' : 'bi-hourglass-split');
    $showPeriod = ! empty($showPeriod);
@endphp
<article class="stask-card {{ $isDone ? 'is-done' : '' }} {{ $isExpired ? 'is-expired' : '' }}" data-type="{{ $task->type }}">
    <div class="stask-card__body">
        <div class="stask-card__top">
            <div class="stask-card__icon" aria-hidden="true">
                <i class="bi {{ $icon }}"></i>
            </div>
            <span class="stask-card__status {{ $statusClass }}">
                <i class="bi {{ $statusIcon }}"></i>
                {{ $statusLabel }}
            </span>
        </div>

        <h3 class="stask-card__title">{{ $task->name }}</h3>
        <p class="stask-card__desc">{{ $task->description }}</p>

        <div class="stask-card__meta">
            <span class="stask-chip">
                <i class="bi bi-tag"></i>
                {{ $task->type_name }}
            </span>
            <span class="stask-chip stask-chip--points">
                <i class="bi bi-star-fill"></i>
                مكافأة {{ number_format($task->points_reward) }} نقطة
            </span>
            @if($showPeriod)
                <span class="stask-chip stask-chip--period">
                    <i class="bi bi-calendar-week"></i>
                    {{ $task->start_day_name }} - {{ $task->end_day_name }}
                </span>
            @endif
        </div>

        <div class="stask-progress">
            <div class="stask-progress__label">
                <span>التقدم</span>
                <span>{{ $progress }} / {{ $requiredCount }}</span>
            </div>
            <div class="stask-progress__track" role="progressbar" aria-valuenow="{{ (int) $percentage }}" aria-valuemin="0" aria-valuemax="100">
                <div class="stask-progress__bar" style="width: {{ number_format($percentage, 2, '.', '') }}%"></div>
            </div>
        </div>
    </div>
</article>
