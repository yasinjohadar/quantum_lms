@extends('admin.layouts.master')

@section('page-title')
    نظرة عامة — {{ $supervisor->name }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">نظرة عامة على المشرف</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.supervisors.assignments.index') }}">تخصيص المشرفين</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $supervisor->name }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.supervisors.assignments.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-right me-1"></i> قائمة المشرفين
                    </a>
                    @can('supervisor-assignment-show')
                        <a href="{{ route('admin.supervisors.assignments', $supervisor) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-user-tie me-1"></i> تعديل التخصيصات
                        </a>
                    @endcan
                    @can('user-edit')
                        <a href="{{ route('users.edit', ['user' => $supervisor->id, 'role' => 'supervisor']) }}" class="btn btn-info btn-sm">
                            <i class="fa-solid fa-pen-to-square me-1"></i> تعديل المستخدم
                        </a>
                    @endcan
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body text-center">
                            @if($supervisor->photo)
                                <img src="{{ media_public_url($supervisor->photo) }}" alt="" class="rounded-circle mb-3"
                                     style="width: 96px; height: 96px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3"
                                     style="width: 96px; height: 96px; font-size: 2rem;">
                                    {{ mb_substr($supervisor->name, 0, 1) }}
                                </div>
                            @endif
                            <h5 class="mb-1">{{ $supervisor->name }}</h5>
                            <p class="text-muted small mb-2">{{ $supervisor->email }}</p>
                            @if($supervisor->phone)
                                <p class="text-muted small mb-0"><i class="bi bi-telephone me-1"></i>{{ $supervisor->phone }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-transparent">
                            <h6 class="mb-0 fw-bold">الأدوار والنشاط</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <span class="text-muted small d-block mb-1">الأدوار</span>
                                @if($supervisor->roles->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($supervisor->roles as $role)
                                            <span class="badge bg-secondary">{{ $role->name }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <span class="text-muted small d-block">آخر دخول ناجح</span>
                                    @if($lastLogin)
                                        <strong>{{ \Carbon\Carbon::parse($lastLogin)->format('Y-m-d H:i') }}</strong>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <span class="text-muted small d-block">الحالة الآن</span>
                                    @if($isOnline)
                                        <span class="badge bg-success"><i class="fa-solid fa-circle fa-fw" style="font-size: 0.5em;"></i> متصل</span>
                                    @else
                                        <span class="badge bg-secondary">غير متصل</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body py-3 d-flex justify-content-between align-items-center">
                            <span class="text-muted small">صفوف مخصصة مباشرة</span>
                            <span class="fs-5 fw-bold text-primary">{{ $assignedClasses->count() }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body py-3 d-flex justify-content-between align-items-center">
                            <span class="text-muted small">مواد مخصصة مباشرة</span>
                            <span class="fs-5 fw-bold text-info">{{ $directSubjects->count() }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body py-3 d-flex justify-content-between align-items-center">
                            <span class="text-muted small">إجمالي المواد ضمن النطاق</span>
                            <span class="fs-5 fw-bold text-success">{{ $accessibleSubjects->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <p class="text-muted small mb-3">
                «النطاق الفعلي» يشمل مواد الصفوف المخصصة للمشرف بالإضافة إلى أي مواد رُبطت به مباشرة (قد تتداخل مع صفوف).
            </p>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-building me-2"></i>الصفوف المخصصة</h6>
                </div>
                <div class="card-body p-0">
                    @if($assignedClasses->isEmpty())
                        <p class="text-muted mb-0 p-3">لا توجد صفوف مخصصة.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>الصف</th>
                                        <th>المرحلة</th>
                                        <th>تاريخ التخصيص</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assignedClasses as $class)
                                        <tr>
                                            <td class="fw-semibold">{{ $class->name }}</td>
                                            <td>{{ $class->stage?->name ?? '—' }}</td>
                                            <td class="small text-muted">
                                                {{ $class->pivot->assigned_at ? \Carbon\Carbon::parse($class->pivot->assigned_at)->format('Y-m-d H:i') : '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-journal-bookmark me-2"></i>المواد المخصصة مباشرة</h6>
                </div>
                <div class="card-body p-0">
                    @if($directSubjects->isEmpty())
                        <p class="text-muted mb-0 p-3">لا توجد مواد مربوطة مباشرة بالمشرف.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>المادة</th>
                                        <th>الصف</th>
                                        <th>المرحلة</th>
                                        <th>تاريخ التخصيص</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($directSubjects as $subject)
                                        <tr>
                                            <td class="fw-semibold">{{ $subject->name }}</td>
                                            <td>{{ $subject->schoolClass?->name ?? '—' }}</td>
                                            <td>{{ $subject->schoolClass?->stage?->name ?? '—' }}</td>
                                            <td class="small text-muted">
                                                {{ $subject->pivot->assigned_at ? \Carbon\Carbon::parse($subject->pivot->assigned_at)->format('Y-m-d H:i') : '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-grid-3x3-gap me-2"></i>جميع المواد ضمن نطاق المشرف (فعّالة)</h6>
                </div>
                <div class="card-body p-0">
                    @if($accessibleSubjects->isEmpty())
                        <p class="text-muted mb-0 p-3">لا توجد مواد ضمن النطاق الحالي.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>المادة</th>
                                        <th>الصف</th>
                                        <th>المرحلة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($accessibleSubjects as $subject)
                                        <tr>
                                            <td>{{ $subject->name }}</td>
                                            <td>{{ $subject->schoolClass?->name ?? '—' }}</td>
                                            <td>{{ $subject->schoolClass?->stage?->name ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@stop
