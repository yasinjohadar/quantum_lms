@extends('student.layouts.master')

@section('page-title')
    التقدم في {{ $section->title }}
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">التقدم في {{ $section->title }}</h4>
                <p class="mb-0 text-muted">عرض تفصيلي لتقدمك في هذا القسم</p>
            </div>
            <div>
                <a href="{{ route('student.progress.subject', $subject->id) }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-right me-1"></i>
                    العودة للمادة
                </a>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Overall Progress Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">التقدم الكلي في القسم</h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="text-primary mb-3">{{ round($progress['overall_percentage'] ?? 0, 1) }}%</h2>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-primary" role="progressbar" 
                                         style="width: {{ $progress['overall_percentage'] ?? 0 }}%"
                                         aria-valuenow="{{ $progress['overall_percentage'] ?? 0 }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        {{ round($progress['overall_percentage'] ?? 0, 1) }}%
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <div class="border rounded p-2">
                                            <h4 class="mb-0 text-success">{{ $progress['lessons_completed'] ?? 0 }}</h4>
                                            <small class="text-muted">دروس مكتملة</small>
                                            <div><small class="text-muted">من {{ $progress['lessons_total'] ?? 0 }}</small></div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="border rounded p-2">
                                            <h4 class="mb-0 text-info">{{ $progress['quizzes_completed'] ?? 0 }}</h4>
                                            <small class="text-muted">اختبارات</small>
                                            <div><small class="text-muted">من {{ $progress['quizzes_total'] ?? 0 }}</small></div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="border rounded p-2">
                                            <h4 class="mb-0 text-warning">{{ $progress['questions_completed'] ?? 0 }}</h4>
                                            <small class="text-muted">أسئلة</small>
                                            <div><small class="text-muted">من {{ $progress['questions_total'] ?? 0 }}</small></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Description -->
        @if($section->description)
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">وصف القسم</h5>
                    <p class="text-muted">{{ $section->description }}</p>
                </div>
            </div>
        @endif

        <!-- Progress by Type -->
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="text-success mb-3">الدروس</h5>
                        <h2 class="text-success">{{ round($progress['lessons_percentage'] ?? 0, 1) }}%</h2>
                        <p class="text-muted mb-0">
                            {{ $progress['lessons_completed'] ?? 0 }} / {{ $progress['lessons_total'] ?? 0 }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="text-info mb-3">الاختبارات</h5>
                        <h2 class="text-info">{{ round($progress['quizzes_percentage'] ?? 0, 1) }}%</h2>
                        <p class="text-muted mb-0">
                            {{ $progress['quizzes_completed'] ?? 0 }} / {{ $progress['quizzes_total'] ?? 0 }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="text-warning mb-3">الأسئلة</h5>
                        <h2 class="text-warning">{{ round($progress['questions_percentage'] ?? 0, 1) }}%</h2>
                        <p class="text-muted mb-0">
                            {{ $progress['questions_completed'] ?? 0 }} / {{ $progress['questions_total'] ?? 0 }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End::app-content -->
@stop

