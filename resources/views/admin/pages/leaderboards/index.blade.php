@extends('admin.layouts.master')

@section('page-title')
    إدارة لوحة المتصدرين
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إدارة لوحة المتصدرين</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">لوحة المتصدرين</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.leaderboards.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i>
                    إضافة لوحة جديدة
                </a>
            </div>
        </div>
        <!-- End Page Header -->

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">قائمة لوحات المتصدرين</div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>الاسم</th>
                                        <th>النوع</th>
                                        <th>المادة</th>
                                        <th>الفترة</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($leaderboards as $leaderboard)
                                    <tr>
                                        <td>{{ $leaderboard->id }}</td>
                                        <td>{{ $leaderboard->name }}</td>
                                        <td><span class="badge bg-info">{{ $leaderboard->type_name }}</span></td>
                                        <td>{{ $leaderboard->subject->name ?? 'عام' }}</td>
                                        <td>
                                            @if($leaderboard->period_start && $leaderboard->period_end)
                                                {{ $leaderboard->period_start->format('Y-m-d') }} - {{ $leaderboard->period_end->format('Y-m-d') }}
                                            @else
                                                دائم
                                            @endif
                                        </td>
                                        <td>
                                            @if($leaderboard->is_active)
                                                <span class="badge bg-success">نشط</span>
                                            @else
                                                <span class="badge bg-danger">غير نشط</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('admin.leaderboards.edit', $leaderboard) }}" class="btn btn-sm btn-primary">
                                                    <i class="fe fe-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.leaderboards.refresh', $leaderboard) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning" title="تحديث اللوحة">
                                                        <i class="fe fe-refresh-cw"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">لا توجد لوحات متصدرين</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End::app-content -->
@stop

