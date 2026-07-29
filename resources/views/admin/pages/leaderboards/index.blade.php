@extends('admin.layouts.master')

@section('page-title')
    إدارة لوحة المتصدرين
@stop

@push('styles')
    @include('admin.pages.gamification.partials.gamification-styles')
@endpush

@section('content')
<div class="main-content app-content gami-page">
    <div class="container-fluid">

        @include('admin.pages.gamification.partials.hero', [
            'gamiTitle' => 'إدارة لوحة المتصدرين',
            'gamiIcon' => 'bi-graph-up-arrow',
            'gamiBreadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => route('admin.dashboard')],
                ['label' => 'نظام التحفيز', 'url' => route('admin.gamification.index')],
                ['label' => 'لوحة المتصدرين', 'active' => true],
            ],
            'gamiStatValue' => $leaderboards->count(),
            'gamiStatLabel' => 'لوحة',
            'gamiHeroActions' => '<a href="' . route('admin.leaderboards.create') . '" class="btn btn-sm btn-primary"><i class="bi bi-plus-circle me-1"></i> إضافة لوحة جديدة</a>',
        ])

        @include('partials.gamification-help-box', ['helpKey' => 'admin.leaderboards', 'showQueueStatus' => true])

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="gami-card gami-card--flush">
            <div class="gami-card__header">
                <div class="d-flex align-items-center gap-2">
                    <span class="gami-card__header-icon"><i class="bi bi-list-ul"></i></span>
                    <span>قائمة لوحات المتصدرين</span>
                </div>
            </div>
            <div class="gami-card__body">
                <div class="gami-table-wrap">
                    <table class="table gami-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>النوع</th>
                                <th>المادة</th>
                                <th>الفترة</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaderboards as $leaderboard)
                            <tr>
                                <td>{{ $leaderboard->id }}</td>
                                <td>
                                    <div class="gami-item-cell">
                                        <span class="gami-item-icon"><i class="bi bi-graph-up-arrow"></i></span>
                                        <span class="gami-item-name">{{ $leaderboard->name }}</span>
                                    </div>
                                </td>
                                <td><span class="gami-type-pill">{{ $leaderboard->type_name }}</span></td>
                                <td>{{ $leaderboard->subject->name ?? 'عام' }}</td>
                                <td>
                                    @if($leaderboard->period_start && $leaderboard->period_end)
                                        {{ $leaderboard->period_start->format('Y-m-d') }} - {{ $leaderboard->period_end->format('Y-m-d') }}
                                    @else
                                        دائم
                                    @endif
                                </td>
                                <td>
                                    @if($leaderboard->is_active)
                                        <span class="gami-status gami-status--active">نشط</span>
                                    @else
                                        <span class="gami-status gami-status--inactive">غير نشط</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="row-action-bar">
                                        <a href="{{ route('admin.leaderboards.edit', $leaderboard) }}" class="row-action-btn row-action-btn--primary" title="تعديل">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.leaderboards.refresh', $leaderboard) }}" method="POST" class="row-action-form">
                                            @csrf
                                            <button type="submit" class="row-action-btn row-action-btn--warning" title="تحديث اللوحة">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7">
                                    <div class="gami-empty">لا توجد لوحات متصدرين</div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@stop
