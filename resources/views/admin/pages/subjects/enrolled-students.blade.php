@extends('admin.layouts.master')

@section('page-title')
    الطلاب المنضمين - {{ $subject->name }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">الطلاب المنضمين - {{ $subject->name }}</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.subjects.index') }}">المواد</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.subjects.show', $subject) }}">تفاصيل المادة</a></li>
                        <li class="breadcrumb-item active" aria-current="page">الطلاب المنضمين</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.subjects.show', $subject) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">قائمة الطلاب المنضمين</h5>
                            <div>
                                <span class="badge bg-primary">{{ $enrollments->total() }} طالب</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filter Form -->
                        <form method="GET" action="{{ route('admin.subjects.enrolled-students', $subject) }}" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">بحث</label>
                                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="البحث بالاسم أو البريد أو الهاتف...">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">الحالة</label>
                                    <select class="form-select" name="status">
                                        <option value="">الكل</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>معلق</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>معلق</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-1"></i> بحث
                                    </button>
                                    @if(request()->hasAny(['search', 'status']))
                                        <a href="{{ route('admin.subjects.enrolled-students', $subject) }}" class="btn btn-secondary ms-2">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>

                        <!-- Students Table -->
                        <div class="table-responsive">
                            <table class="table table-striped align-middle table-hover table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>الطالب</th>
                                        <th>البريد الإلكتروني</th>
                                        <th>الهاتف</th>
                                        <th>تاريخ الانضمام</th>
                                        <th>الحالة</th>
                                        <th>أضيف بواسطة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($enrollments as $enrollment)
                                        <tr>
                                            <td>{{ $enrollment->id }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($enrollment->user->photo)
                                                        <img src="{{ asset('storage/' . $enrollment->user->photo) }}" 
                                                             alt="{{ $enrollment->user->name }}" 
                                                             class="rounded-circle me-2" 
                                                             style="width: 40px; height: 40px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                             style="width: 40px; height: 40px;">
                                                            {{ substr($enrollment->user->name, 0, 1) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="fw-semibold">{{ $enrollment->user->name }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $enrollment->user->email }}</td>
                                            <td>{{ $enrollment->user->phone ?? '-' }}</td>
                                            <td>
                                                @if($enrollment->enrolled_at)
                                                    {{ $enrollment->enrolled_at->format('Y-m-d H:i') }}
                                                @else
                                                    {{ $enrollment->created_at->format('Y-m-d H:i') }}
                                                @endif
                                            </td>
                                            <td>
                                                @if($enrollment->status === 'active')
                                                    <span class="badge bg-success">نشط</span>
                                                @elseif($enrollment->status === 'suspended')
                                                    <span class="badge bg-warning">معلق</span>
                                                @elseif($enrollment->status === 'completed')
                                                    <span class="badge bg-info">مكتمل</span>
                                                @elseif($enrollment->status === 'pending')
                                                    <span class="badge bg-secondary">في الانتظار</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $enrollment->status }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($enrollment->enrolledBy)
                                                    {{ $enrollment->enrolledBy->name }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('users.show', $enrollment->user) }}" 
                                                       class="btn btn-sm btn-info" 
                                                       title="عرض الملف الشخصي">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                                    <p class="mb-0">لا يوجد طلاب منضمين لهذه المادة</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($enrollments->hasPages())
                            <div class="mt-4">
                                {{ $enrollments->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

