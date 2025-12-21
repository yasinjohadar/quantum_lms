@extends('admin.layouts.master')

@section('page-title')
    إدارة قوالب التقارير
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إدارة قوالب التقارير</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">التقارير</a></li>
                        <li class="breadcrumb-item active" aria-current="page">القوالب</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.report-templates.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i>
                إنشاء قالب جديد
            </a>
        </div>
        <!-- End Page Header -->

        <div class="card custom-card">
            <div class="card-body">
                @if($templates->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>الاسم</th>
                                    <th>النوع</th>
                                    <th>الحالة</th>
                                    <th>الافتراضي</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($templates as $template)
                                    <tr>
                                        <td>
                                            <h6 class="mb-0">{{ $template->name }}</h6>
                                            @if($template->description)
                                                <small class="text-muted">{{ \Illuminate\Support\Str::limit($template->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $template->type == 'student' ? 'primary' : ($template->type == 'course' ? 'success' : 'info') }}">
                                                {{ $template->type_name }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($template->is_active)
                                                <span class="badge bg-success">نشط</span>
                                            @else
                                                <span class="badge bg-secondary">غير نشط</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($template->is_default)
                                                <span class="badge bg-warning">افتراضي</span>
                                            @else
                                                <form action="{{ route('admin.report-templates.set-default', $template->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-warning">تعيين كافتراضي</button>
                                                </form>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('admin.report-templates.show', $template->id) }}" class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.report-templates.edit', $template->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('admin.report-templates.duplicate', $template->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-secondary" title="نسخ">
                                                        <i class="bi bi-files"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.report-templates.destroy', $template->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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

