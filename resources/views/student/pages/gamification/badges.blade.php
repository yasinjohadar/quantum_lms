@extends('student.layouts.master')

@section('page-title')
    الشارات
@stop

@push('styles')
    @include('student.partials.dashboard-widget-styles')
    @include('student.pages.lessons.partials.subject-content-breadcrumb-styles')
    @include('student.pages.gamification.partials.badges-page-styles')
@endpush

@section('content')
<div class="main-content app-content">
    <div class="container-fluid pt-3">
        @php
            $earnedCount = $userBadges->count();
            $remainingCount = $availableBadges->count();
            $totalCount = $earnedCount + $remainingCount;
        @endphp

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
                        <span>التحفيز</span>
                    </a>
                </li>
                <li class="student-content-breadcrumb__sep" aria-hidden="true"><i class="bi bi-chevron-left"></i></li>
                <li class="student-content-breadcrumb__item" aria-current="page">
                    <span class="student-content-breadcrumb__current">
                        <i class="bi bi-award"></i>
                        <span>الشارات</span>
                    </span>
                </li>
            </ol>
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                <div>
                    <h1 class="student-content-breadcrumb__heading mb-0">
                        <i class="bi bi-award me-2 text-warning"></i>شاراتي
                    </h1>
                    <p class="student-content-breadcrumb__meta mb-0">اجمع الشارات وتابع إنجازاتك في رحلة التعلم</p>
                </div>
                <a href="{{ route('student.gamification.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-right me-1"></i>لوحة التحفيز
                </a>
            </div>
        </nav>

        <div class="student-badges-stats">
            <div class="student-badges-stat student-badges-stat--earned">
                <div class="student-badges-stat__icon"><i class="bi bi-patch-check-fill"></i></div>
                <div>
                    <div class="student-badges-stat__label">شارات مكتسبة</div>
                    <div class="student-badges-stat__value">{{ $earnedCount }}</div>
                </div>
            </div>
            <div class="student-badges-stat student-badges-stat--remaining">
                <div class="student-badges-stat__icon"><i class="bi bi-lock"></i></div>
                <div>
                    <div class="student-badges-stat__label">بانتظار الإنجاز</div>
                    <div class="student-badges-stat__value">{{ $remainingCount }}</div>
                </div>
            </div>
            <div class="student-badges-stat student-badges-stat--total">
                <div class="student-badges-stat__icon"><i class="bi bi-collection"></i></div>
                <div>
                    <div class="student-badges-stat__label">إجمالي الشارات</div>
                    <div class="student-badges-stat__value">{{ $totalCount }}</div>
                </div>
            </div>
        </div>

        <section class="student-badges-section">
            <div class="student-badges-section__head">
                <h2 class="student-badges-section__title">
                    <i class="bi bi-stars me-1 text-warning"></i>شاراتي
                </h2>
                @if($earnedCount > 0)
                    <span class="student-badges-section__count">{{ $earnedCount }}</span>
                @endif
            </div>

            @if($earnedCount > 0)
                <div class="student-badges-grid">
                    @foreach($userBadges as $badge)
                        @include('student.pages.gamification.partials.badge-card', [
                            'badge' => $badge,
                            'earned' => true,
                            'earnedAt' => $badge->pivot->earned_at ?? null,
                            'delay' => $loop->index,
                        ])
                    @endforeach
                </div>
            @else
                <div class="student-badges-empty">
                    <div class="student-badges-empty__icon">
                        <i class="bi bi-award"></i>
                    </div>
                    <h5 class="mb-2 text-muted">لا توجد شارات بعد</h5>
                    <p class="text-muted mb-0">أكمل الدروس والاختبارات لتحصل على شاراتك الأولى</p>
                </div>
            @endif
        </section>

        @if($remainingCount > 0)
            <section class="student-badges-section">
                <div class="student-badges-section__head">
                    <h2 class="student-badges-section__title">
                        <i class="bi bi-unlock me-1 text-primary"></i>الشارات المتاحة
                    </h2>
                    <span class="student-badges-section__count">{{ $remainingCount }}</span>
                </div>

                <div class="student-badges-grid">
                    @foreach($availableBadges as $badge)
                        @include('student.pages.gamification.partials.badge-card', [
                            'badge' => $badge,
                            'earned' => false,
                            'delay' => $loop->index,
                        ])
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>
@stop
