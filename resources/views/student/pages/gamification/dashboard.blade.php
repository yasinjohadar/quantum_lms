@extends('student.layouts.master')

@section('page-title')
    لوحة التحفيز - ملف اللاعب
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">لوحة التحفيز - ملف اللاعب</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">لوحة التحفيز</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('student.gamification.stats') }}" class="btn btn-info btn-sm">
                    <i class="bi bi-graph-up me-1"></i> إحصائياتي
                </a>
                <a href="{{ route('student.gamification.leaderboard') }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-trophy me-1"></i> لوحة المتصدرين
                </a>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Player Profile Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card custom-card overflow-hidden bg-gradient-primary">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar avatar-xl bg-white text-primary me-3">
                                        <i class="bi bi-person-circle fs-1"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-1 text-white">{{ Auth::user()->name }}</h4>
                                        <p class="mb-0 text-white-50">
                                            @if($stats['current_level'])
                                                <i class="bi bi-star-fill me-1"></i>
                                                {{ $stats['current_level']->name }}
                                            @else
                                                <i class="bi bi-star me-1"></i>
                                                مبتدئ
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-auto">
                                        <div class="stats-text">
                                            <h2 class="mb-0 stats-number">{{ number_format($stats['total_points']) }}</h2>
                                            <small class="stats-label">إجمالي النقاط</small>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="stats-text">
                                            <h2 class="mb-0 stats-number">{{ $stats['badges_count'] }}</h2>
                                            <small class="stats-label">شارة</small>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="stats-text">
                                            <h2 class="mb-0 stats-number">{{ $stats['achievements_count'] }}</h2>
                                            <small class="stats-label">إنجاز</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                @if($stats['current_level'] && $stats['current_level']->icon)
                                    <div class="avatar avatar-xxl bg-white-transparent">
                                        <img src="{{ asset($stats['current_level']->icon) }}" alt="{{ $stats['current_level']->name }}" class="w-100 h-100">
                                    </div>
                                @else
                                    <div class="avatar avatar-xxl bg-white-transparent">
                                        <i class="bi bi-trophy fs-1 text-white"></i>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3 mb-xl-0">
                <div class="card overflow-hidden sales-card bg-primary-gradient h-100">
                    <div class="px-3 pt-3 pb-2 pt-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-2 fs-12 text-fixed-white">إجمالي النقاط</h6>
                                <h3 class="mb-1 text-fixed-white">{{ number_format($stats['total_points']) }}</h3>
                                <p class="mb-0 fs-11 text-fixed-white op-7">النقاط الكلية المكتسبة</p>
                            </div>
                            <div class="ms-auto">
                                <i class="fe fe-star fs-32 text-fixed-white op-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3 mb-xl-0">
                <div class="card overflow-hidden sales-card bg-success-gradient h-100">
                    <div class="px-3 pt-3 pb-2 pt-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-2 fs-12 text-fixed-white">الشارات</h6>
                                <h3 class="mb-1 text-fixed-white">{{ number_format($stats['badges_count']) }}</h3>
                                <p class="mb-0 fs-11 text-fixed-white op-7">الشارات المكتسبة</p>
                            </div>
                            <div class="ms-auto">
                                <i class="fe fe-award fs-32 text-fixed-white op-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3 mb-xl-0">
                <div class="card overflow-hidden sales-card bg-info-gradient h-100">
                    <div class="px-3 pt-3 pb-2 pt-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-2 fs-12 text-fixed-white">الإنجازات</h6>
                                <h3 class="mb-1 text-fixed-white">{{ number_format($stats['achievements_count']) }}</h3>
                                <p class="mb-0 fs-11 text-fixed-white op-7">الإنجازات المكتملة</p>
                            </div>
                            <div class="ms-auto">
                                <i class="fe fe-trophy fs-32 text-fixed-white op-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3 mb-xl-0">
                <div class="card overflow-hidden sales-card bg-warning-gradient h-100">
                    <div class="px-3 pt-3 pb-2 pt-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-2 fs-12 text-fixed-white">المستوى الحالي</h6>
                                <h3 class="mb-1 text-fixed-white" style="font-size: 1.25rem; line-height: 1.2;">
                                    {{ $stats['current_level'] ? $stats['current_level']->name : 'لا يوجد' }}
                                </h3>
                                <p class="mb-0 fs-11 text-fixed-white op-7">مستواك الحالي</p>
                            </div>
                            <div class="ms-auto">
                                <i class="fe fe-trending-up fs-32 text-fixed-white op-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Level Progress -->
        @if($stats['current_level'])
        <div class="row mb-4">
            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-header border-bottom-0">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-graph-up me-2 text-primary"></i>
                            تقدم المستوى
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $progress = $stats['level_progress'];
                        @endphp
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1">{{ $stats['current_level']->name }}</h6>
                                <small class="text-muted">المستوى الحالي</small>
                            </div>
                            <div class="text-end">
                                @if($progress['next_level'])
                                    <h6 class="mb-1">{{ $progress['next_level']->name }}</h6>
                                    <small class="text-muted">المستوى التالي</small>
                                @else
                                    <h6 class="mb-1">أعلى مستوى</h6>
                                    <small class="text-muted">تهانينا!</small>
                                @endif
                            </div>
                        </div>
                        <div class="progress" style="height: 25px; border-radius: 10px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary-gradient" 
                                 role="progressbar" 
                                 style="width: {{ $progress['progress_percentage'] }}%"
                                 aria-valuenow="{{ $progress['progress_percentage'] }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <strong class="text-white">{{ number_format($progress['progress_percentage'], 1) }}%</strong>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="text-muted">
                                <i class="bi bi-star me-1"></i>
                                {{ number_format($progress['current_points']) }} / {{ number_format($progress['points_required']) }} نقطة
                            </span>
                            @if($progress['next_level'])
                                <span class="badge bg-primary-transparent">
                                    تحتاج {{ number_format($progress['points_required'] - $progress['current_points']) }} نقطة للوصول إلى {{ $progress['next_level']->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Recent Badges & Achievements -->
        <div class="row">
            <div class="col-xl-6 col-lg-12">
                <div class="card custom-card">
                    <div class="card-header border-bottom-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-award me-2 text-warning"></i>
                                آخر الشارات
                            </h5>
                            <a href="{{ route('student.gamification.badges') }}" class="btn btn-sm btn-outline-primary">
                                عرض الكل
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($recentBadges->count() > 0)
                            <div class="row g-3">
                                @foreach($recentBadges as $badge)
                                <div class="col-md-6">
                                    <div class="card border h-100">
                                        <div class="card-body text-center">
                                            <div class="avatar avatar-lg bg-{{ $badge->color ?? 'primary' }}-transparent mx-auto mb-3">
                                                <i class="bi bi-award-fill fs-2 text-{{ $badge->color ?? 'primary' }}"></i>
                                            </div>
                                            <h6 class="mb-1">{{ $badge->name }}</h6>
                                            @if($badge->description)
                                                <p class="text-muted small mb-2">{{ Str::limit($badge->description, 50) }}</p>
                                            @endif
                                            <small class="text-muted">
                                                <i class="bi bi-clock me-1"></i>
                                                {{ $badge->pivot->earned_at ? \Carbon\Carbon::parse($badge->pivot->earned_at)->diffForHumans() : '-' }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-award fs-1 text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">لا توجد شارات بعد</p>
                                <a href="{{ route('student.gamification.badges') }}" class="btn btn-sm btn-primary mt-3">
                                    عرض الشارات المتاحة
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-12">
                <div class="card custom-card">
                    <div class="card-header border-bottom-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-trophy me-2 text-success"></i>
                                آخر الإنجازات
                            </h5>
                            <a href="{{ route('student.gamification.achievements') }}" class="btn btn-sm btn-outline-primary">
                                عرض الكل
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($recentAchievements->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($recentAchievements as $achievement)
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-md bg-success-transparent me-3">
                                            <i class="bi bi-trophy-fill text-success"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $achievement->name }}</h6>
                                            @if($achievement->description)
                                                <p class="text-muted small mb-0">{{ Str::limit($achievement->description, 60) }}</p>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted d-block">
                                                <i class="bi bi-clock me-1"></i>
                                                {{ $achievement->pivot->completed_at ? \Carbon\Carbon::parse($achievement->pivot->completed_at)->diffForHumans() : '-' }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-trophy fs-1 text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">لا توجد إنجازات بعد</p>
                                <a href="{{ route('student.gamification.achievements') }}" class="btn btn-sm btn-primary mt-3">
                                    عرض الإنجازات المتاحة
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-header border-bottom-0">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-lightning-charge me-2 text-warning"></i>
                            إجراءات سريعة
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('student.gamification.challenges') }}" class="card border text-center text-decoration-none h-100">
                                    <div class="card-body">
                                        <i class="bi bi-fire fs-2 text-danger mb-2 d-block"></i>
                                        <h6 class="mb-0">التحديات</h6>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('student.gamification.rewards') }}" class="card border text-center text-decoration-none h-100">
                                    <div class="card-body">
                                        <i class="bi bi-gift fs-2 text-primary mb-2 d-block"></i>
                                        <h6 class="mb-0">المكافآت</h6>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('student.gamification.certificates') }}" class="card border text-center text-decoration-none h-100">
                                    <div class="card-body">
                                        <i class="bi bi-file-earmark-pdf fs-2 text-info mb-2 d-block"></i>
                                        <h6 class="mb-0">الشهادات</h6>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('student.tasks.index') }}" class="card border text-center text-decoration-none h-100">
                                    <div class="card-body">
                                        <i class="bi bi-check2-square fs-2 text-success mb-2 d-block"></i>
                                        <h6 class="mb-0">المهام</h6>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('styles')
