@extends('admin.layouts.master')

@section('page-title')
    {{ $subject->name }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">{{ $subject->name }}</h5>
                    @if($subject->schoolClass)
                        <p class="text-muted mb-0">{{ $subject->schoolClass->name }} 
                            @if($subject->schoolClass->stage)
                                - {{ $subject->schoolClass->stage->name }}
                            @endif
                        </p>
                    @endif
                </div>
                <div>
                    <a href="{{ route('admin.my-subjects') }}" class="btn btn-secondary btn-sm">
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
                            <h6 class="text-white-50 mb-2">عدد الأقسام</h6>
                            <h3 class="mb-0">{{ $stats['total_sections'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6 class="text-white-50 mb-2">عدد الوحدات</h6>
                            <h3 class="mb-0">{{ $stats['total_units'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6 class="text-white-50 mb-2">عدد الدروس</h6>
                            <h3 class="mb-0">{{ $stats['total_lessons'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h6 class="text-white-50 mb-2">عدد الطلاب</h6>
                            <h3 class="mb-0">{{ $stats['total_students'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- معلومات المادة -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">معلومات المادة</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>الاسم:</strong> {{ $subject->name }}</p>
                            @if($subject->schoolClass)
                                <p><strong>الصف:</strong> {{ $subject->schoolClass->name }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($subject->description)
                                <p><strong>الوصف:</strong> {{ $subject->description }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- الأقسام والوحدات -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">الأقسام والوحدات</h6>
                </div>
                <div class="card-body">
                    @if($sections->count() > 0)
                        @foreach($sections as $section)
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">{{ $section->name }}</h6>
                                @if($section->units->count() > 0)
                                    <div class="row">
                                        @foreach($section->units as $unit)
                                            <div class="col-md-6 mb-3">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h6 class="card-title">{{ $unit->name }}</h6>
                                                        <p class="text-muted small mb-2">
                                                            {{ $unit->lessons->count() }} درس
                                                        </p>
                                                        @if($unit->lessons->count() > 0)
                                                            <div class="small">
                                                                <strong>الدروس:</strong>
                                                                <ul class="mb-0 mt-1">
                                                                    @foreach($unit->lessons->take(3) as $lesson)
                                                                        <li>{{ $lesson->title }}</li>
                                                                    @endforeach
                                                                    @if($unit->lessons->count() > 3)
                                                                        <li class="text-muted">+ {{ $unit->lessons->count() - 3 }} دروس أخرى</li>
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted mb-0" style="font-size: 0.75rem;">لا توجد وحدات في هذا القسم</p>
                                @endif
                            </div>
                            @if(!$loop->last)
                                <hr>
                            @endif
                        @endforeach
                    @else
                        <p class="text-muted text-center py-4">لا توجد أقسام في هذه المادة</p>
                    @endif
                </div>
            </div>

            <!-- الاختبارات -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">الاختبارات</h6>
                </div>
                <div class="card-body">
                    @if($quizzes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>اسم الاختبار</th>
                                        <th>الحالة</th>
                                        <th>تاريخ الإنشاء</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($quizzes as $quiz)
                                        <tr>
                                            <td>{{ $loop->iteration + ($quizzes->currentPage() - 1) * $quizzes->perPage() }}</td>
                                            <td>{{ $quiz->title }}</td>
                                            <td>
                                                @if($quiz->is_published)
                                                    <span class="badge bg-success">منشور</span>
                                                @else
                                                    <span class="badge bg-warning">مسودة</span>
                                                @endif
                                            </td>
                                            <td>{{ $quiz->created_at->format('Y-m-d') }}</td>
                                            <td>
                                                <a href="{{ route('admin.quizzes.show', $quiz->id) }}" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye me-1"></i> عرض
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $quizzes->links() }}
                        </div>
                    @else
                        <p class="text-muted text-center py-4">لا توجد اختبارات في هذه المادة</p>
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
                        <p class="text-muted text-center py-4">لا يوجد طلاب مسجلين في هذه المادة</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
