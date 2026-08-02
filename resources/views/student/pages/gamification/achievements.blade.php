@extends('student.layouts.master')

@section('page-title')
    الإنجازات
@stop

@push('styles')
    @include('student.pages.gamification.partials.achievements-page-styles')
@endpush

@section('content')
@php
    $typeIcons = [
        'attendance' => 'bi-calendar-check',
        'quiz' => 'bi-pencil-square',
        'course' => 'bi-journal-bookmark',
        'streak' => 'bi-fire',
        'special' => 'bi-gem',
    ];
    $completedCount = $achievements->filter(fn ($a) => $a->pivot && $a->pivot->completed_at)->count();
    $inProgressCount = $achievements->count() - $completedCount;
    $pointsEarned = $achievements
        ->filter(fn ($a) => $a->pivot && $a->pivot->completed_at)
        ->sum(fn ($a) => (int) ($a->points_reward ?? 0));
    $typesPresent = $achievements->pluck('type')->unique()->values();
@endphp
<!-- Start::app-content -->
<div class="main-content app-content sach-page">
    <div class="container-fluid">
        <div class="sach-hero">
            <div class="sach-hero__main">
                <div class="sach-hero__icon" aria-hidden="true">
                    <i class="bi bi-trophy"></i>
                </div>
                <div class="min-w-0">
                    <h1 class="sach-hero__title">إنجازاتي</h1>
                    <p class="sach-hero__meta">افتح الإنجازات عبر نشاطك اليومي وتابع تقدّمك</p>
                </div>
            </div>
            <div class="sach-stats">
                <div class="sach-stat">
                    <span class="sach-stat__value">{{ $completedCount }}</span>
                    <span class="sach-stat__label">مكتمل</span>
                </div>
                <div class="sach-stat">
                    <span class="sach-stat__value">{{ $inProgressCount }}</span>
                    <span class="sach-stat__label">قيد التقدم</span>
                </div>
                <div class="sach-stat">
                    <span class="sach-stat__value">{{ number_format($pointsEarned) }}</span>
                    <span class="sach-stat__label">نقاط الإنجازات</span>
                </div>
            </div>
        </div>

        @include('partials.gamification-help-box', ['helpKey' => 'student.achievements'])

        @if($achievements->count() > 0)
            <div class="sach-filters" role="toolbar" aria-label="تصفية الإنجازات">
                <button type="button" class="sach-filter is-active" data-sach-filter="all">الكل</button>
                <button type="button" class="sach-filter" data-sach-filter="done">مكتمل</button>
                <button type="button" class="sach-filter" data-sach-filter="progress">قيد التقدم</button>
                @foreach($typesPresent as $type)
                    <button type="button" class="sach-filter" data-sach-filter="type:{{ $type }}">
                        {{ \App\Models\Achievement::TYPES[$type] ?? $type }}
                    </button>
                @endforeach
            </div>

            <div class="sach-grid" id="sach-grid">
                @foreach($achievements as $achievement)
                    @php
                        $userAchievement = $achievement->pivot;
                        $isCompleted = $userAchievement && $userAchievement->completed_at !== null;
                        $progress = (int) ($userAchievement->progress ?? 0);
                        if ($isCompleted) {
                            $progress = 100;
                        }
                        $progress = max(0, min(100, $progress));
                        $iconClass = $typeIcons[$achievement->type] ?? 'bi-star';
                        $rawIcon = trim((string) ($achievement->icon ?? ''));
                        if ($rawIcon !== '') {
                            if (str_starts_with($rawIcon, 'fe ')) {
                                $iconClass = $rawIcon;
                            } elseif (str_starts_with($rawIcon, 'bi-')) {
                                $iconClass = $rawIcon;
                            } elseif (str_starts_with($rawIcon, 'bi ')) {
                                $iconClass = trim(substr($rawIcon, 3));
                            }
                        }
                        $iconHtmlClass = str_starts_with($iconClass, 'fe ') ? $iconClass : 'bi ' . ltrim($iconClass, '.');
                    @endphp
                    <article
                        class="sach-card {{ $isCompleted ? 'is-done' : 'is-locked' }}"
                        data-type="{{ $achievement->type }}"
                        data-status="{{ $isCompleted ? 'done' : 'progress' }}"
                    >
                        <div class="sach-card__body">
                            <div class="sach-card__top">
                                <div class="sach-card__icon" aria-hidden="true">
                                    <i class="{{ $iconHtmlClass }}"></i>
                                </div>
                                <span class="sach-card__badge">{{ $achievement->type_name }}</span>
                            </div>

                            <h2 class="sach-card__title">{{ $achievement->name }}</h2>
                            <p class="sach-card__desc">{{ $achievement->description }}</p>

                            <div class="sach-card__meta">
                                @if(($achievement->points_reward ?? 0) > 0)
                                    <span class="sach-chip sach-chip--points">
                                        <i class="bi bi-star-fill"></i>
                                        {{ (int) $achievement->points_reward }} نقطة
                                    </span>
                                @endif
                                @if($isCompleted)
                                    <span class="sach-chip sach-chip--done">
                                        <i class="bi bi-check-circle-fill"></i>
                                        مكتمل {{ optional($userAchievement->completed_at)->format('Y-m-d') }}
                                    </span>
                                @endif
                            </div>

                            <div class="sach-progress">
                                <div class="sach-progress__label">
                                    <span>{{ $isCompleted ? 'مكتمل' : 'التقدم' }}</span>
                                    <span>{{ $progress }}%</span>
                                </div>
                                <div class="sach-progress__track" role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="sach-progress__bar" style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach

                <div class="sach-empty d-none" id="sach-empty-filter" hidden>
                    <div class="sach-empty__icon" aria-hidden="true">
                        <i class="bi bi-funnel"></i>
                    </div>
                    <h5 class="fw-bold mb-2">لا نتائج لهذا التصفية</h5>
                    <p class="text-muted mb-0">جرّب تصفية أخرى لعرض الإنجازات</p>
                </div>
            </div>
        @else
            <div class="sach-empty">
                <div class="sach-empty__icon" aria-hidden="true">
                    <i class="bi bi-trophy"></i>
                </div>
                <h5 class="fw-bold mb-2">لا توجد إنجازات بعد</h5>
                <p class="text-muted mb-0">ستظهر هنا إنجازاتك عند تفعيلها من لوحة التحكم</p>
            </div>
        @endif
    </div>
</div>
<!-- End::app-content -->
@stop

@push('scripts')
<script>
    (function () {
        const filters = document.querySelectorAll('[data-sach-filter]');
        const cards = Array.from(document.querySelectorAll('#sach-grid .sach-card'));
        const empty = document.getElementById('sach-empty-filter');
        if (!filters.length || !cards.length) return;

        function applyFilter(key) {
            let visible = 0;
            cards.forEach(function (card) {
                const status = card.getAttribute('data-status');
                const type = card.getAttribute('data-type');
                let show = true;
                if (key === 'done') show = status === 'done';
                else if (key === 'progress') show = status === 'progress';
                else if (key.indexOf('type:') === 0) show = type === key.slice(5);
                card.classList.toggle('d-none', !show);
                if (show) visible += 1;
            });
            if (empty) {
                const showEmpty = visible === 0;
                empty.classList.toggle('d-none', !showEmpty);
                empty.hidden = !showEmpty;
            }
        }

        filters.forEach(function (btn) {
            btn.addEventListener('click', function () {
                filters.forEach(function (b) { b.classList.remove('is-active'); });
                btn.classList.add('is-active');
                applyFilter(btn.getAttribute('data-sach-filter') || 'all');
            });
        });
    })();
</script>
@endpush
