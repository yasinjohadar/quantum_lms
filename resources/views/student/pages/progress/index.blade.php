@extends('student.layouts.master')

@section('page-title')
    تقدمي الدراسي
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">تقدمي الدراسي</h4>
                <p class="mb-0 text-muted">مراقبة تقدمك في جميع الكورسات</p>
            </div>
        </div>
        <!-- End Page Header -->

        @if(count($progressList) > 0)
            <div class="row">
                @foreach($progressList as $item)
                    @php
                        $subject = $item['subject'];
                        $progress = $item['progress'];
                    @endphp
                    <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-3">
                        <div class="card custom-card h-100">
                            @if($subject->image)
                                <img src="{{ asset('storage/' . $subject->image) }}" class="card-img-top" alt="{{ $subject->name }}" style="height: 150px; object-fit: cover;">
                            @else
                                <div class="card-img-top bg-primary-gradient d-flex align-items-center justify-content-center" style="height: 150px;">
                                    <i class="bi bi-book text-white" style="font-size: 3rem;"></i>
                                </div>
                            @endif
                            <div class="card-body">
                                <h6 class="card-title fw-semibold">{{ $subject->name }}</h6>
                                @if($subject->schoolClass)
                                    <p class="card-text text-muted mb-2 small">
                                        <i class="bi bi-building me-1"></i>
                                        {{ $subject->schoolClass->name }}
                                    </p>
                                @endif
                                
                                <!-- شريط التقدم -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted">التقدم الإجمالي</small>
                                        <small class="fw-semibold">{{ number_format($progress['overall_percentage'], 1) }}%</small>
                                    </div>
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
                                            {{ $progress['lessons_completed'] }} / {{ $progress['lessons_total'] }}
                                        </small>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted">
                                            <i class="bi bi-clipboard-check me-1"></i>
                                            الاختبارات
                                        </small>
                                        <small class="fw-semibold">
                                            {{ $progress['quizzes_completed'] }} / {{ $progress['quizzes_total'] }}
                                        </small>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="bi bi-question-circle me-1"></i>
                                            الأسئلة
                                        </small>
                                        <small class="fw-semibold">
                                            {{ $progress['questions_completed'] }} / {{ $progress['questions_total'] }}
                                        </small>
                                    </div>
                                </div>

                                <a href="{{ route('student.progress.subject', $subject->id) }}" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-eye me-1"></i>
                                    عرض التفاصيل
                                </a>
                            </div>
                            <div class="card-footer">
                                <span class="card-text text-muted small">
                                    <i class="bi bi-percent me-1"></i>
                                    الدروس: {{ number_format($progress['lessons_percentage'], 1) }}% | 
                                    الاختبارات: {{ number_format($progress['quizzes_percentage'], 1) }}% | 
                                    الأسئلة: {{ number_format($progress['questions_percentage'], 1) }}%
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card custom-card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-book fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="mb-2">لا توجد كورسات مسجلة</h5>
                    <p class="text-muted">لم يتم تسجيلك في أي كورس دراسي بعد</p>
                    <a href="{{ route('student.enrollments.index') }}" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-circle me-1"></i>
                        طلب الانضمام
                    </a>
                </div>
            </div>
        @endif
    </div>
    <!-- Container closed -->
</div>
<!-- main-content closed -->
@stop

