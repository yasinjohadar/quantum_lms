@extends('admin.layouts.master')

@section('page-title')
    التقارير
@stop

@push('styles')
    @include('admin.pages.reports.partials.reports-index-styles')
@endpush

@section('content')
    <div class="main-content app-content reports-index-page">
        <div class="container-fluid">

            <div class="reports-index-hero my-4">
                <div class="reports-index-hero__icon">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                </div>
                <div class="reports-index-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">التقارير</li>
                        </ol>
                    </nav>
                    <h4 class="reports-index-hero__title">التقارير</h4>
                    <p class="reports-index-hero__subtitle">إنشاء وتصدير تقارير الطلاب والمواد والنظام من القوالب النشطة</p>
                </div>
                <div class="reports-index-stat-mini">
                    <span class="reports-index-stat-mini__value">{{ number_format($templates->count()) }}</span>
                    <span class="reports-index-stat-mini__label">قالب متاح</span>
                </div>
                <div class="reports-index-hero__actions">
                    <a href="{{ route('admin.reports.templates') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-file-earmark-text me-1"></i> إدارة القوالب
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
                        <span class="reports-index-card__header-icon"><i class="bi bi-funnel"></i></span>
                        <span>تصفية حسب النوع</span>
                    </div>
                </div>
                <div class="reports-index-card__body py-3">
                    <div class="reports-type-tabs">
                        <a href="{{ route('admin.reports.index') }}"
                           class="reports-type-tab {{ !$type ? 'is-active' : '' }}">
                            <i class="bi bi-grid"></i> جميع التقارير
                        </a>
                        <a href="{{ route('admin.reports.index', ['type' => 'student']) }}"
                           class="reports-type-tab {{ $type === 'student' ? 'is-active' : '' }}">
                            <i class="bi bi-person"></i> تقارير الطلاب
                        </a>
                        <a href="{{ route('admin.reports.index', ['type' => 'course']) }}"
                           class="reports-type-tab {{ $type === 'course' ? 'is-active' : '' }}">
                            <i class="bi bi-book"></i> تقارير المواد
                        </a>
                        <a href="{{ route('admin.reports.index', ['type' => 'system']) }}"
                           class="reports-type-tab {{ $type === 'system' ? 'is-active' : '' }}">
                            <i class="bi bi-gear"></i> تقارير النظام
                        </a>
                    </div>
                </div>
            </div>

            @if($templates->isNotEmpty())
                <div class="reports-templates-grid">
                    @foreach($templates as $template)
                        <article class="reports-template-card">
                            <div class="reports-template-card__header">
                                <h6 class="reports-template-card__title">
                                    @if($template->is_default)
                                        <i class="bi bi-star-fill text-warning me-1" title="قالب افتراضي"></i>
                                    @endif
                                    {{ $template->name }}
                                </h6>
                                <span class="reports-type-badge reports-type-badge--{{ $template->type === 'student' ? 'student' : ($template->type === 'course' ? 'course' : 'system') }}">
                                    @if($template->type === 'student')
                                        طالب
                                    @elseif($template->type === 'course')
                                        مادة
                                    @else
                                        نظام
                                    @endif
                                </span>
                            </div>
                            <div class="reports-template-card__body">
                                <p class="reports-template-card__desc">
                                    {{ $template->description ?? 'لا يوجد وصف' }}
                                </p>
                                <a href="{{ route('admin.reports.create', ['template' => $template->id, 'type' => $template->type]) }}"
                                   class="btn btn-sm btn-primary w-100">
                                    <i class="bi bi-file-earmark-plus me-1"></i> إنشاء تقرير
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="reports-index-card">
                    <div class="reports-index-empty">
                        <i class="bi bi-file-earmark-text"></i>
                        <h5 class="mb-2">لا توجد قوالب تقارير متاحة</h5>
                        <p class="mb-4">
                            @if($type)
                                لا توجد قوالب تقارير نشطة من نوع
                                @if($type === 'student') الطلاب
                                @elseif($type === 'course') المواد
                                @else النظام
                                @endif
                            @else
                                لا توجد قوالب تقارير نشطة في النظام
                            @endif
                        </p>
                        <a href="{{ route('admin.reports.templates') }}" class="btn btn-primary">
                            <i class="bi bi-gear me-1"></i> إدارة القوالب
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>
@stop
