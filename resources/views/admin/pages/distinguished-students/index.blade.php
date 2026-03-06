@extends('admin.layouts.master')

@section('page-title')
    الطلاب المتميزون
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">الطلاب المتميزون</h5>
                    <div class="small text-muted">عرض في الصفحة الرئيسية بسلايدر</div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.distinguished-students.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> إضافة طالب متميز
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
                            <h5 class="mb-0 fw-bold">قائمة الطلاب المتميزين</h5>

                            <form method="GET" action="{{ route('admin.distinguished-students.index') }}"
                                  class="d-flex flex-wrap gap-2 align-items-center">
                                <input type="text" name="query" class="form-control form-control-sm"
                                       placeholder="بحث بالاسم أو الرأي"
                                       value="{{ request('query') }}" style="min-width: 200px;">

                                <select name="is_active" class="form-select form-select-sm" style="min-width: 130px;">
                                    <option value="">كل الحالات</option>
                                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>نشط</option>
                                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>غير نشط</option>
                                </select>

                                <button type="submit" class="btn btn-secondary btn-sm">بحث</button>
                                <a href="{{ route('admin.distinguished-students.index') }}" class="btn btn-outline-danger btn-sm">مسح</a>
                            </form>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle table-hover table-bordered mb-0 text-center">
                                    <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th style="width: 70px;">الصورة</th>
                                        <th style="min-width: 120px;">الطالب</th>
                                        <th style="min-width: 120px;">الصف</th>
                                        <th style="min-width: 180px;">رأي الطالب</th>
                                        <th style="width: 80px;">الترتيب</th>
                                        <th style="width: 90px;">الحالة</th>
                                        <th style="min-width: 160px;">العمليات</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($items as $item)
                                        <tr>
                                            <td>{{ $loop->iteration + ($items->currentPage() - 1) * $items->perPage() }}</td>
                                            <td>
                                                @if ($item->photo)
                                                    <img src="{{ asset('storage/' . $item->photo) }}" alt="{{ $item->user->name ?? '' }}"
                                                         class="rounded-circle" style="width: 44px; height: 44px; object-fit: cover;"
                                                         onerror="this.src='{{ asset('assets/images/users/avatar.svg') }}'">
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                            <td class="text-start">{{ $item->user->name ?? '—' }}</td>
                                            <td class="text-start">{{ $item->schoolClass->name ?? '—' }}</td>
                                            <td class="text-start small">{{ Str::limit($item->quote, 60) }}</td>
                                            <td>{{ $item->order }}</td>
                                            <td>
                                                @if ($item->is_active)
                                                    <span class="badge bg-success">نشط</span>
                                                @else
                                                    <span class="badge bg-secondary">غير نشط</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.distinguished-students.edit', $item) }}"
                                                   class="btn btn-sm btn-warning text-white">
                                                    <i class="fas fa-edit"></i> تعديل
                                                </a>
                                                <form action="{{ route('admin.distinguished-students.destroy', $item) }}"
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل؟');">
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
                                            <td colspan="8" class="text-center text-muted py-4">
                                                لا يوجد طلاب متميزون. أضف طالباً من زر «إضافة طالب متميز».
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if ($items->hasPages())
                                <div class="mt-3">
                                    {{ $items->withQueryString()->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
