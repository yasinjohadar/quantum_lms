@extends('admin.layouts.master')

@section('page-title')
    إدارة الشارات
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إدارة الشارات</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">الشارات</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.badges.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i>
                    إضافة شارة جديدة
                </a>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">قائمة الشارات</div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>الاسم</th>
                                        <th>الوصف</th>
                                        <th>النقاط المطلوبة</th>
                                        <th>تلقائي</th>
                                        <th>الحالة</th>
                                        <th>الترتيب</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($badges as $badge)
                                    <tr>
                                        <td>{{ $badge->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($badge->icon)
                                                    <i class="{{ $badge->icon }} me-2" style="color: {{ $badge->color ?? '#007bff' }};"></i>
                                                @else
                                                    <i class="fe fe-award me-2" style="color: {{ $badge->color ?? '#007bff' }};"></i>
                                                @endif
                                                {{ $badge->name }}
                                            </div>
                                        </td>
                                        <td>{{ Str::limit($badge->description, 50) }}</td>
                                        <td>{{ $badge->points_required }}</td>
                                        <td>
                                            @if($badge->is_automatic)
                                                <span class="badge bg-success">نعم</span>
                                            @else
                                                <span class="badge bg-secondary">لا</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($badge->is_active)
                                                <span class="badge bg-success">نشط</span>
                                            @else
                                                <span class="badge bg-danger">غير نشط</span>
                                            @endif
                                        </td>
                                        <td>{{ $badge->order }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('admin.badges.edit', $badge) }}" class="btn btn-sm btn-primary">
                                                    <i class="fe fe-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.badges.destroy', $badge) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fe fe-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center">لا توجد شارات</td>
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

