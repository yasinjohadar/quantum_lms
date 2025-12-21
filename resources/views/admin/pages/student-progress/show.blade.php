@extends('admin.layouts.master')

@section('page-title')
    تقدم الطالب - {{ $student->name }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تقدم الطالب - {{ $student->name }}</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.student-progress.index') }}">مراقبة تقدم الطلاب</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $student->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.student-progress.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-right me-1"></i>
                    العودة
                </a>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- معلومات الطالب -->
        <div class="row mb-4">
            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-3">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            @if($student->photo)
                                <img src="{{ asset('storage/' . $student->photo) }}" 
                                     alt="{{ $student->name }}" 
                                     class="rounded-circle me-3" 
                                     style="width: 60px; height: 60px; object-fit: cover;">
                            @else
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                     style="width: 60px; height: 60px;">
                                    <i class="bi bi-person fs-3"></i>
                                </div>
                            @endif
                            <div>
                                <h6 class="mb-0">{{ $student->name }}</h6>
                                <small class="text-muted">{{ $student->email }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-8 col-lg-6 col-md-6 col-sm-12 mb-3">
                <div class="card custom-card">
                    <div class="card-body">
                        <h6 class="mb-3">إحصائيات عامة</h6>
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <h4 class="mb-0 fw-bold text-primary">{{ $overallStats['completed_lessons'] }}/{{ $overallStats['total_lessons'] }}</h4>
                                <small class="text-muted">دروس مكتملة</small>
                            </div>
                            <div class="col-md-4 text-center">
                                <h4 class="mb-0 fw-bold text-success">{{ $overallStats['completed_quizzes'] }}/{{ $overallStats['total_quizzes'] }}</h4>
                                <small class="text-muted">اختبارات مكتملة</small>
                            </div>
                            <div class="col-md-4 text-center">
                                <h4 class="mb-0 fw-bold text-info">{{ $overallStats['completed_questions'] }}/{{ $overallStats['total_questions'] }}</h4>
                                <small class="text-muted">أسئلة مكتملة</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الكورسات -->
        <div class="row">
            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-book me-2"></i>
                            الكورسات المسجلة ({{ count($progressList) }})
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(count($progressList) > 0)
                            <div class="row">
                                @foreach($progressList as $item)
                                    @php
                                        $subject = $item['subject'];
                                        $progress = $item['progress'];
                                    @endphp
                                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-3">
                                        <div class="card border h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div>
                                                        <h6 class="mb-1 fw-semibold">{{ $subject->name }}</h6>
                                                        @if($subject->schoolClass)
                                                            <small class="text-muted">
                                                                <i class="bi bi-building me-1"></i>
                                                                {{ $subject->schoolClass->name }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                    <span class="badge bg-primary">
                                                        {{ number_format($progress['overall_percentage'], 1) }}%
                                                    </span>
                                                </div>

                                                <!-- شريط التقدم -->
                                                <div class="mb-3">
                                                    <div class="progress" style="height: 8px;">
                                                        <div class="progress-bar bg-primary" role="progressbar" 
                                                             style="width: {{ $progress['overall_percentage'] }}%"
                                                             aria-valuenow="{{ $progress['overall_percentage'] }}" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- الإحصائيات -->
                                                <div class="mb-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <small class="text-muted">
                                                            <i class="bi bi-play-circle me-1"></i>
                                                            الدروس
                                                        </small>
                                                        <small class="fw-semibold">
                                                            {{ $progress['lessons_completed'] }}/{{ $progress['lessons_total'] }}
                                                        </small>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <small class="text-muted">
                                                            <i class="bi bi-clipboard-check me-1"></i>
                                                            الاختبارات
                                                        </small>
                                                        <small class="fw-semibold">
                                                            {{ $progress['quizzes_completed'] }}/{{ $progress['quizzes_total'] }}
                                                        </small>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <small class="text-muted">
                                                            <i class="bi bi-question-circle me-1"></i>
                                                            الأسئلة
                                                        </small>
                                                        <small class="fw-semibold">
                                                            {{ $progress['questions_completed'] }}/{{ $progress['questions_total'] }}
                                                        </small>
                                                    </div>
                                                </div>

                                                <a href="{{ route('admin.student-progress.subject', ['user' => $student->id, 'subject' => $subject->id]) }}" class="btn btn-sm btn-primary w-100">
                                                    <i class="bi bi-eye me-1"></i>
                                                    عرض التفاصيل
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-book fs-1 text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">الطالب غير مسجل في أي كورس</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

