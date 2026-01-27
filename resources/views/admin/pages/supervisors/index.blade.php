@extends('admin.layouts.master')

@section('page-title')
    تخصيص المشرفين
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تخصيص المشرفين للصفوف والمواد</h5>
                </div>
                <div class="d-flex gap-2">
                    @can('user-create')
                        <a href="{{ route('users.create', ['role' => 'supervisor']) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-user-plus me-1"></i> إضافة مشرف جديد
                        </a>
                    @endcan
                    <a href="{{ route('users.index', ['role' => 'supervisor']) }}" class="btn btn-info btn-sm">
                        <i class="fas fa-users me-1"></i> عرض جميع المشرفين
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

            <!-- بطاقات الإحصائيات -->
            @if(isset($totalSupervisors))
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h6 class="text-white-50 mb-2">إجمالي المشرفين</h6>
                            <h3 class="mb-0">{{ $totalSupervisors }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6 class="text-white-50 mb-2">مشرفون مخصصون</h6>
                            <h3 class="mb-0">{{ $assignedSupervisors }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h6 class="text-white-50 mb-2">مشرفون غير مخصصين</h6>
                            <h3 class="mb-0">{{ $unassignedSupervisors }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="row">
                <div class="col-xl-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <h5 class="mb-0 fw-bold">قائمة المشرفين</h5>

                            <form method="GET" action="{{ route('admin.supervisors.assignments.index') }}"
                                  class="d-flex gap-2 align-items-center">
                                <input type="text" name="search" class="form-control form-control-sm"
                                       placeholder="بحث بالاسم أو البريد الإلكتروني"
                                       value="{{ request('search') }}" style="min-width: 250px;">

                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search me-1"></i> بحث
                                </button>
                                @if(request('search'))
                                    <a href="{{ route('admin.supervisors.assignments.index') }}" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-times me-1"></i> إلغاء
                                    </a>
                                @endif
                            </form>
                        </div>

                        <div class="card-body">
                            @if($supervisors->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover text-nowrap">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>الاسم</th>
                                                <th>البريد الإلكتروني</th>
                                                <th>الصفوف المخصصة</th>
                                                <th>المواد المخصصة</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($supervisors as $supervisor)
                                                <tr>
                                                    <td>{{ $loop->iteration + ($supervisors->currentPage() - 1) * $supervisors->perPage() }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($supervisor->photo)
                                                                <img src="{{ asset('storage/' . $supervisor->photo) }}" 
                                                                     alt="{{ $supervisor->name }}" 
                                                                     class="rounded-circle me-2" 
                                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                                            @else
                                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" 
                                                                     style="width: 40px; height: 40px;">
                                                                    {{ substr($supervisor->name, 0, 1) }}
                                                                </div>
                                                            @endif
                                                            <span class="fw-semibold">{{ $supervisor->name }}</span>
                                                        </div>
                                                    </td>
                                                    <td>{{ $supervisor->email }}</td>
                                                    <td>
                                                        @php
                                                            $assignedClasses = $supervisor->assignedClassesAsSupervisor;
                                                        @endphp
                                                        @if($assignedClasses->count() > 0)
                                                            <span class="badge bg-primary">
                                                                {{ $assignedClasses->count() }} صف
                                                            </span>
                                                            <div class="mt-1">
                                                                @foreach($assignedClasses->take(2) as $class)
                                                                    <small class="d-block text-muted">{{ $class->name }}</small>
                                                                @endforeach
                                                                @if($assignedClasses->count() > 2)
                                                                    <small class="text-muted">+ {{ $assignedClasses->count() - 2 }} أخرى</small>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <span class="text-muted">لا يوجد</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php
                                                            $assignedSubjects = $supervisor->assignedSubjectsAsSupervisor;
                                                        @endphp
                                                        @if($assignedSubjects->count() > 0)
                                                            <span class="badge bg-info">
                                                                {{ $assignedSubjects->count() }} مادة
                                                            </span>
                                                            <div class="mt-1">
                                                                @foreach($assignedSubjects->take(2) as $subject)
                                                                    <small class="d-block text-muted">{{ $subject->name }}</small>
                                                                @endforeach
                                                                @if($assignedSubjects->count() > 2)
                                                                    <small class="text-muted">+ {{ $assignedSubjects->count() - 2 }} أخرى</small>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <span class="text-muted">لا يوجد</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.supervisors.assignments', $supervisor->id) }}" 
                                                           class="btn btn-primary btn-sm">
                                                            <i class="fas fa-user-tie me-1"></i> تخصيص
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-center mt-3">
                                    {{ $supervisors->links() }}
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-3">لا يوجد مشرفين</p>
                                    @can('user-create')
                                        <a href="{{ route('users.create', ['role' => 'supervisor']) }}" class="btn btn-primary">
                                            <i class="fas fa-user-plus me-1"></i> إضافة مشرف جديد
                                        </a>
                                    @endcan
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop
