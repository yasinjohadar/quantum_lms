@extends('student.layouts.master')

@section('page-title')
    تقدمي في {{ $subject->name }}
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">تقدمي في {{ $subject->name }}</h4>
                <p class="mb-0 text-muted">مراقبة تقدمك في هذا الكورس</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.progress.index') }}">تقدمي الدراسي</a></li>
                    <li class="breadcrumb-item active">{{ $subject->name }}</li>
                </ol>
            </nav>
        </div>
        <!-- End Page Header -->

        @php
            $progress = $stats['progress'];
        @endphp

        <!-- إحصائيات عامة -->
        <div class="row mb-4">
            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-3">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 text-muted">التقدم الإجمالي</h6>
                                <h3 class="mb-0 fw-bold text-primary">{{ number_format($progress['overall_percentage'], 1) }}%</h3>
                            </div>
                            <div class="bg-primary-transparent rounded p-3">
                                <i class="bi bi-graph-up text-primary fs-2"></i>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 10px;">
                            <div class="progress-bar bg-primary" role="progressbar" 
                                 style="width: {{ $progress['overall_percentage'] }}%"
                                 aria-valuenow="{{ $progress['overall_percentage'] }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-3">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 text-muted">الدروس</h6>
                                <h3 class="mb-0 fw-bold text-info">
                                    {{ $progress['lessons_completed'] }} / {{ $progress['lessons_total'] }}
                                </h3>
                                <small class="text-muted">{{ number_format($progress['lessons_percentage'], 1) }}%</small>
                            </div>
                            <div class="bg-info-transparent rounded p-3">
                                <i class="bi bi-play-circle text-info fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-3">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 text-muted">الاختبارات</h6>
                                <h3 class="mb-0 fw-bold text-success">
                                    {{ $progress['quizzes_completed'] }} / {{ $progress['quizzes_total'] }}
                                </h3>
                                <small class="text-muted">{{ number_format($progress['quizzes_percentage'], 1) }}%</small>
                            </div>
                            <div class="bg-success-transparent rounded p-3">
                                <i class="bi bi-clipboard-check text-success fs-2"></i>
                            </div>
                        </div>
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
                                                <div class="d-flex flex-wrap gap-2 mb-3">
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

                                                <a href="{{ route('student.progress.section', $section->id) }}" class="btn btn-sm btn-outline-primary w-100">
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
                                <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">لا توجد أقسام دراسية في هذا الكورس</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Container closed -->
</div>
<!-- main-content closed -->
@stop

