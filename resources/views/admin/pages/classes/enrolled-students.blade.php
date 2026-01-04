@extends('admin.layouts.master')

@section('page-title')
    الطلاب المنضمين - {{ $class->name }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">الطلاب المنضمين - {{ $class->name }}</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.classes.index') }}">الصفوف</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.classes.show', $class) }}">تفاصيل الصف</a></li>
                        <li class="breadcrumb-item active" aria-current="page">الطلاب المنضمين</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.classes.show', $class) }}" class="btn btn-secondary btn-sm">
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
                        <!-- Filters -->
                        <form method="GET" action="{{ route('admin.classes.enrolled-students', $class) }}">
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">البحث</label>
                                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="البحث بالاسم أو البريد أو الهاتف...">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">المادة</label>
                                    <select class="form-select" name="subject_id">
                                        <option value="">جميع المواد</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                        @endforeach
                                    </select>
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
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-1"></i> بحث
                                    </button>
                                    @if(request()->hasAny(['search', 'status', 'subject_id']))
                                        <a href="{{ route('admin.classes.enrolled-students', $class) }}" class="btn btn-secondary ms-2">
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
                                        <th>المادة</th>
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
                                                             style="width: 35px; height: 35px; object-fit: cover;">
                                                    @else
                                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" 
                                                             style="width: 35px; height: 35px; font-size: 14px;">
                                                            {{ strtoupper(substr($enrollment->user->name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="fw-semibold">{{ $enrollment->user->name }}</div>
                                                        @if($enrollment->user->is_active)
                                                            <span class="badge bg-success badge-sm">نشط</span>
                                                        @else
                                                            <span class="badge bg-danger badge-sm">غير نشط</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $enrollment->user->email }}</td>
                                            <td>{{ $enrollment->user->phone ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ $enrollment->subject->name }}</span>
                                            </td>
                                            <td>{{ $enrollment->enrolled_at ? $enrollment->enrolled_at->format('Y-m-d') : '-' }}</td>
                                            <td>
                                                @if($enrollment->status === 'active')
                                                    <span class="badge bg-success">نشط</span>
                                                @elseif($enrollment->status === 'suspended')
                                                    <span class="badge bg-warning">معلق</span>
                                                @elseif($enrollment->status === 'completed')
                                                    <span class="badge bg-info">مكتمل</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $enrollment->status }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($enrollment->enrolledBy)
                                                    {{ $enrollment->enrolledBy->name }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('users.show', $enrollment->user->id) }}" 
                                                       class="btn btn-info btn-sm" 
                                                       title="عرض الملف الشخصي">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                                    <p class="mb-0">لا يوجد طلاب منضمين</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($enrollments->hasPages())
                            <div class="mt-3">
                                {{ $enrollments->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

