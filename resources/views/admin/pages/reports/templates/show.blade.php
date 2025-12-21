@extends('admin.layouts.master')

@section('page-title')
    {{ $template->name }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">{{ $template->name }}</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">التقارير</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.report-templates.index') }}">القوالب</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $template->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.report-templates.edit', $template->id) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-pencil me-1"></i>
                    تعديل
                </a>
                <a href="{{ route('admin.report-templates.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-right me-1"></i>
                    العودة
                </a>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <div class="col-xl-8 col-lg-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <h5 class="mb-0">معلومات القالب</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted">الاسم</label>
                            <h6 class="mb-0">{{ $template->name }}</h6>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted">النوع</label>
                            <div>
                                <span class="badge bg-{{ $template->type == 'student' ? 'primary' : ($template->type == 'course' ? 'success' : 'info') }}">
                                    {{ $template->type_name }}
                                </span>
                            </div>
                        </div>

                        @if($template->description)
                            <div class="mb-3">
                                <label class="form-label text-muted">الوصف</label>
                                <p class="mb-0">{{ $template->description }}</p>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label text-muted">الحالة</label>
                            <div>
                                @if($template->is_active)
                                    <span class="badge bg-success">نشط</span>
                                @else
                                    <span class="badge bg-secondary">غير نشط</span>
                                @endif
                                @if($template->is_default)
                                    <span class="badge bg-warning">افتراضي</span>
                                @endif
                            </div>
                        </div>

                        @if($template->config)
                            <div class="mb-3">
                                <label class="form-label text-muted">الإعدادات</label>
                                <pre class="bg-light p-3 rounded"><code>{{ json_encode($template->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                            </div>
                        @endif

                        @if($template->creator)
                            <div class="mb-3">
                                <label class="form-label text-muted">أنشئ بواسطة</label>
                                <p class="mb-0">{{ $template->creator->name }}</p>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label text-muted">تاريخ الإنشاء</label>
                            <p class="mb-0">{{ $template->created_at->format('Y-m-d H:i') }}</p>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <a href="{{ route('admin.reports.create', ['type' => $template->type, 'template' => $template->id]) }}" class="btn btn-primary">
                                <i class="bi bi-eye me-1"></i>
                                استخدام القالب
                            </a>
                            <a href="{{ route('admin.report-templates.edit', $template->id) }}" class="btn btn-info">
                                <i class="bi bi-pencil me-1"></i>
                                تعديل
                            </a>
                            <form action="{{ route('admin.report-templates.duplicate', $template->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-secondary">
                                    <i class="bi bi-files me-1"></i>
                                    نسخ
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

