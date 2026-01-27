@extends('admin.layouts.master')

@section('page-title')
    {{ $class->name }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">{{ $class->name }}</h5>
                    @if($class->stage)
                        <p class="text-muted mb-0">{{ $class->stage->name }}</p>
                    @endif
                </div>
                <div>
                    <a href="{{ route('admin.my-classes') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i> رجوع
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <!-- إحصائيات -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h6 class="text-white-50 mb-2">عدد المواد</h6>
                            <h3 class="mb-0">{{ $stats['total_subjects'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6 class="text-white-50 mb-2">عدد الطلاب</h6>
                            <h3 class="mb-0">{{ $stats['total_students'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6 class="text-white-50 mb-2">عدد الاختبارات</h6>
                            <h3 class="mb-0">{{ $stats['total_quizzes'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h6 class="text-white-50 mb-2">عدد الواجبات</h6>
                            <h3 class="mb-0">{{ $stats['total_assignments'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- معلومات الصف -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">معلومات الصف</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>الاسم:</strong> {{ $class->name }}</p>
                            @if($class->stage)
                                <p><strong>المرحلة:</strong> {{ $class->stage->name }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($class->description)
                                <p><strong>الوصف:</strong> {{ $class->description }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- المواد -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">المواد الدراسية</h6>
                </div>
                <div class="card-body">
                    @if($subjects->count() > 0)
                        <div class="row">
                            @foreach($subjects as $subject)
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h6 class="card-title">{{ $subject->name }}</h6>
                                            <a href="{{ route('admin.my-subjects.show', $subject->id) }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye me-1"></i> عرض المادة
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-4">لا توجد مواد في هذا الصف</p>
                    @endif
                </div>
            </div>

            <!-- الطلاب المسجلين -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">الطلاب المسجلين</h6>
                </div>
                <div class="card-body">
                    @if($enrolledStudents->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>الاسم</th>
                                        <th>البريد الإلكتروني</th>
                                        <th>تاريخ التسجيل</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($enrolledStudents as $enrollment)
                                        <tr>
                                            <td>{{ $loop->iteration + ($enrolledStudents->currentPage() - 1) * $enrolledStudents->perPage() }}</td>
                                            <td>{{ $enrollment->user->name }}</td>
                                            <td>{{ $enrollment->user->email }}</td>
                                            <td>{{ $enrollment->created_at->format('Y-m-d') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $enrolledStudents->links() }}
                        </div>
                    @else
                        <p class="text-muted text-center py-4">لا يوجد طلاب مسجلين في هذا الصف</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
