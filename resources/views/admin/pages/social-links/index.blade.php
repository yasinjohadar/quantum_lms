@extends('admin.layouts.master')

@section('page-title')
    روابط التواصل الاجتماعي
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">روابط التواصل الاجتماعي</h5>
                    <div class="small text-muted">إدارة روابط وسائل التواصل المعروضة في الهيدر والفوتر — يمكنك إضافة أي منصة</div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.social-links.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> إضافة رابط
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
                        <div class="card-header">
                            <h5 class="mb-0 fw-bold">قائمة الروابط</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle table-hover table-bordered mb-0 text-center">
                                    <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th style="width: 70px;">الأيقونة</th>
                                        <th style="min-width: 120px;">الاسم</th>
                                        <th>الرابط</th>
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
                                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light" style="width: 40px; height: 40px;">
                                                    <i class="{{ $item->icon_class }} fa-lg text-primary"></i>
                                                </span>
                                            </td>
                                            <td class="text-start">{{ $item->name }}</td>
                                            <td class="text-start small text-break">{{ Str::limit($item->url, 50) }}</td>
                                            <td>{{ $item->sort_order }}</td>
                                            <td>
                                                @if ($item->is_active)
                                                    <span class="badge bg-success">نشط</span>
                                                @else
                                                    <span class="badge bg-secondary">غير نشط</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.social-links.edit', $item) }}"
                                                   class="btn btn-sm btn-warning text-white">
                                                    <i class="fas fa-edit"></i> تعديل
                                                </a>
                                                <form action="{{ route('admin.social-links.destroy', $item) }}"
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا الرابط؟');">
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
                                            <td colspan="7" class="text-center text-muted py-4">
                                                لا توجد روابط. أضف رابطاً من زر «إضافة رابط».
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if ($items->hasPages())
                                <div class="mt-3">
                                    {{ $items->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
