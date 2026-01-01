@extends('admin.layouts.master')

@section('page-title')
    تفاصيل تقدم الطالب: {{ $student->name }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تفاصيل تقدم الطالب</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.student-progress.index') }}">مراقبة تقدم الطلاب</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $student->name }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Student Info Card -->
        <div class="card custom-card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    @if($student->photo)
                        <img src="{{ asset('storage/' . $student->photo) }}" 
                             alt="{{ $student->name }}" 
                             class="rounded-circle me-3" 
                             style="width: 80px; height: 80px; object-fit: cover;">
                    @else
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 80px; height: 80px; font-size: 2rem;">
                            <i class="bi bi-person"></i>
                        </div>
                    @endif
                    <div class="flex-grow-1">
                        <h4 class="mb-1">{{ $student->name }}</h4>
                        <p class="text-muted mb-1">
                            <i class="bi bi-envelope me-1"></i>{{ $student->email }}
                        </p>
                        @if($student->phone)
                            <p class="text-muted mb-0">
                                <i class="bi bi-telephone me-1"></i>{{ $student->phone }}
                            </p>
                        @endif
                    </div>
                    <div>
                        <a href="{{ route('admin.student-progress.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-right me-1"></i>
                            العودة للقائمة
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overall Statistics -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">إجمالي الدروس</p>
                                <h3 class="mb-0">{{ $overallStats['total_lessons'] }}</h3>
                                <small class="text-success">
                                    مكتمل: {{ $overallStats['completed_lessons'] }}
                                </small>
                            </div>
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 60px; height: 60px;">
                                <i class="bi bi-book fs-4"></i>
                            </div>
                        </div>
                        @if($overallStats['total_lessons'] > 0)
                            <div class="progress mt-3" style="height: 6px;">
                                <div class="progress-bar bg-primary" 
                                     role="progressbar" 
                                     style="width: {{ ($overallStats['completed_lessons'] / $overallStats['total_lessons']) * 100 }}%">
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">إجمالي الاختبارات</p>
                                <h3 class="mb-0">{{ $overallStats['total_quizzes'] }}</h3>
                                <small class="text-success">
                                    مكتمل: {{ $overallStats['completed_quizzes'] }}
                                </small>
                            </div>
                            <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 60px; height: 60px;">
                                <i class="bi bi-file-check fs-4"></i>
                            </div>
                        </div>
                        @if($overallStats['total_quizzes'] > 0)
                            <div class="progress mt-3" style="height: 6px;">
                                <div class="progress-bar bg-success" 
                                     role="progressbar" 
                                     style="width: {{ ($overallStats['completed_quizzes'] / $overallStats['total_quizzes']) * 100 }}%">
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">إجمالي الأسئلة</p>
                                <h3 class="mb-0">{{ $overallStats['total_questions'] }}</h3>
                                <small class="text-success">
                                    مكتمل: {{ $overallStats['completed_questions'] }}
                                </small>
                            </div>
                            <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 60px; height: 60px;">
                                <i class="bi bi-question-circle fs-4"></i>
                            </div>
                        </div>
                        @if($overallStats['total_questions'] > 0)
                            <div class="progress mt-3" style="height: 6px;">
                                <div class="progress-bar bg-warning" 
                                     role="progressbar" 
                                     style="width: {{ ($overallStats['completed_questions'] / $overallStats['total_questions']) * 100 }}%">
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Subjects Progress List -->
        <div class="card custom-card">
            <div class="card-header">
                <h5 class="mb-0">الكورسات المسجل بها</h5>
            </div>
            <div class="card-body">
                @if(count($progressList) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>الكورس</th>
                                    <th>الصف</th>
                                    <th>الدروس</th>
                                    <th>الاختبارات</th>
                                    <th>الأسئلة</th>
                                    <th>نسبة التقدم</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($progressList as $item)
                                    @php
                                        $subject = $item['subject'];
                                        $progress = $item['progress'];
                                        $overallPercentage = $progress['overall_percentage'] ?? 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <h6 class="mb-0">{{ $subject->name }}</h6>
                                            @if($subject->description)
                                                <small class="text-muted">{{ Str::limit($subject->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($subject->schoolClass)
                                                <span class="badge bg-info">
                                                    {{ $subject->schoolClass->name }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $progress['lessons_completed'] }}/{{ $progress['lessons_total'] }}
                                            </span>
                                            <small class="text-muted d-block">
                                                {{ number_format($progress['lessons_percentage'] ?? 0, 1) }}%
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">
                                                {{ $progress['quizzes_completed'] }}/{{ $progress['quizzes_total'] }}
                                            </span>
                                            <small class="text-muted d-block">
                                                {{ number_format($progress['quizzes_percentage'] ?? 0, 1) }}%
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">
                                                {{ $progress['questions_completed'] }}/{{ $progress['questions_total'] }}
                                            </span>
                                            <small class="text-muted d-block">
                                                {{ number_format($progress['questions_percentage'] ?? 0, 1) }}%
                                            </small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1 me-2">
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-{{ $overallPercentage >= 75 ? 'success' : ($overallPercentage >= 50 ? 'warning' : 'danger') }}" 
                                                             role="progressbar" 
                                                             style="width: {{ $overallPercentage }}%"
                                                             aria-valuenow="{{ $overallPercentage }}" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                            {{ number_format($overallPercentage, 1) }}%
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.student-progress.subject', ['user' => $student->id, 'subject' => $subject->id]) }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye me-1"></i>
                                                التفاصيل
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-book fs-1 text-muted mb-3 d-block"></i>
                        <h5 class="mb-2">لا يوجد كورسات</h5>
                        <p class="text-muted">الطالب غير مسجل في أي كورسات حالياً</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop



