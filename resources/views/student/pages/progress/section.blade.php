@extends('student.layouts.master')

@section('page-title')
    التقدم في {{ $section->title }}
@stop

@push('styles')
    @include('student.partials.dashboard-widget-styles')
    @include('student.pages.lessons.partials.subject-content-breadcrumb-styles')
    @include('student.pages.progress.partials.progress-page-styles')
@endpush

@section('content')
<div class="main-content app-content">
    <div class="container-fluid pt-3">
        <nav class="student-content-breadcrumb mb-3" aria-label="مسار التنقل">
            <ol class="student-content-breadcrumb__trail">
                <li class="student-content-breadcrumb__item">
                    <a href="{{ route('student.dashboard') }}" class="student-content-breadcrumb__link">
                        <i class="bi bi-house-door-fill"></i>
                        <span>الرئيسية</span>
                    </a>
                </li>
                <li class="student-content-breadcrumb__sep" aria-hidden="true"><i class="bi bi-chevron-left"></i></li>
                <li class="student-content-breadcrumb__item">
                    <a href="{{ route('student.progress.index') }}" class="student-content-breadcrumb__link">
                        <i class="bi bi-graph-up-arrow"></i>
                        <span>تقدمي الدراسي</span>
                    </a>
                </li>
                <li class="student-content-breadcrumb__sep" aria-hidden="true"><i class="bi bi-chevron-left"></i></li>
                <li class="student-content-breadcrumb__item">
                    <a href="{{ route('student.progress.subject', $subject->id) }}" class="student-content-breadcrumb__link">
                        <i class="bi bi-book-half"></i>
                        <span>{{ $subject->name }}</span>
                    </a>
                </li>
                <li class="student-content-breadcrumb__sep" aria-hidden="true"><i class="bi bi-chevron-left"></i></li>
                <li class="student-content-breadcrumb__item" aria-current="page">
                    <span class="student-content-breadcrumb__current">
                        <i class="bi bi-folder2-open"></i>
                        <span>{{ $section->title }}</span>
                    </span>
                </li>
            </ol>
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                <div>
                    <h1 class="student-content-breadcrumb__heading mb-0">
                        <i class="bi bi-folder2-open me-2 text-warning"></i>التقدم في {{ $section->title }}
                    </h1>
                    <p class="student-content-breadcrumb__meta mb-0">عرض تفصيلي لتقدمك في هذا القسم</p>
                </div>
                <a href="{{ route('student.progress.subject', $subject->id) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-right me-1"></i>العودة للمادة
                </a>
            </div>
        </nav>

        @php
            $overall = round($progress['overall_percentage'] ?? 0, 1);
            $lessonsPct = round($progress['lessons_percentage'] ?? 0, 1);
            $quizzesPct = round($progress['quizzes_percentage'] ?? 0, 1);
            $questionsPct = round($progress['questions_percentage'] ?? 0, 1);
        @endphp

        <div class="student-progress-overview student-progress-overview--section">
            <div class="student-progress-overview__head">
                <h5 class="mb-0 fw-bold"><i class="fe fe-trending-up me-2"></i>التقدم الكلي في القسم</h5>
            </div>
            <div class="student-progress-overview__body">
                <div class="student-progress-overview__hero">
                    <div class="student-progress-overview__percent">{{ $overall }}%</div>
                    <div class="progress student-progress-overview__bar flex-grow-1">
                        <div class="progress-bar" role="progressbar"
                             style="width: {{ min(100, $overall) }}%;"
                             aria-valuenow="{{ $overall }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <div class="student-progress-overview__stats student-progress-overview__stats--three">
                    <div class="student-progress-card__stat student-progress-card__stat--lessons student-progress-detail-stat">
                        <span class="student-progress-detail-stat__pct">{{ $lessonsPct }}%</span>
                        <span class="student-progress-card__stat-label">الدروس</span>
                        <span class="student-progress-detail-stat__count">{{ $progress['lessons_completed'] ?? 0 }} / {{ $progress['lessons_total'] ?? 0 }}</span>
                        <div class="progress student-progress-detail-stat__bar">
                            <div class="progress-bar bg-success" role="progressbar"
                                 style="width: {{ min(100, $lessonsPct) }}%;"
                                 aria-valuenow="{{ $lessonsPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="student-progress-card__stat student-progress-card__stat--quizzes student-progress-detail-stat">
                        <span class="student-progress-detail-stat__pct">{{ $quizzesPct }}%</span>
                        <span class="student-progress-card__stat-label">الاختبارات</span>
                        <span class="student-progress-detail-stat__count">{{ $progress['quizzes_completed'] ?? 0 }} / {{ $progress['quizzes_total'] ?? 0 }}</span>
                        <div class="progress student-progress-detail-stat__bar">
                            <div class="progress-bar bg-info" role="progressbar"
                                 style="width: {{ min(100, $quizzesPct) }}%;"
                                 aria-valuenow="{{ $quizzesPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="student-progress-card__stat student-progress-card__stat--questions student-progress-detail-stat">
                        <span class="student-progress-detail-stat__pct">{{ $questionsPct }}%</span>
                        <span class="student-progress-card__stat-label">الأسئلة</span>
                        <span class="student-progress-detail-stat__count">{{ $progress['questions_completed'] ?? 0 }} / {{ $progress['questions_total'] ?? 0 }}</span>
                        <div class="progress student-progress-detail-stat__bar">
                            <div class="progress-bar bg-warning" role="progressbar"
                                 style="width: {{ min(100, $questionsPct) }}%;"
                                 aria-valuenow="{{ $questionsPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($section->description)
            <div class="card dashboard-panel mb-3">
                <div class="card-body py-3">
                    <h6 class="fw-bold mb-2"><i class="bi bi-info-circle me-1"></i>وصف القسم</h6>
                    <p class="text-muted mb-0">{{ $section->description }}</p>
                </div>
            </div>
        @endif
    </div>
</div>
@stop