<style>
    /* إصلاح مشكلة الألوان في الوضع النهاري */
    [data-theme-mode="light"] .bg-gradient-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
    }
    
    [data-theme-mode="light"] .stats-text,
    [data-theme-mode="light"] .stats-number,
    [data-theme-mode="light"] .stats-label {
        color: #ffffff !important;
    }
    
    [data-theme-mode="dark"] .stats-text,
    [data-theme-mode="dark"] .stats-number,
    [data-theme-mode="dark"] .stats-label {
        color: #ffffff !important;
    }
    
    /* التأكد من أن النص واضح في جميع الأوضاع */
    .stats-text {
        color: #ffffff;
    }
    
    .stats-number {
        color: #ffffff !important;
        font-weight: bold;
    }
    
    .stats-label {
        color: rgba(255, 255, 255, 0.8) !important;
    }
    
    /* إصلاح خلفية الكارد في الوضع النهاري */
    [data-theme-mode="light"] .card.custom-card.bg-gradient-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
    }
    
    [data-theme-mode="light"] .card.custom-card.bg-gradient-primary .text-white,
    [data-theme-mode="light"] .card.custom-card.bg-gradient-primary .text-white-50 {
        color: #ffffff !important;
    }
</style>
@endpush

@section('scripts')
@parent
@stop

@section('scripts')
@parent
@stop
