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
                <p class="mb-0 text-muted">عرض تفصيلي لتقدمك في جميع المواد الدراسية</p>
            </div>
        </div>
        <!-- End Page Header -->

        @if(isset($progressList) && count($progressList) > 0)
            <div class="row">
                @foreach($progressList as $item)
                    @php
                        $subject = $item['subject'];
                        $progress = $item['progress'];
                        $progressPercentage = $progress['overall_percentage'] ?? 0;
                    @endphp
                    <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">{{ $subject->name }}</h5>
                                    @if($subject->schoolClass)
                                        <small class="text-muted">
                                            {{ $subject->schoolClass->name }}
                                            @if($subject->schoolClass->stage)
                                                - {{ $subject->schoolClass->stage->name }}
                                            @endif
                                        </small>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <h4 class="mb-0 text-primary">{{ round($progressPercentage, 1) }}%</h4>
                                    <small class="text-muted">التقدم الكلي</small>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Progress Bar -->
                                <div class="progress mb-3" style="height: 8px;">
                                    <div class="progress-bar bg-primary" role="progressbar" 
                                         style="width: {{ $progressPercentage }}%"
                                         aria-valuenow="{{ $progressPercentage }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>

                                <!-- Statistics -->
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="mb-2">
                                            <h6 class="mb-0 text-primary">{{ $progress['lessons_completed'] ?? 0 }}</h6>
                                            <small class="text-muted">دروس مكتملة</small>
                                        </div>
                                        <div>
                                            <small class="text-muted">من {{ $progress['total_lessons'] ?? 0 }}</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="mb-2">
                                            <h6 class="mb-0 text-success">{{ $progress['quizzes_completed'] ?? 0 }}</h6>
                                            <small class="text-muted">اختبارات مكتملة</small>
                                        </div>
                                        <div>
                                            <small class="text-muted">من {{ $progress['total_quizzes'] ?? 0 }}</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="mb-2">
                                            <h6 class="mb-0 text-info">{{ $progress['questions_answered'] ?? 0 }}</h6>
                                            <small class="text-muted">أسئلة مجابة</small>
                                        </div>
                                        <div>
                                            <small class="text-muted">من {{ $progress['total_questions'] ?? 0 }}</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sections Progress -->
                                @if(isset($progress['sections']) && count($progress['sections']) > 0)
                                    <hr class="my-3">
                                    <h6 class="mb-2">التقدم حسب الأقسام:</h6>
                                    @foreach($progress['sections'] as $sectionProgress)
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small class="text-muted">{{ $sectionProgress['section_name'] ?? 'قسم' }}</small>
                                                <small class="text-primary">{{ round($sectionProgress['percentage'] ?? 0, 1) }}%</small>
                                            </div>
                                            <div class="progress" style="height: 4px;">
                                                <div class="progress-bar bg-info" role="progressbar" 
                                                     style="width: {{ $sectionProgress['percentage'] ?? 0 }}%">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                                <!-- Action Button -->
                                <div class="mt-3">
                                    <a href="{{ route('student.progress.subject', $subject->id) }}" 
                                       class="btn btn-primary btn-sm w-100">
                                        <i class="bi bi-eye me-1"></i>
                                        عرض التفاصيل
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-graph-up fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="mb-2">لا يوجد تقدم لعرضه</h5>
                    <p class="text-muted">لم يتم تسجيلك في أي مادة دراسية بعد، أو لا يوجد تقدم مسجل</p>
                    <a href="{{ route('student.subjects') }}" class="btn btn-primary mt-3">
                        <i class="bi bi-book me-1"></i>
                        تصفح المواد الدراسية
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
<!-- End::app-content -->
@stop

