@extends('admin.layouts.master')

@section('page-title')
    إدارة الشارات
@stop

@push('styles')
    @include('admin.pages.gamification.partials.gamification-styles')
@endpush

@section('content')
<div class="main-content app-content gami-page">
    <div class="container-fluid">

        @include('admin.pages.gamification.partials.hero', [
            'gamiTitle' => 'إدارة الشارات',
            'gamiIcon' => 'bi-award',
            'gamiBreadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => route('admin.dashboard')],
                ['label' => 'نظام التحفيز', 'url' => route('admin.gamification.index')],
                ['label' => 'الشارات', 'active' => true],
            ],
            'gamiStatValue' => $badges->count(),
            'gamiStatLabel' => 'شارة',
            'gamiHeroActions' => '<a href="' . route('admin.badges.create') . '" class="btn btn-sm btn-primary"><i class="bi bi-plus-circle me-1"></i> إضافة شارة جديدة</a>',
        ])

        @include('partials.gamification-help-box', ['helpKey' => 'admin.badges', 'showQueueStatus' => true])

        <div class="gami-card gami-card--flush">
            <div class="gami-card__header">
                <div class="d-flex align-items-center gap-2">
                    <span class="gami-card__header-icon"><i class="bi bi-list-ul"></i></span>
                    <span>قائمة الشارات</span>
                </div>
            </div>
            <div class="gami-card__body">
                <div class="gami-table-wrap">
                    <table class="table gami-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>الوصف</th>
                                <th>النقاط المطلوبة</th>
                                <th>تلقائي</th>
                                <th>الحالة</th>
                                <th>الترتيب</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($badges as $badge)
                            <tr>
                                <td>{{ $badge->id }}</td>
                                <td>
                                    <div class="gami-item-cell">
                                        <span class="gami-item-icon" style="color: {{ $badge->color ?? '#007bff' }};">
                                            @if($badge->icon)
                                                <i class="{{ $badge->icon }}"></i>
                                            @else
                                                <i class="bi bi-award"></i>
                                            @endif
                                        </span>
                                        <span class="gami-item-name">{{ $badge->name }}</span>
                                    </div>
                                </td>
                                <td>{{ Str::limit($badge->description, 50) }}</td>
                                <td>{{ $badge->points_required }}</td>
                                <td>
                                    @if($badge->is_automatic)
                                        <span class="gami-status gami-status--yes">نعم</span>
                                    @else
                                        <span class="gami-status gami-status--no">لا</span>
                                    @endif
                                </td>
                                <td>
                                    @if($badge->is_active)
                                        <span class="gami-status gami-status--active">نشط</span>
                                    @else
                                        <span class="gami-status gami-status--inactive">غير نشط</span>
                                    @endif
                                </td>
                                <td>{{ $badge->order }}</td>
                                <td>
                                    <div class="row-action-bar">
                                        <a href="{{ route('admin.badges.edit', $badge) }}" class="row-action-btn row-action-btn--primary" title="تعديل">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.badges.destroy', $badge) }}" method="POST" class="row-action-form" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
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
                                <td colspan="8">
                                    <div class="gami-empty">لا توجد شارات</div>
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
