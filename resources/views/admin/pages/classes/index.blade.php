@extends('admin.layouts.master')

@section('page-title')
    الصفوف الدراسية
@stop

@section('css')
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">الصفوف الدراسية</h5>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.classes.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> إضافة صف جديد
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li class="small">{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-xl-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <h5 class="mb-0 fw-bold">قائمة الصفوف الدراسية</h5>

                            <form method="GET" action="{{ route('admin.classes.index') }}"
                                  class="d-flex flex-wrap gap-2 align-items-center">
                                <input type="text" name="query" class="form-control form-control-sm"
                                       placeholder="بحث باسم الصف أو الوصف"
                                       value="{{ request('query') }}" style="min-width: 220px;">

                                <select name="stage_id" class="form-select form-select-sm" style="min-width: 160px;">
                                    <option value="">كل المراحل</option>
                                    @foreach($stages as $stage)
                                        <option value="{{ $stage->id }}" {{ request('stage_id') == $stage->id ? 'selected' : '' }}>
                                            {{ $stage->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <select name="is_active" class="form-select form-select-sm" style="min-width: 150px;">
                                    <option value="">كل الحالات</option>
                                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>نشط</option>
                                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>غير نشط</option>
                                </select>

                                <button type="submit" class="btn btn-secondary btn-sm">
                                    بحث
                                </button>
                                <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-danger btn-sm">
                                    مسح الفلاتر
                                </a>
                            </form>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle table-hover table-bordered mb-0 text-center">
                                    <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th style="min-width: 140px;">الصورة</th>
                                        <th style="min-width: 180px;">اسم الصف</th>
                                        <th style="min-width: 180px;">المرحلة</th>
                                        <th style="min-width: 90px;">الترتيب</th>
                                        <th style="min-width: 100px;">الحالة</th>
                                        <th style="min-width: 160px;">تاريخ الإنشاء</th>
                                        <th style="min-width: 200px;">العمليات</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($classes as $class)
                                        <tr>
                                            <td>{{ $loop->iteration + ($classes->currentPage() - 1) * $classes->perPage() }}</td>
                                            <td>
                                                <div class="d-flex justify-content-center">
                                                    <img src="{{ $class->image ? asset('storage/' . $class->image) : asset('assets/images/media/media-22.jpg') }}"
                                                         alt="{{ $class->name }}"
                                                         class="rounded"
                                                         style="width: 60px; height: 60px; object-fit: cover;">
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.classes.show', $class->id) }}" class="text-decoration-none fw-semibold">
                                                    {{ $class->name }}
                                                </a>
                                            </td>
                                            <td>{{ $class->stage?->name ?? '-' }}</td>
                                            <td>{{ $class->order }}</td>
                                            <td>
                                                @if ($class->is_active)
                                                    <span class="badge bg-success">نشط</span>
                                                @else
                                                    <span class="badge bg-danger">غير نشط</span>
                                                @endif
                                            </td>
                                            <td>{{ $class->created_at?->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <a href="{{ route('admin.classes.show', $class->id) }}"
                                                   class="btn btn-sm btn-info text-white">
                                                    <i class="fas fa-eye"></i> عرض
                                                </a>
                                                <a href="{{ route('admin.classes.edit', $class->id) }}"
                                                   class="btn btn-sm btn-warning text-white">
                                                    <i class="fas fa-edit"></i> تعديل
                                                </a>
                                                <button type="button"
                                                        class="btn btn-sm btn-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteClass{{ $class->id }}">
                                                    <i class="fas fa-trash-alt"></i> حذف
                                                </button>

                                                @include('admin.pages.classes.delete', ['class' => $class])
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-danger fw-bold">
                                                لا توجد صفوف مسجلة حالياً
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if ($classes instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                <div class="mt-3">
                                    {{ $classes->withQueryString()->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop


