@extends('admin.layouts.master')

@section('page-title')
    التقارير
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">التقارير</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">التقارير</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.reports.templates') }}" class="btn btn-info btn-sm">
                    <i class="bi bi-file-earmark-text me-1"></i> إدارة القوالب
                </a>
                <a href="{{ route('admin.reports.create') }}" class="btn btn-primary btn-sm">
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

        <!-- فلترة حسب النوع -->
        <div class="card custom-card mb-3">
            <div class="card-body">
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.reports.index') }}" 
                       class="btn {{ !$type ? 'btn-primary' : 'btn-outline-primary' }} btn-sm">
                        <i class="bi bi-grid me-1"></i> جميع التقارير
                    </a>
                    <a href="{{ route('admin.reports.index', ['type' => 'student']) }}" 
                       class="btn {{ $type === 'student' ? 'btn-primary' : 'btn-outline-primary' }} btn-sm">
                        <i class="bi bi-person me-1"></i> تقارير الطلاب
                    </a>
                    <a href="{{ route('admin.reports.index', ['type' => 'course']) }}" 
                       class="btn {{ $type === 'course' ? 'btn-primary' : 'btn-outline-primary' }} btn-sm">
                        <i class="bi bi-book me-1"></i> تقارير المواد
                    </a>
                    <a href="{{ route('admin.reports.index', ['type' => 'system']) }}" 
                       class="btn {{ $type === 'system' ? 'btn-primary' : 'btn-outline-primary' }} btn-sm">
                        <i class="bi bi-gear me-1"></i> تقارير النظام
                    </a>
                </div>
            </div>
        </div>

        <!-- قائمة القوالب -->
        <div class="row">
            @forelse($templates as $template)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card custom-card h-100 shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">
                                @if($template->is_default)
                                    <i class="bi bi-star-fill text-warning me-1" title="قالب افتراضي"></i>
                                @endif
                                {{ $template->name }}
                            </h6>
                            <span class="badge bg-{{ $template->type === 'student' ? 'info' : ($template->type === 'course' ? 'success' : 'secondary') }}">
                                @if($template->type === 'student')
                                    طالب
                                @elseif($template->type === 'course')
                                    مادة
                                @else
                                    نظام
                                @endif
                            </span>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">
                                {{ $template->description ?? 'لا يوجد وصف' }}
                            </p>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('admin.reports.create', ['template' => $template->id, 'type' => $template->type]) }}" 
                                   class="btn btn-primary btn-sm flex-fill">
                                    <i class="bi bi-file-earmark-plus me-1"></i> إنشاء تقرير
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card custom-card">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-file-earmark-text display-4 text-muted d-block mb-3"></i>
                            <h5 class="text-muted mb-2">لا توجد قوالب تقارير متاحة</h5>
                            <p class="text-muted mb-4">
                                @if($type)
                                    لا توجد قوالب تقارير من نوع "{{ $type }}"
                                @else
                                    لا توجد قوالب تقارير نشطة في النظام
                                @endif
                            </p>
                            <a href="{{ route('admin.reports.templates') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i> إدارة القوالب
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@stop

