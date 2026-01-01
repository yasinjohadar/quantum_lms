@extends('admin.layouts.master')

@section('page-title')
    تقدم الطالب في {{ $subject->name }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تقدم الطالب في {{ $subject->name }}</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.student-progress.index') }}">مراقبة تقدم الطلاب</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.student-progress.show', $student->id) }}">{{ $student->name }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $subject->name }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Student & Subject Info -->
        <div class="card custom-card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            @if($student->photo)
                                <img src="{{ asset('storage/' . $student->photo) }}" 
                                     alt="{{ $student->name }}" 
                                     class="rounded-circle me-3" 
                                     style="width: 60px; height: 60px; object-fit: cover;">
                            @else
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                     style="width: 60px; height: 60px;">
                                    <i class="bi bi-person"></i>
                                </div>
                            @endif
                            <div>
                                <h5 class="mb-0">{{ $student->name }}</h5>
                                <p class="text-muted mb-0">{{ $student->email }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <h4 class="mb-1">{{ $subject->name }}</h4>
                        @if($subject->schoolClass)
                            <span class="badge bg-info">{{ $subject->schoolClass->name }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @php
            $progress = $stats['progress'] ?? [];
            $overallPercentage = $progress['overall_percentage'] ?? 0;
        @endphp

        <!-- Overall Progress -->
        <div class="card custom-card mb-4">
            <div class="card-header">
                <h5 class="mb-0">نظرة عامة على التقدم</h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="text-center mb-3">
                            <h2 class="mb-0">{{ number_format($overallPercentage, 1) }}%</h2>
                            <p class="text-muted">نسبة الإكمال الإجمالية</p>
                        </div>
                        <div class="progress" style="height: 30px;">
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
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <i class="bi bi-book fs-2 text-primary mb-2 d-block"></i>
                            <h4 class="mb-1">{{ $progress['lessons_completed'] ?? 0 }}/{{ $progress['lessons_total'] ?? 0 }}</h4>
                            <p class="text-muted mb-0">الدروس</p>
                            <small class="text-primary">
                                {{ number_format($progress['lessons_percentage'] ?? 0, 1) }}%
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <i class="bi bi-file-check fs-2 text-success mb-2 d-block"></i>
                            <h4 class="mb-1">{{ $progress['quizzes_completed'] ?? 0 }}/{{ $progress['quizzes_total'] ?? 0 }}</h4>
                            <p class="text-muted mb-0">الاختبارات</p>
                            <small class="text-success">
                                {{ number_format($progress['quizzes_percentage'] ?? 0, 1) }}%
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <i class="bi bi-question-circle fs-2 text-warning mb-2 d-block"></i>
                            <h4 class="mb-1">{{ $progress['questions_completed'] ?? 0 }}/{{ $progress['questions_total'] ?? 0 }}</h4>
                            <p class="text-muted mb-0">الأسئلة</p>
                            <small class="text-warning">
                                {{ number_format($progress['questions_percentage'] ?? 0, 1) }}%
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sections Progress -->
        <div class="card custom-card">
            <div class="card-header">
                <h5 class="mb-0">تقدم الأقسام</h5>
            </div>
            <div class="card-body">
                @if(isset($stats['sections']) && count($stats['sections']) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>القسم</th>
                                    <th>الدروس</th>
                                    <th>الاختبارات</th>
                                    <th>الأسئلة</th>
                                    <th>نسبة التقدم</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['sections'] as $sectionItem)
                                    @php
                                        $section = $sectionItem['section'] ?? null;
                                        $sectionProgress = $sectionItem['progress'] ?? [];
                                        $sectionPercentage = $sectionProgress['overall_percentage'] ?? 0;
                                    @endphp
                                    @if($section)
                                        <tr>
                                            <td>
                                                <h6 class="mb-0">{{ $section->name }}</h6>
                                                @if($section->description)
                                                    <small class="text-muted">{{ Str::limit($section->description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    {{ $sectionProgress['lessons_completed'] ?? 0 }}/{{ $sectionProgress['lessons_total'] ?? 0 }}
                                                </span>
                                                <small class="text-muted d-block">
                                                    {{ number_format($sectionProgress['lessons_percentage'] ?? 0, 1) }}%
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">
                                                    {{ $sectionProgress['quizzes_completed'] ?? 0 }}/{{ $sectionProgress['quizzes_total'] ?? 0 }}
                                                </span>
                                                <small class="text-muted d-block">
                                                    {{ number_format($sectionProgress['quizzes_percentage'] ?? 0, 1) }}%
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning">
                                                    {{ $sectionProgress['questions_completed'] ?? 0 }}/{{ $sectionProgress['questions_total'] ?? 0 }}
                                                </span>
                                                <small class="text-muted d-block">
                                                    {{ number_format($sectionProgress['questions_percentage'] ?? 0, 1) }}%
                                                </small>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-grow-1 me-2">
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar bg-{{ $sectionPercentage >= 75 ? 'success' : ($sectionPercentage >= 50 ? 'warning' : 'danger') }}" 
                                                                 role="progressbar" 
                                                                 style="width: {{ $sectionPercentage }}%"
                                                                 aria-valuenow="{{ $sectionPercentage }}" 
                                                                 aria-valuemin="0" 
                                                                 aria-valuemax="100">
                                                                {{ number_format($sectionPercentage, 1) }}%
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-folder fs-1 text-muted mb-3 d-block"></i>
                        <h5 class="mb-2">لا يوجد أقسام</h5>
                        <p class="text-muted">لا توجد أقسام في هذا الكورس حالياً</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-4">
            <a href="{{ route('admin.student-progress.show', $student->id) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-right me-1"></i>
                العودة لتقدم الطالب
            </a>
        </div>
    </div>
</div>
@stop



