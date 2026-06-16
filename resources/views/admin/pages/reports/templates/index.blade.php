@extends('admin.layouts.master')

@section('page-title')
    قوالب التقارير
@stop

@push('styles')
    @include('admin.pages.reports.partials.reports-index-styles')
@endpush

@section('content')
    <div class="main-content app-content reports-index-page">
        <div class="container-fluid">

            <div class="reports-index-hero my-4">
                <div class="reports-index-hero__icon">
                    <i class="bi bi-file-earmark-ruled"></i>
                </div>
                <div class="reports-index-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">التقارير</a></li>
                            <li class="breadcrumb-item active" aria-current="page">قوالب التقارير</li>
                        </ol>
                    </nav>
                    <h4 class="reports-index-hero__title">قوالب التقارير</h4>
                    <p class="reports-index-hero__subtitle">عرض القوالب المتاحة وإنشاء تقارير منها</p>
                </div>
                <div class="reports-index-stat-mini">
                    <span class="reports-index-stat-mini__value">{{ number_format($templates->count()) }}</span>
                    <span class="reports-index-stat-mini__label">قالب</span>
                </div>
                <div class="reports-index-hero__actions">
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-right me-1"></i> رجوع للتقارير
                    </a>
                    <a href="{{ route('admin.reports.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> تقرير جديد
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="reports-index-card">
                <div class="reports-index-card__header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="reports-index-card__header-icon"><i class="bi bi-list-ul"></i></span>
                        <span>قائمة قوالب التقارير</span>
                    </div>
                </div>
                <div class="reports-index-card__body p-0">
                    <div class="reports-index-table-wrap">
                        <div class="table-responsive">
                            <table class="table reports-index-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>الاسم</th>
                                        <th>النوع</th>
                                        <th class="reports-col-desc">الوصف</th>
                                        <th>الحالة</th>
                                        <th class="reports-col-default">افتراضي</th>
                                        <th style="width: 100px;">إجراء</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($templates as $template)
                                        <tr>
                                            <td class="text-muted">{{ $template->id }}</td>
                                            <td>
                                                <strong>{{ $template->name }}</strong>
                                                @if($template->is_default)
                                                    <i class="bi bi-star-fill text-warning ms-1" title="قالب افتراضي"></i>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="reports-type-badge reports-type-badge--{{ $template->type === 'student' ? 'student' : ($template->type === 'course' ? 'course' : 'system') }}">
                                                    @if($template->type === 'student')
                                                        طالب
                                                    @elseif($template->type === 'course')
                                                        مادة
                                                    @else
                                                        نظام
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="reports-col-desc text-muted">{{ $template->description ?? '—' }}</td>
                                            <td>
                                                @if($template->is_active)
                                                    <span class="reports-status-badge reports-status-badge--active">
                                                        <i class="bi bi-check-circle"></i> نشط
                                                    </span>
                                                @else
                                                    <span class="reports-status-badge reports-status-badge--inactive">
                                                        <i class="bi bi-dash-circle"></i> غير نشط
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="reports-col-default text-center">
                                                @if($template->is_default)
                                                    <i class="bi bi-star-fill text-warning"></i>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="row-action-bar">
                                                    <a href="{{ route('admin.reports.create', ['template' => $template->id, 'type' => $template->type]) }}"
                                                       class="row-action-btn row-action-btn--primary"
                                                       title="إنشاء تقرير">
                                                        <i class="bi bi-file-earmark-plus"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7">
                                                <div class="reports-index-empty">
                                                    <i class="bi bi-file-earmark-text"></i>
                                                    <p class="mb-0">لا توجد قوالب تقارير</p>
                                                </div>
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
    </div>
@stop
