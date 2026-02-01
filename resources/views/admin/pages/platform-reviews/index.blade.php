@extends('admin.layouts.master')

@section('page-title')
    إدارة التقييمات
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة التقييمات</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">إدارة التقييمات</li>
                        </ol>
                    </nav>
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
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <form method="get" class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label mb-1">الحالة</label>
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="">كل الحالات</option>
                                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>معلق</option>
                                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>معتمد</option>
                                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>مرفوض</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-search me-1"></i> تطبيق
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header">
                            <h5 class="mb-0 fw-bold">قائمة التقييمات (قبول / رفض / تعديل)</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle table-hover table-bordered mb-0 text-center">
                                    <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>الطالب</th>
                                        <th>الصف</th>
                                        <th style="width: 80px;">النجوم</th>
                                        <th>التعليق</th>
                                        <th style="width: 100px;">الحالة</th>
                                        <th style="width: 100px;">الترتيب</th>
                                        <th style="width: 180px;">إجراءات</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($reviews as $review)
                                        <tr>
                                            <td>{{ $review->id }}</td>
                                            <td class="text-start">{{ $review->user->name ?? '—' }}</td>
                                            <td>{{ $review->schoolClass->name ?? '—' }}</td>
                                            <td>
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fa-solid fa-star {{ $i <= $review->stars ? 'text-warning' : 'text-muted opacity-50' }}"></i>
                                                @endfor
                                            </td>
                                            <td class="text-start text-truncate" style="max-width: 200px;" title="{{ $review->comment }}">
                                                {{ Str::limit($review->comment, 50) }}
                                            </td>
                                            <td>
                                                @if($review->status === 'pending')
                                                    <span class="badge bg-warning">معلق</span>
                                                @elseif($review->status === 'approved')
                                                    <span class="badge bg-success">معتمد</span>
                                                @else
                                                    <span class="badge bg-danger">مرفوض</span>
                                                @endif
                                            </td>
                                            <td>{{ $review->order }}</td>
                                            <td>
                                                @can('platform-reviews-edit')
                                                    <a href="{{ route('admin.platform-reviews.edit', $review) }}" class="btn btn-sm btn-outline-primary" title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('platform-reviews-approve')
                                                    @if($review->status !== 'approved')
                                                        <form action="{{ route('admin.platform-reviews.approve', $review) }}" method="post" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-success" title="اعتماد">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if($review->status !== 'rejected')
                                                        <form action="{{ route('admin.platform-reviews.reject', $review) }}" method="post" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="رفض">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">لا توجد آراء.</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if($reviews->hasPages())
                                <div class="d-flex justify-content-center mt-3">
                                    {{ $reviews->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
