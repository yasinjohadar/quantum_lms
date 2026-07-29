@extends('student.layouts.master')

@section('page-title')
    لوحة التحفيز
@stop

@push('styles')
    @include('student.partials.dashboard-widget-styles')
    @include('student.pages.lessons.partials.subject-content-breadcrumb-styles')
    @include('student.pages.gamification.partials.gamification-dashboard-styles')
@endpush

@section('content')
@php
    $user = Auth::user();
    $levelName = $stats['current_level']?->name ?? 'مبتدئ';
    $progress = $stats['level_progress'] ?? null;
@endphp

<div class="main-content app-content">
    <div class="container-fluid pt-3">
        <nav class="student-content-breadcrumb mb-3" aria-label="مسار التنقل">
            <ol class="student-content-breadcrumb__trail">
                <li class="student-content-breadcrumb__item">
                    <a href="{{ route('student.dashboard') }}" class="student-content-breadcrumb__link">
                        <i class="bi bi-house-door-fill"></i>
                        <span>الرئيسية</span>
                    </a>
                </li>
                <li class="student-content-breadcrumb__sep" aria-hidden="true"><i class="bi bi-chevron-left"></i></li>
                <li class="student-content-breadcrumb__item" aria-current="page">
                    <span class="student-content-breadcrumb__current">
                        <i class="bi bi-trophy"></i>
                        <span>لوحة التحفيز</span>
                    </span>
                </li>
            </ol>
            <h1 class="student-content-breadcrumb__heading">
                <i class="bi bi-trophy me-2 text-warning"></i>لوحة التحفيز
            </h1>
            <p class="student-content-breadcrumb__meta mb-0">ملف اللاعب — نقاطك، مستواك، وإنجازاتك</p>
        </nav>

        @include('partials.gamification-help-box', ['helpKey' => 'student.dashboard'])

        <div class="student-gamification-hero">
            <div class="student-gamification-hero__pattern" aria-hidden="true"></div>
            <div class="student-gamification-hero__inner">
                <div>
                    <div class="student-gamification-hero__profile">
                        <div class="student-gamification-hero__avatar">
                            @if($user->photo)
                                <img src="{{ media_public_url($user->photo) }}" alt="{{ $user->name }}">
                            @else
                                <i class="bi bi-person-fill"></i>
                            @endif
                        </div>
                        <div>
                            <h2 class="student-gamification-hero__name">{{ $user->name }}</h2>
                            <span class="student-gamification-hero__level">
                                <i class="bi bi-star-fill"></i>{{ $levelName }}
                            </span>
                        </div>
                    </div>
                    <div class="student-gamification-hero__metrics">
                        <div>
                            <span class="student-gamification-hero__metric-value">{{ number_format($stats['total_points']) }}</span>
                            <span class="student-gamification-hero__metric-label">إجمالي النقاط</span>
                        </div>
                        <div>
                            <span class="student-gamification-hero__metric-value">{{ $stats['badges_count'] }}</span>
                            <span class="student-gamification-hero__metric-label">شارة</span>
                        </div>
                        <div>
                            <span class="student-gamification-hero__metric-value">{{ $stats['achievements_count'] }}</span>
                            <span class="student-gamification-hero__metric-label">إنجاز</span>
                        </div>
                    </div>
                </div>
                <div class="student-gamification-hero__actions">
                    <a href="{{ route('student.gamification.stats') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-graph-up me-1"></i>إحصائياتي
                    </a>
                    <a href="{{ route('student.gamification.leaderboard') }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-trophy me-1"></i>لوحة المتصدرين
                    </a>
                </div>
            </div>
        </div>

        <div class="student-gamification-stats">
            <div class="student-gamification-stat student-gamification-stat--points">
                <div class="student-gamification-stat__icon"><i class="bi bi-star-fill"></i></div>
                <div>
                    <div class="student-gamification-stat__label">إجمالي النقاط</div>
                    <div class="student-gamification-stat__value">{{ number_format($stats['total_points']) }}</div>
                </div>
            </div>
            <div class="student-gamification-stat student-gamification-stat--badges">
                <div class="student-gamification-stat__icon"><i class="bi bi-award"></i></div>
                <div>
                    <div class="student-gamification-stat__label">الشارات</div>
                    <div class="student-gamification-stat__value">{{ number_format($stats['badges_count']) }}</div>
                </div>
            </div>
            <div class="student-gamification-stat student-gamification-stat--achievements">
                <div class="student-gamification-stat__icon"><i class="bi bi-trophy"></i></div>
                <div>
                    <div class="student-gamification-stat__label">الإنجازات</div>
                    <div class="student-gamification-stat__value">{{ number_format($stats['achievements_count']) }}</div>
                </div>
            </div>
            <div class="student-gamification-stat student-gamification-stat--level">
                <div class="student-gamification-stat__icon"><i class="bi bi-graph-up-arrow"></i></div>
                <div>
                    <div class="student-gamification-stat__label">المستوى الحالي</div>
                    <div class="student-gamification-stat__value">{{ $levelName }}</div>
                </div>
            </div>
        </div>

        @if($stats['current_level'] && $progress)
            <div class="student-gamification-level">
                <div class="student-gamification-level__head">
                    <h5><i class="bi bi-graph-up me-2 text-primary"></i>تقدم المستوى</h5>
                </div>
                <div class="student-gamification-level__body">
                    <div class="student-gamification-level__ends">
                        <div>
                            <p class="student-gamification-level__end-title">{{ $stats['current_level']->name }}</p>
                            <span class="student-gamification-level__end-sub">المستوى الحالي</span>
                        </div>
                        <div class="text-end">
                            @if($progress['next_level'])
                                <p class="student-gamification-level__end-title">{{ $progress['next_level']->name }}</p>
                                <span class="student-gamification-level__end-sub">المستوى التالي</span>
                            @else
                                <p class="student-gamification-level__end-title">أعلى مستوى</p>
                                <span class="student-gamification-level__end-sub">تهانينا!</span>
                            @endif
                        </div>
                    </div>
                    <div class="progress student-gamification-level__bar">
                        <div class="progress-bar" role="progressbar"
                             style="width: {{ min(100, $progress['progress_percentage']) }}%;"
                             aria-valuenow="{{ $progress['progress_percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                            {{ number_format($progress['progress_percentage'], 1) }}%
                        </div>
                    </div>
                    <div class="student-gamification-level__footer">
                        <span>
                            <i class="bi bi-star me-1"></i>
                            {{ number_format($progress['current_points']) }} / {{ number_format($progress['points_required']) }} نقطة
                        </span>
                        @if($progress['next_level'])
                            <span class="student-gamification-level__chip">
                                تحتاج {{ number_format(max(0, $progress['points_required'] - $progress['current_points'])) }} نقطة
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-xl-6">
                <div class="student-gamification-panel h-100">
                    <div class="student-gamification-panel__head">
                        <h5 class="student-gamification-panel__title">
                            <i class="bi bi-award me-1 text-warning"></i>آخر الشارات
                        </h5>
                        <a href="{{ route('student.gamification.badges') }}" class="btn btn-outline-primary btn-sm">عرض الكل</a>
                    </div>
                    <div class="student-gamification-panel__body">
                        @if($recentBadges->count() > 0)
                            @foreach($recentBadges as $badge)
                                <div class="student-gamification-mini-badge"
                                     style="--badge-color: {{ $badge->color ?? '#6366f1' }}; animation-delay: {{ $loop->index * 0.05 }}s;">
                                    <div class="student-gamification-mini-badge__icon">
                                        <i class="{{ $badge->icon ?? 'fe fe-award' }}"></i>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <h6 class="student-gamification-mini-badge__title">{{ $badge->name }}</h6>
                                        <div class="student-gamification-mini-badge__meta">
                                            @if($badge->description)
                                                {{ Str::limit($badge->description, 55) }} ·
                                            @endif
                                            <i class="bi bi-clock me-1"></i>
                                            {{ $badge->pivot->earned_at ? \Carbon\Carbon::parse($badge->pivot->earned_at)->diffForHumans() : '-' }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="student-gamification-empty">
                                <div class="student-gamification-empty__icon"><i class="bi bi-award"></i></div>
                                <p class="text-muted mb-2">لا توجد شارات بعد</p>
                                <a href="{{ route('student.gamification.badges') }}" class="btn btn-primary btn-sm">عرض الشارات المتاحة</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="student-gamification-panel h-100">
                    <div class="student-gamification-panel__head">
                        <h5 class="student-gamification-panel__title">
                            <i class="bi bi-trophy me-1 text-success"></i>آخر الإنجازات
                        </h5>
                        <a href="{{ route('student.gamification.achievements') }}" class="btn btn-outline-primary btn-sm">عرض الكل</a>
                    </div>
                    <div class="student-gamification-panel__body">
                        @if($recentAchievements->count() > 0)
                            @foreach($recentAchievements as $achievement)
                                <div class="student-gamification-achievement-row" style="animation-delay: {{ $loop->index * 0.05 }}s;">
                                    <div class="student-gamification-achievement-row__icon">
                                        <i class="{{ $achievement->icon ?? 'bi bi-trophy-fill' }}"></i>
                                    </div>
                                    <div class="student-gamification-achievement-row__main">
                                        <h6 class="student-gamification-achievement-row__title">{{ $achievement->name }}</h6>
                                        @if($achievement->description)
                                            <p class="student-gamification-achievement-row__desc">{{ Str::limit($achievement->description, 60) }}</p>
                                        @endif
                                    </div>
                                    <span class="student-gamification-achievement-row__time">
                                        {{ $achievement->pivot->completed_at ? \Carbon\Carbon::parse($achievement->pivot->completed_at)->diffForHumans() : '-' }}
                                    </span>
                                </div>
                            @endforeach
                        @else
                            <div class="student-gamification-empty">
                                <div class="student-gamification-empty__icon"><i class="bi bi-trophy"></i></div>
                                <p class="text-muted mb-2">لا توجد إنجازات بعد</p>
                                <a href="{{ route('student.gamification.achievements') }}" class="btn btn-primary btn-sm">عرض الإنجازات المتاحة</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="student-gamification-panel">
            <div class="student-gamification-panel__head">
                <h5 class="student-gamification-panel__title">
                    <i class="bi bi-lightning-charge me-1 text-warning"></i>إجراءات سريعة
                </h5>
            </div>
            <div class="student-gamification-panel__body">
                <div class="student-gamification-actions">
                    <a href="{{ route('student.gamification.challenges') }}" class="student-gamification-action student-gamification-action--challenges">
                        <span class="student-gamification-action__icon"><i class="bi bi-fire"></i></span>
                        <span class="student-gamification-action__label">التحديات</span>
                    </a>
                    <a href="{{ route('student.gamification.rewards') }}" class="student-gamification-action student-gamification-action--rewards">
                        <span class="student-gamification-action__icon"><i class="bi bi-gift"></i></span>
                        <span class="student-gamification-action__label">المكافآت</span>
                    </a>
                    <a href="{{ route('student.tasks.index') }}" class="student-gamification-action student-gamification-action--tasks">
                        <span class="student-gamification-action__icon"><i class="bi bi-check2-square"></i></span>
                        <span class="student-gamification-action__label">المهام</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
