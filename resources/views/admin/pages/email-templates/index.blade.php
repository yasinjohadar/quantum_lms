@extends('admin.layouts.master')

@section('page-title')
    قوالب الإيميلات
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">قوالب الإيميلات</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">قوالب الإيميلات</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('admin.email-templates.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> قالب جديد
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header">
                <h5 class="card-title mb-0">قائمة القوالب</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped text-nowrap">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>المعرف</th>
                                <th>الموضوع</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $template)
                                <tr>
                                    <td>{{ $template->id }}</td>
                                    <td>{{ $template->name }}</td>
                                    <td><code>{{ $template->slug }}</code></td>
                                    <td>{{ \Illuminate\Support\Str::limit($template->subject, 50) }}</td>
                                    <td>
                                        @if($template->is_active)
                                            <span class="badge bg-success">نشط</span>
                                        @else
                                            <span class="badge bg-secondary">غير نشط</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.email-templates.edit', $template) }}" class="btn btn-sm btn-primary" title="تعديل">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.email-templates.destroy', $template) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">لا توجد قوالب</td>
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

