@extends('admin.layouts.master')

@section('page-title')
    تقدم {{ $student->name }} في {{ $subject->name }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تقدم {{ $student->name }} في {{ $subject->name }}</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.student-progress.index') }}">مراقبة تقدم الطلاب</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.student-progress.show', $student->id) }}">{{ $student->name }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $subject->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.student-progress.show', $student->id) }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-right me-1"></i>
                    العودة
                </a>
            </div>
        </div>
        <!-- End Page Header -->

        @php
            $progress = $stats['progress'];
        @endphp

        <!-- معلومات الطالب والكورس -->
        <div class="row mb-4">
            <div class="col-xl-6 col-lg-12 mb-3">
                <div class="card custom-card">
                    <div class="card-body">
                        <h6 class="mb-3">معلومات الطالب</h6>
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
            <div class="col-xl-6 col-lg-12 mb-3">
                <div class="card custom-card">
                    <div class="card-body">
                        <h6 class="mb-3">معلومات الكورس</h6>
                        <h6 class="mb-1">{{ $subject->name }}</h6>
                        @if($subject->schoolClass)
                            <small class="text-muted">
                                <i class="bi bi-building me-1"></i>
                                {{ $subject->schoolClass->name }}
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- إحصائيات عامة -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
                <div class="card custom-card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">التقدم الإجمالي</h6>
                        <h3 class="mb-0 fw-bold text-primary">{{ number_format($progress['overall_percentage'], 1) }}%</h3>
                        <div class="progress mt-2" style="height: 10px;">
                            <div class="progress-bar bg-primary" style="width: {{ $progress['overall_percentage'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
                <div class="card custom-card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">الدروس</h6>
                        <h3 class="mb-0 fw-bold text-info">
                            {{ $progress['lessons_completed'] }}/{{ $progress['lessons_total'] }}
                        </h3>
                        <small class="text-muted">{{ number_format($progress['lessons_percentage'], 1) }}%</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
                <div class="card custom-card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">الاختبارات</h6>
                        <h3 class="mb-0 fw-bold text-success">
                            {{ $progress['quizzes_completed'] }}/{{ $progress['quizzes_total'] }}
                        </h3>
                        <small class="text-muted">{{ number_format($progress['quizzes_percentage'], 1) }}%</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
                <div class="card custom-card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">الأسئلة</h6>
                        <h3 class="mb-0 fw-bold text-warning">
                            {{ $progress['questions_completed'] }}/{{ $progress['questions_total'] }}
                        </h3>
                        <small class="text-muted">{{ number_format($progress['questions_percentage'], 1) }}%</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- الأقسام -->
        <div class="row">
            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-list-ul me-2"></i>
                            الأقسام الدراسية
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(count($stats['sections']) > 0)
                            <div class="row">
                                @foreach($stats['sections'] as $sectionItem)
                                    @php
                                        $section = $sectionItem['section'];
                                        $sectionProgress = $sectionItem['progress'];
                                    @endphp
                                    <div class="col-xl-6 col-lg-12 mb-3">
                                        <div class="card border h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div>
                                                        <h6 class="mb-1 fw-semibold">{{ $section->title }}</h6>
                                                        @if($section->description)
                                                            <p class="text-muted small mb-0">{{ \Illuminate\Support\Str::limit($section->description, 80) }}</p>
                                                        @endif
                                                    </div>
                                                    <span class="badge bg-primary">
                                                        {{ number_format($sectionProgress['overall_percentage'], 1) }}%
                                                    </span>
                                                </div>

                                                <!-- شريط التقدم -->
                                                <div class="mb-3">
                                                    <div class="progress" style="height: 8px;">
                                                        <div class="progress-bar bg-primary" role="progressbar" 
                                                             style="width: {{ $sectionProgress['overall_percentage'] }}%"
                                                             aria-valuenow="{{ $sectionProgress['overall_percentage'] }}" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- إحصائيات القسم -->
                                                <div class="d-flex flex-wrap gap-2">
                                                    <span class="badge bg-info-transparent text-info">
                                                        <i class="bi bi-play-circle me-1"></i>
                                                        {{ $sectionProgress['lessons_completed'] }}/{{ $sectionProgress['lessons_total'] }} دروس
                                                    </span>
                                                    <span class="badge bg-success-transparent text-success">
                                                        <i class="bi bi-clipboard-check me-1"></i>
                                                        {{ $sectionProgress['quizzes_completed'] }}/{{ $sectionProgress['quizzes_total'] }} اختبارات
                                                    </span>
                                                    <span class="badge bg-warning-transparent text-warning">
                                                        <i class="bi bi-question-circle me-1"></i>
                                                        {{ $sectionProgress['questions_completed'] }}/{{ $sectionProgress['questions_total'] }} أسئلة
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">لا توجد أقسام دراسية في هذا الكورس</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

