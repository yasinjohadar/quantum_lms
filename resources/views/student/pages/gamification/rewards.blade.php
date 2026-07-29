@extends('student.layouts.master')

@section('page-title')
    المكافآت
@stop

@push('styles')
    @include('student.partials.dashboard-widget-styles')
    @include('student.pages.lessons.partials.subject-content-breadcrumb-styles')
    @include('student.pages.gamification.partials.gamification-dashboard-styles')
    @include('student.pages.gamification.partials.gamification-rewards-styles')
@endpush

@section('content')
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
                <li class="student-content-breadcrumb__item">
                    <a href="{{ route('student.gamification.dashboard') }}" class="student-content-breadcrumb__link">
                        <i class="bi bi-trophy"></i>
                        <span>لوحة التحفيز</span>
                    </a>
                </li>
                <li class="student-content-breadcrumb__sep" aria-hidden="true"><i class="bi bi-chevron-left"></i></li>
                <li class="student-content-breadcrumb__item" aria-current="page">
                    <span class="student-content-breadcrumb__current">
                        <i class="bi bi-gift"></i>
                        <span>المكافآت</span>
                    </span>
                </li>
            </ol>
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                <div>
                    <h1 class="student-content-breadcrumb__heading mb-0">
                        <i class="bi bi-gift me-2 text-warning"></i>المكافآت
                    </h1>
                    <p class="student-content-breadcrumb__meta mb-0">استبدل نقاطك بمكافآت حصرية</p>
                </div>
                <a href="{{ route('student.gamification.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-right me-1"></i>لوحة التحفيز
                </a>
            </div>
        </nav>

        @include('partials.gamification-help-box', ['helpKey' => 'student.rewards'])

        <div class="student-rewards-points">
            <div>
                <div class="student-rewards-points__label">نقاطك الحالية</div>
                <div class="student-rewards-points__value">{{ number_format($totalPoints) }}</div>
            </div>
            <div class="student-rewards-points__icon">
                <i class="bi bi-star-fill"></i>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-8 order-lg-1 order-2">
                <div class="student-gamification-panel h-100">
                    <div class="student-gamification-panel__head">
                        <h5 class="student-gamification-panel__title">
                            <i class="bi bi-shop me-1 text-primary"></i>المكافآت المتاحة
                        </h5>
                        @if($availableRewards->count() > 0)
                            <span class="student-gamification-level__chip">{{ $availableRewards->count() }}</span>
                        @endif
                    </div>
                    <div class="student-gamification-panel__body">
                        @if($availableRewards->count() > 0)
                            <div class="student-rewards-grid">
                                @foreach($availableRewards as $reward)
                                    @include('student.pages.gamification.partials.reward-card', [
                                        'reward' => $reward,
                                        'totalPoints' => $totalPoints,
                                        'delay' => $loop->index,
                                    ])
                                @endforeach
                            </div>
                        @else
                            <div class="student-gamification-empty">
                                <div class="student-gamification-empty__icon"><i class="bi bi-gift"></i></div>
                                <p class="text-muted mb-0">لا توجد مكافآت متاحة حالياً</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4 order-lg-2 order-1">
                <div class="student-gamification-panel h-100">
                    <div class="student-gamification-panel__head">
                        <h5 class="student-gamification-panel__title">
                            <i class="bi bi-bag-check me-1 text-success"></i>مكافآتي
                        </h5>
                        @if($userRewards->count() > 0)
                            <span class="student-gamification-level__chip">{{ $userRewards->count() }}</span>
                        @endif
                    </div>
                    <div class="student-gamification-panel__body">
                        @if($userRewards->count() > 0)
                            @foreach($userRewards as $userReward)
                                @include('student.pages.gamification.partials.user-reward-row', [
                                    'userReward' => $userReward,
                                    'delay' => $loop->index,
                                ])
                            @endforeach
                        @else
                            <div class="student-gamification-empty">
                                <div class="student-gamification-empty__icon"><i class="bi bi-inbox"></i></div>
                                <p class="text-muted mb-0">لا توجد مكافآت مستبدلة</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
