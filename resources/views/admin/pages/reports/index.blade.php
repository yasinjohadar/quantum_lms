@extends('admin.layouts.master')

@section('page-title')
    التقارير
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
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
                <a href="{{ route('admin.reports.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i>
                    إنشاء تقرير جديد
                </a>
                <a href="{{ route('admin.report-templates.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-file-earmark-text me-1"></i>
                    إدارة القوالب
                </a>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Filters -->
        <div class="card custom-card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">نوع التقرير</label>
                        <select name="type" class="form-select" onchange="this.form.submit()">
                            <option value="">جميع الأنواع</option>
                            <option value="student" {{ request('type') == 'student' ? 'selected' : '' }}>تقارير الطلاب</option>
                            <option value="course" {{ request('type') == 'course' ? 'selected' : '' }}>تقارير الكورسات</option>
                            <option value="system" {{ request('type') == 'system' ? 'selected' : '' }}>تقارير النظام</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Templates List -->
        <div class="card custom-card">
            <div class="card-header">
                <h5 class="mb-0">قوالب التقارير المتاحة</h5>
            </div>
            <div class="card-body">
                @if($templates->count() > 0)
                    <div class="row">
                        @foreach($templates as $template)
                            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-3">
                                <div class="card border h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-1 fw-semibold">{{ $template->name }}</h6>
                                                <span class="badge bg-{{ $template->type == 'student' ? 'primary' : ($template->type == 'course' ? 'success' : 'info') }}">
                                                    {{ $template->type_name }}
                                                </span>
                                                @if($template->is_default)
                                                    <span class="badge bg-warning">افتراضي</span>
                                                @endif
                                            </div>
                                        </div>
                                        @if($template->description)
                                            <p class="text-muted small mb-3">{{ \Illuminate\Support\Str::limit($template->description, 100) }}</p>
                                        @endif
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.reports.create', ['type' => $template->type, 'template' => $template->id]) }}" class="btn btn-sm btn-primary flex-grow-1">
                                                <i class="bi bi-eye me-1"></i>
                                                استخدام
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-file-earmark-text fs-1 text-muted mb-3 d-block"></i>
                        <h5 class="mb-2">لا توجد قوالب</h5>
                        <p class="text-muted">لم يتم إنشاء أي قوالب تقارير بعد</p>
                        <a href="{{ route('admin.report-templates.create') }}" class="btn btn-primary mt-3">
                            <i class="bi bi-plus-circle me-1"></i>
                            إنشاء قالب جديد
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

