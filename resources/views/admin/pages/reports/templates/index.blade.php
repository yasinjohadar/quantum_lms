@extends('admin.layouts.master')

@section('page-title')
    قوالب التقارير
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">قوالب التقارير</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">التقارير</a></li>
                        <li class="breadcrumb-item active" aria-current="page">قوالب التقارير</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-right me-1"></i> رجوع
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

        <div class="card custom-card">
            <div class="card-header">
                <h5 class="mb-0 fw-bold">قائمة قوالب التقارير</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle table-hover table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>الاسم</th>
                                <th>النوع</th>
                                <th>الوصف</th>
                                <th>الحالة</th>
                                <th>افتراضي</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $template)
                                <tr>
                                    <td>{{ $template->id }}</td>
                                    <td>
                                        <strong>{{ $template->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $template->type === 'student' ? 'info' : ($template->type === 'course' ? 'success' : 'secondary') }}">
                                            @if($template->type === 'student')
                                                طالب
                                            @elseif($template->type === 'course')
                                                مادة
                                            @else
                                                نظام
                                            @endif
                                        </span>
                                    </td>
                                    <td>{{ $template->description ?? '-' }}</td>
                                    <td>
                                        @if($template->is_active)
                                            <span class="badge bg-success">نشط</span>
                                        @else
                                            <span class="badge bg-secondary">غير نشط</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($template->is_default)
                                            <i class="bi bi-star-fill text-warning"></i>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.reports.create', ['template' => $template->id, 'type' => $template->type]) }}" 
                                               class="btn btn-sm btn-primary" title="إنشاء تقرير">
                                                <i class="bi bi-file-earmark-plus"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="bi bi-file-earmark-text display-4 text-muted d-block mb-3"></i>
                                        <p class="text-muted">لا توجد قوالب تقارير</p>
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

