@extends('admin.layouts.master')

@section('page-title')
    لوحة التحكم - نظام التحفيز
@stop

@push('styles')
    @include('admin.pages.gamification.partials.gamification-styles')
@endpush

@section('content')
<div class="main-content app-content gami-page">
    <div class="container-fluid">

        @include('admin.pages.gamification.partials.hero', [
            'gamiTitle' => 'لوحة التحكم — نظام التحفيز',
            'gamiSubtitle' => 'نظرة عامة على النقاط والشارات والإنجازات والمستويات',
            'gamiIcon' => 'bi-trophy',
            'gamiBreadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => route('admin.dashboard')],
                ['label' => 'نظام التحفيز', 'active' => true],
            ],
            'gamiStatValue' => $stats['total_users_with_points'],
            'gamiStatLabel' => 'طالب بنقاط',
            'gamiHeroActions' => view('admin.pages.gamification.partials.dashboard-actions')->render(),
        ])

        @include('partials.gamification-help-box', ['helpKey' => 'admin.dashboard', 'showQueueStatus' => true])

        <div class="gami-stat-grid">
            <div class="gami-stat-card gami-stat-card--primary">
                <span class="gami-stat-card__icon"><i class="bi bi-coin"></i></span>
                <div class="gami-stat-card__label">إجمالي النقاط</div>
                <div class="gami-stat-card__value">{{ number_format($stats['total_points']) }}</div>
            </div>
            <div class="gami-stat-card gami-stat-card--success">
                <span class="gami-stat-card__icon"><i class="bi bi-award"></i></span>
                <div class="gami-stat-card__label">الشارات النشطة</div>
                <div class="gami-stat-card__value">{{ number_format($stats['total_badges']) }}</div>
            </div>
            <div class="gami-stat-card gami-stat-card--info">
                <span class="gami-stat-card__icon"><i class="bi bi-star"></i></span>
                <div class="gami-stat-card__label">الإنجازات النشطة</div>
                <div class="gami-stat-card__value">{{ number_format($stats['total_achievements']) }}</div>
            </div>
            <div class="gami-stat-card gami-stat-card--warning">
                <span class="gami-stat-card__icon"><i class="bi bi-bar-chart-steps"></i></span>
                <div class="gami-stat-card__label">المستويات</div>
                <div class="gami-stat-card__value">{{ number_format($stats['total_levels']) }}</div>
            </div>
        </div>

        <div class="gami-card">
            <div class="gami-card__header">
                <div class="d-flex align-items-center gap-2">
                    <span class="gami-card__header-icon"><i class="bi bi-grid"></i></span>
                    <span>روابط سريعة</span>
                </div>
            </div>
            <div class="gami-card__body">
                <div class="gami-quick-grid">
                    @can('gamification-update')
                        <a href="{{ route('admin.gamification.settings') }}" class="gami-quick-link">
                            <span class="gami-quick-link__icon gami-quick-link__icon--primary"><i class="bi bi-gear"></i></span>
                            <span>الإعدادات</span>
                        </a>
                        <a href="{{ route('admin.gamification.rules') }}" class="gami-quick-link">
                            <span class="gami-quick-link__icon gami-quick-link__icon--info"><i class="bi bi-journal-code"></i></span>
                            <span>قواعد النقاط</span>
                        </a>
                    @endcan
                    @can('badge-list')
                        <a href="{{ route('admin.badges.index') }}" class="gami-quick-link">
                            <span class="gami-quick-link__icon gami-quick-link__icon--success"><i class="bi bi-award"></i></span>
                            <span>إدارة الشارات</span>
                        </a>
                    @endcan
                    @can('achievement-list')
                        <a href="{{ route('admin.achievements.index') }}" class="gami-quick-link">
                            <span class="gami-quick-link__icon gami-quick-link__icon--info"><i class="bi bi-star"></i></span>
                            <span>إدارة الإنجازات</span>
                        </a>
                    @endcan
                    @can('level-list')
                        <a href="{{ route('admin.levels.index') }}" class="gami-quick-link">
                            <span class="gami-quick-link__icon gami-quick-link__icon--warning"><i class="bi bi-bar-chart-steps"></i></span>
                            <span>إدارة المستويات</span>
                        </a>
                    @endcan
                    @can('challenge-list')
                        <a href="{{ route('admin.challenges.index') }}" class="gami-quick-link">
                            <span class="gami-quick-link__icon gami-quick-link__icon--purple"><i class="bi bi-lightning"></i></span>
                            <span>التحديات</span>
                        </a>
                    @endcan
                    @can('reward-list')
                        <a href="{{ route('admin.rewards.index') }}" class="gami-quick-link">
                            <span class="gami-quick-link__icon gami-quick-link__icon--rose"><i class="bi bi-gift"></i></span>
                            <span>المكافآت</span>
                        </a>
                    @endcan
                    @can('leaderboard-list')
                        <a href="{{ route('admin.leaderboards.index') }}" class="gami-quick-link">
                            <span class="gami-quick-link__icon gami-quick-link__icon--warning"><i class="bi bi-graph-up-arrow"></i></span>
                            <span>لوحة المتصدرين</span>
                        </a>
                    @endcan
                    @can('daily-task-list')
                        <a href="{{ route('admin.daily-tasks.index') }}" class="gami-quick-link">
                            <span class="gami-quick-link__icon gami-quick-link__icon--primary"><i class="bi bi-calendar-day"></i></span>
                            <span>المهام اليومية</span>
                        </a>
                    @endcan
                    @can('weekly-task-list')
                        <a href="{{ route('admin.weekly-tasks.index') }}" class="gami-quick-link">
                            <span class="gami-quick-link__icon gami-quick-link__icon--purple"><i class="bi bi-calendar-week"></i></span>
                            <span>المهام الأسبوعية</span>
                        </a>
                    @endcan
                </div>
            </div>
        </div>

    </div>
</div>
@stop
