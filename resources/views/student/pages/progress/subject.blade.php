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
                <p class="mb-0 text-muted">عرض تفصيلي لتقدمك في هذه المادة الدراسية</p>
            </div>
            <div>
                <a href="{{ route('student.progress.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-right me-1"></i>
                    العودة للقائمة
                </a>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Overall Progress Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">التقدم الكلي</h5>
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
                                    <div class="col-6">
                                        <div class="border rounded p-2">
                                            <h4 class="mb-0 text-primary">{{ $sections->count() }}</h4>
                                            <small class="text-muted">أقسام</small>
                                            <div><small class="text-muted">إجمالي</small></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sections Progress -->
        @if(isset($sections) && $sections->count() > 0)
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">التقدم حسب الأقسام</h5>
                        </div>
                        <div class="card-body">
                            @foreach($sections as $section)
                                @php
                                    $sectionProgress = $sectionsProgress[$section->id] ?? [];
                                    $sectionPercentage = $sectionProgress['overall_percentage'] ?? 0;
                                @endphp
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div>
                                                <h5 class="mb-1">{{ $section->title }}</h5>
                                                @if($section->description)
                                                    <p class="text-muted mb-0">{{ \Illuminate\Support\Str::limit($section->description, 100) }}</p>
                                                @endif
                                            </div>
                                            <div class="text-end">
                                                <h4 class="mb-0 text-primary">{{ round($sectionPercentage, 1) }}%</h4>
                                                <small class="text-muted">التقدم</small>
                                            </div>
                                        </div>

                                        <div class="progress mb-3" style="height: 10px;">
                                            <div class="progress-bar bg-info" role="progressbar" 
                                                 style="width: {{ $sectionPercentage }}%"
                                                 aria-valuenow="{{ $sectionPercentage }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>

                                        <div class="row text-center">
                                            <div class="col-3">
                                                <small class="text-muted d-block">دروس</small>
                                                <strong>{{ $sectionProgress['lessons_completed'] ?? 0 }}/{{ $sectionProgress['lessons_total'] ?? 0 }}</strong>
                                            </div>
                                            <div class="col-3">
                                                <small class="text-muted d-block">اختبارات</small>
                                                <strong>{{ $sectionProgress['quizzes_completed'] ?? 0 }}/{{ $sectionProgress['quizzes_total'] ?? 0 }}</strong>
                                            </div>
                                            <div class="col-3">
                                                <small class="text-muted d-block">أسئلة</small>
                                                <strong>{{ $sectionProgress['questions_completed'] ?? 0 }}/{{ $sectionProgress['questions_total'] ?? 0 }}</strong>
                                            </div>
                                            <div class="col-3">
                                                <a href="{{ route('student.progress.section', $section->id) }}" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-eye me-1"></i>
                                                    التفاصيل
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="mb-2">لا توجد أقسام</h5>
                    <p class="text-muted">هذه المادة لا تحتوي على أقسام دراسية بعد</p>
                </div>
            </div>
        @endif
    </div>
</div>
<!-- End::app-content -->
@stop
