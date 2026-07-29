@extends('admin.layouts.master')

@section('page-title')
    إدارة المستويات
@stop

@push('styles')
    @include('admin.pages.gamification.partials.gamification-styles')
@endpush

@section('content')
<div class="main-content app-content gami-page">
    <div class="container-fluid">

        @include('admin.pages.gamification.partials.hero', [
            'gamiTitle' => 'إدارة المستويات',
            'gamiIcon' => 'bi-bar-chart-steps',
            'gamiBreadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => route('admin.dashboard')],
                ['label' => 'نظام التحفيز', 'url' => route('admin.gamification.index')],
                ['label' => 'المستويات', 'active' => true],
            ],
            'gamiStatValue' => $levels->count(),
            'gamiStatLabel' => 'مستوى',
            'gamiHeroActions' => '<a href="' . route('admin.levels.create') . '" class="btn btn-sm btn-primary"><i class="bi bi-plus-circle me-1"></i> إضافة مستوى جديد</a>',
        ])

        @include('partials.gamification-help-box', ['helpKey' => 'admin.levels', 'showQueueStatus' => true])

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
                    <span>قائمة المستويات</span>
                </div>
            </div>
            <div class="gami-card__body">
                <div class="gami-table-wrap">
                    <table class="table gami-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>رقم المستوى</th>
                                <th>النقاط المطلوبة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($levels as $level)
                            <tr>
                                <td>{{ $level->id }}</td>
                                <td>
                                    <div class="gami-item-cell">
                                        @if($level->icon)
                                            <span class="gami-item-icon" style="color: {{ $level->color ?? '#007bff' }};">
                                                <i class="{{ $level->icon }}"></i>
                                            </span>
                                        @endif
                                        <span class="gami-item-name">{{ $level->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $level->level_number }}</td>
                                <td>{{ number_format($level->points_required) }}</td>
                                <td>
                                    <div class="row-action-bar">
                                        <a href="{{ route('admin.levels.edit', $level) }}" class="row-action-btn row-action-btn--primary" title="تعديل">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.levels.destroy', $level) }}" method="POST" class="row-action-form" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="row-action-btn row-action-btn--danger" title="حذف">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5">
                                    <div class="gami-empty">لا توجد مستويات</div>
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
