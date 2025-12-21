@extends('admin.layouts.master')

@section('page-title')
    مراقبة تقدم الطلاب
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">مراقبة تقدم الطلاب</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">مراقبة تقدم الطلاب</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Filters -->
        <div class="card custom-card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.student-progress.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">البحث</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="اسم الطالب أو البريد الإلكتروني" 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">الصف</label>
                        <select name="class_id" class="form-select">
                            <option value="">جميع الصفوف</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                    @if($class->stage)
                                        - {{ $class->stage->name }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">الكورس</label>
                        <select name="subject_id" class="form-select">
                            <option value="">جميع الكورسات</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search me-1"></i>
                            بحث
                        </button>
                        <a href="{{ route('admin.student-progress.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise me-1"></i>
                            إعادة تعيين
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Students Table -->
        <div class="card custom-card">
            <div class="card-header">
                <h5 class="mb-0">قائمة الطلاب</h5>
            </div>
            <div class="card-body">
                @if($students->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>الطالب</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>عدد الكورسات</th>
                                    <th>متوسط التقدم</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $student)
                                    @php
                                        $stats = $studentsStats[$student->id] ?? ['total_subjects' => 0, 'avg_progress' => 0];
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($student->photo)
                                                    <img src="{{ asset('storage/' . $student->photo) }}" 
                                                         alt="{{ $student->name }}" 
                                                         class="rounded-circle me-2" 
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                @else
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                         style="width: 40px; height: 40px;">
                                                        <i class="bi bi-person"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <h6 class="mb-0">{{ $student->name }}</h6>
                                                    @if($student->phone)
                                                        <small class="text-muted">{{ $student->phone }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $student->email }}</td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $stats['total_subjects'] }} كورس
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1 me-2">
                                                    <div class="progress" style="height: 8px;">
                                                        <div class="progress-bar bg-{{ $stats['avg_progress'] >= 75 ? 'success' : ($stats['avg_progress'] >= 50 ? 'warning' : 'danger') }}" 
                                                             role="progressbar" 
                                                             style="width: {{ $stats['avg_progress'] }}%"
                                                             aria-valuenow="{{ $stats['avg_progress'] }}" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="fw-semibold">{{ number_format($stats['avg_progress'], 1) }}%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.student-progress.show', $student->id) }}" class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye me-1"></i>
                                                عرض التفاصيل
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $students->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-people fs-1 text-muted mb-3 d-block"></i>
                        <h5 class="mb-2">لا يوجد طلاب</h5>
                        <p class="text-muted">لم يتم العثور على أي طلاب</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

