@extends('student.layouts.master')

@section('page-title')
    مراقبة التقدم
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">مراقبة التقدم</h4>
                <p class="mb-0 text-muted">عرض تقدمك في جميع المواد الدراسية</p>
            </div>
        </div>
        <!-- End Page Header -->

        @php
            $progressList = collect($progressList ?? []);
        @endphp

        @if($progressList->isEmpty())
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="mb-2">لا توجد مواد مسجلة</h5>
                    <p class="text-muted">لم يتم تسجيلك في أي مادة دراسية بعد</p>
                    <a href="{{ route('student.subjects') }}" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-circle me-1"></i>
                        تصفح المواد
                    </a>
                </div>
            </div>
        @else
            <div class="row">
                @foreach($progressList as $item)
                    @php
                        $subject = $item['subject'] ?? null;
                        $progress = $item['progress'] ?? [];
                        $overallPercentage = $progress['overall_percentage'] ?? 0;
                    @endphp
                    @if($subject)
                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0">{{ $subject->name }}</h5>
                                        <span class="badge bg-primary-transparent text-primary fs-12">
                                            {{ round($overallPercentage, 1) }}%
                                        </span>
                                    </div>
                                    @if($subject->schoolClass)
                                        <small class="text-muted">
                                            {{ $subject->schoolClass->name }}
                                            @if($subject->schoolClass->stage)
                                                - {{ $subject->schoolClass->stage->name }}
                                            @endif
                                        </small>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <div class="progress mb-3" style="height: 10px;">
                                        <div class="progress-bar bg-primary" role="progressbar" 
                                             style="width: {{ $overallPercentage }}%"
                                             aria-valuenow="{{ $overallPercentage }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>

                                    <div class="row text-center mb-3">
                                        <div class="col-4">
                                            <div class="border rounded p-2">
                                                <h6 class="mb-0 text-success">{{ $progress['lessons_completed'] ?? 0 }}</h6>
                                                <small class="text-muted">دروس</small>
                                                <div><small class="text-muted">من {{ $progress['lessons_total'] ?? 0 }}</small></div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="border rounded p-2">
                                                <h6 class="mb-0 text-info">{{ $progress['quizzes_completed'] ?? 0 }}</h6>
                                                <small class="text-muted">اختبارات</small>
                                                <div><small class="text-muted">من {{ $progress['quizzes_total'] ?? 0 }}</small></div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="border rounded p-2">
                                                <h6 class="mb-0 text-warning">{{ $progress['questions_completed'] ?? 0 }}</h6>
                                                <small class="text-muted">أسئلة</small>
                                                <div><small class="text-muted">من {{ $progress['questions_total'] ?? 0 }}</small></div>
                                            </div>
                                        </div>
                                    </div>

                                    <a href="{{ route('student.progress.subject', $subject->id) }}" class="btn btn-primary btn-sm w-100">
                                        <i class="bi bi-eye me-1"></i>
                                        عرض التفاصيل
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>
<!-- End::app-content -->
@stop

