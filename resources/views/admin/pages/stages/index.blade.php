@extends('admin.layouts.master')

@section('page-title')
    المراحل الدراسية
@stop

@section('css')
@stop

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">المراحل الدراسية</h5>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.stages.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> إضافة مرحلة جديدة
                    </a>
                </div>
            </div>
            <!-- Page Header Close -->

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

            <!-- Start::row-1 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <h5 class="mb-0 fw-bold">قائمة المراحل الدراسية</h5>

                            <form method="GET" action="{{ route('admin.stages.index') }}"
                                  class="d-flex flex-wrap gap-2 align-items-center">
                                <input type="text" name="query" class="form-control form-control-sm"
                                       placeholder="بحث باسم المرحلة أو الوصف"
                                       value="{{ request('query') }}" style="min-width: 220px;">

                                <select name="is_active" class="form-select form-select-sm" style="min-width: 150px;">
                                    <option value="">كل الحالات</option>
                                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>نشطة</option>
                                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>غير نشطة</option>
                                </select>

                                <button type="submit" class="btn btn-secondary btn-sm">
                                    بحث
                                </button>
                                <a href="{{ route('admin.stages.index') }}" class="btn btn-outline-danger btn-sm">
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
                                        <th style="min-width: 180px;">اسم المرحلة</th>
                                        <th style="min-width: 90px;">الترتيب</th>
                                        <th style="min-width: 100px;">الحالة</th>
                                        <th style="min-width: 160px;">تاريخ الإنشاء</th>
                                        <th style="min-width: 180px;">العمليات</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($stages as $stage)
                                        <tr>
                                            <td>{{ $loop->iteration + ($stages->currentPage() - 1) * $stages->perPage() }}</td>
                                            <td>
                                                <div class="d-flex justify-content-center">
                                                    <img src="{{ $stage->image ? asset('storage/' . $stage->image) : asset('assets/images/media/media-22.jpg') }}"
                                                         alt="{{ $stage->name }}"
                                                         class="rounded"
                                                         style="width: 60px; height: 60px; object-fit: cover;">
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.stages.show', $stage->id) }}" class="text-decoration-none fw-semibold">
                                                    {{ $stage->name }}
                                                </a>
                                            </td>
                                            <td>{{ $stage->order }}</td>
                                            <td>
                                                @if ($stage->is_active)
                                                    <span class="badge bg-success">نشطة</span>
                                                @else
                                                    <span class="badge bg-danger">غير نشطة</span>
                                                @endif
                                            </td>
                                            <td>{{ $stage->created_at?->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <a href="{{ route('admin.stages.show', $stage->id) }}"
                                                   class="btn btn-sm btn-info text-white">
                                                    <i class="fas fa-eye"></i> عرض
                                                </a>
                                                <a href="{{ route('admin.stages.edit', $stage->id) }}"
                                                   class="btn btn-sm btn-warning text-white">
                                                    <i class="fas fa-edit"></i> تعديل
                                                </a>
                                                <button type="button"
                                                        class="btn btn-sm btn-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteStage{{ $stage->id }}">
                                                    <i class="fas fa-trash-alt"></i> حذف
                                                </button>

                                                @include('admin.pages.stages.delete', ['stage' => $stage])
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-danger fw-bold">
                                                لا توجد مراحل مسجلة حالياً
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if ($stages instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                <div class="mt-3">
                                    {{ $stages->withQueryString()->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <!--End::row-1 -->
        </div>
    </div>
    <!-- End::app-content -->
@stop


