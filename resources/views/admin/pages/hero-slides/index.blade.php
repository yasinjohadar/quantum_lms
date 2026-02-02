@extends('admin.layouts.master')

@section('page-title')
    شرائح Hero (سلايدر الصفحة الرئيسية)
@stop

@section('css')
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">شرائح Hero</h5>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.hero-slides.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> إضافة شريحة جديدة
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

            <div class="row">
                <div class="col-xl-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <h5 class="mb-0 fw-bold">قائمة شرائح Hero</h5>

                            <form method="GET" action="{{ route('admin.hero-slides.index') }}"
                                  class="d-flex flex-wrap gap-2 align-items-center">
                                <input type="text" name="query" class="form-control form-control-sm"
                                       placeholder="بحث بالعنوان أو الوصف"
                                       value="{{ request('query') }}" style="min-width: 220px;">

                                <select name="is_active" class="form-select form-select-sm" style="min-width: 150px;">
                                    <option value="">كل الحالات</option>
                                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>نشطة</option>
                                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>غير نشطة</option>
                                </select>

                                <button type="submit" class="btn btn-secondary btn-sm">بحث</button>
                                <a href="{{ route('admin.hero-slides.index') }}" class="btn btn-outline-danger btn-sm">مسح الفلاتر</a>
                            </form>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle table-hover table-bordered mb-0 text-center">
                                    <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th style="min-width: 100px;">الصورة</th>
                                        <th style="min-width: 160px;">العنوان</th>
                                        <th style="min-width: 90px;">الترتيب</th>
                                        <th style="min-width: 90px;">الحالة</th>
                                        <th style="min-width: 180px;">العمليات</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($slides as $slide)
                                        <tr>
                                            <td>{{ $loop->iteration + ($slides->currentPage() - 1) * $slides->perPage() }}</td>
                                            <td>
                                                <div class="d-flex justify-content-center">
                                                    @if ($slide->background_image)
                                                        <img src="{{ asset('storage/' . $slide->background_image) }}"
                                                             alt="{{ $slide->title }}"
                                                             class="rounded"
                                                             style="width: 60px; height: 40px; object-fit: cover;"
                                                             onerror="this.src='{{ asset('assets/images/media/media-22.jpg') }}'">
                                                    @else
                                                        <span class="text-muted small">—</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-start">{{ Str::limit($slide->title, 40) }}</td>
                                            <td>{{ $slide->order }}</td>
                                            <td>
                                                @if ($slide->is_active)
                                                    <span class="badge bg-success">نشطة</span>
                                                @else
                                                    <span class="badge bg-danger">غير نشطة</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.hero-slides.edit', $slide) }}"
                                                   class="btn btn-sm btn-warning text-white">
                                                    <i class="fas fa-edit"></i> تعديل
                                                </a>
                                                <form action="{{ route('admin.hero-slides.destroy', $slide) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('هل أنت متأكد من حذف هذه الشريحة؟');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash-alt"></i> حذف
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-danger fw-bold">
                                                لا توجد شرائح مسجلة. أضف شريحة من زر إضافة شريحة جديدة.
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if ($slides->hasPages())
                                <div class="mt-3">
                                    {{ $slides->withQueryString()->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
