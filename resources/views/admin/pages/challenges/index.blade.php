@extends('admin.layouts.master')

@section('page-title')
    إدارة التحديات
@stop

@push('styles')
    @include('admin.pages.gamification.partials.gamification-styles')
@endpush

@section('content')
<div class="main-content app-content gami-page">
    <div class="container-fluid">

        @include('admin.pages.gamification.partials.hero', [
            'gamiTitle' => 'إدارة التحديات',
            'gamiIcon' => 'bi-lightning',
            'gamiBreadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => route('admin.dashboard')],
                ['label' => 'نظام التحفيز', 'url' => route('admin.gamification.index')],
                ['label' => 'التحديات', 'active' => true],
            ],
            'gamiStatValue' => $challenges->count(),
            'gamiStatLabel' => 'تحدٍ',
            'gamiHeroActions' => '<a href="' . route('admin.challenges.create') . '" class="btn btn-sm btn-primary"><i class="bi bi-plus-circle me-1"></i> إضافة تحدٍ جديد</a>',
        ])

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
                    <span>قائمة التحديات</span>
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
                                <th>تاريخ البداية</th>
                                <th>تاريخ النهاية</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($challenges as $challenge)
                            <tr>
                                <td>{{ $challenge->id }}</td>
                                <td>
                                    <div class="gami-item-cell">
                                        <span class="gami-item-icon"><i class="bi bi-lightning"></i></span>
                                        <span class="gami-item-name">{{ $challenge->name }}</span>
                                    </div>
                                </td>
                                <td><span class="gami-type-pill">{{ $challenge->type_name }}</span></td>
                                <td>{{ $challenge->start_date->format('Y-m-d') }}</td>
                                <td>{{ $challenge->end_date->format('Y-m-d') }}</td>
                                <td>
                                    @if($challenge->is_active)
                                        <span class="gami-status gami-status--active">نشط</span>
                                    @else
                                        <span class="gami-status gami-status--inactive">غير نشط</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="row-action-bar">
                                        <a href="{{ route('admin.challenges.edit', $challenge) }}" class="row-action-btn row-action-btn--primary" title="تعديل">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.challenges.destroy', $challenge) }}" method="POST" class="row-action-form" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
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
                                <td colspan="7">
                                    <div class="gami-empty">لا توجد تحديات</div>
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
