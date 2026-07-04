@extends('student.layouts.master')

@section('page-title')
    تقدمي في {{ $subject->name }}
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
                <li class="student-content-breadcrumb__item" aria-current="page">
                    <span class="student-content-breadcrumb__current">
                        <i class="bi bi-book-half"></i>
                        <span>{{ $subject->name }}</span>
                    </span>
                </li>
            </ol>
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                <div>
                    <h1 class="student-content-breadcrumb__heading mb-0">
                        <i class="bi bi-book-half me-2 text-warning"></i>تقدمي في {{ $subject->name }}
                    </h1>
                    <p class="student-content-breadcrumb__meta mb-0">عرض تفصيلي لتقدمك في هذه المادة الدراسية</p>
                </div>
                <a href="{{ route('student.progress.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-right me-1"></i>العودة للقائمة
                </a>
            </div>
        </nav>

        @php
            $overall = round($progress['overall_percentage'] ?? 0, 1);
        @endphp

        <div class="student-progress-overview">
            <div class="student-progress-overview__head">
                <h5 class="mb-0 fw-bold"><i class="fe fe-trending-up me-2"></i>التقدم الكلي</h5>
            </div>
            <div class="student-progress-overview__body">
                <div class="student-progress-overview__percent">{{ $overall }}%</div>
                <div class="progress student-progress-overview__bar">
                    <div class="progress-bar" role="progressbar"
                         style="width: {{ min(100, $overall) }}%;"
                         aria-valuenow="{{ $overall }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                <div class="student-progress-overview__stats">
                    <div class="student-progress-card__stat student-progress-card__stat--lessons">
                        <span class="student-progress-card__stat-value">{{ $progress['lessons_completed'] ?? 0 }}</span>
                        <span class="student-progress-card__stat-label">دروس مكتملة</span>
                        <span class="student-progress-card__stat-total">من {{ $progress['lessons_total'] ?? 0 }}</span>
                    </div>
                    <div class="student-progress-card__stat student-progress-card__stat--quizzes">
                        <span class="student-progress-card__stat-value">{{ $progress['quizzes_completed'] ?? 0 }}</span>
                        <span class="student-progress-card__stat-label">اختبارات</span>
                        <span class="student-progress-card__stat-total">من {{ $progress['quizzes_total'] ?? 0 }}</span>
                    </div>
                    <div class="student-progress-card__stat student-progress-card__stat--questions">
                        <span class="student-progress-card__stat-value">{{ $progress['questions_completed'] ?? 0 }}</span>
                        <span class="student-progress-card__stat-label">أسئلة</span>
                        <span class="student-progress-card__stat-total">من {{ $progress['questions_total'] ?? 0 }}</span>
                    </div>
                    <div class="student-progress-card__stat student-progress-card__stat--lessons">
                        <span class="student-progress-card__stat-value">{{ $sections->count() }}</span>
                        <span class="student-progress-card__stat-label">أقسام</span>
                        <span class="student-progress-card__stat-total">إجمالي</span>
                    </div>
                </div>

                @if(isset($stats['attendance']))
                    <div class="student-progress-overview__attendance">
                        <i class="bi bi-clock-history me-1"></i>
                        <strong>الحضور ومدة المشاهدة:</strong>
                        حضرت <strong>{{ $stats['attendance']['attended_lessons'] }}</strong> من
                        <strong>{{ $stats['attendance']['total_lessons'] }}</strong> درس
                        ({{ $stats['attendance']['lessons_attendance_percentage'] }}%).
                        شاهدت <strong>{{ $stats['attendance']['watch_time_percentage'] }}%</strong> من مدة الكورس.
                    </div>
                @endif
            </div>
        </div>

        @if(isset($sections) && $sections->count() > 0)
            <div class="card dashboard-panel student-progress-sections-panel">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fe fe-layers me-2"></i>التقدم حسب الأقسام</h5>
                </div>
                <div class="card-body pt-3">
                    @foreach($sections as $section)
                        @php
                            $sectionProgress = $sectionsProgress[$section->id] ?? [];
                            $sectionPercentage = round($sectionProgress['overall_percentage'] ?? 0, 1);
                        @endphp
                        <div class="student-progress-section-row" style="animation-delay: {{ $loop->index * 0.04 }}s;">
                            <div class="student-progress-section-row__head">
                                <div>
                                    <h3 class="student-progress-section-row__title">{{ $section->title }}</h3>
                                    @if($section->description)
                                        <p class="student-progress-section-row__desc">{{ Str::limit($section->description, 100) }}</p>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <span class="student-progress-section-row__percent">{{ $sectionPercentage }}%</span>
                                    <span class="student-progress-section-row__percent-label">التقدم</span>
                                </div>
                            </div>

                            <div class="progress student-progress-section-row__bar">
                                <div class="progress-bar" role="progressbar"
                                     style="width: {{ min(100, $sectionPercentage) }}%;"
                                     aria-valuenow="{{ $sectionPercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>

                            <div class="student-progress-section-row__footer">
                                <div class="student-progress-section-row__metrics">
                                    <span><i class="bi bi-book me-1"></i>دروس <strong>{{ $sectionProgress['lessons_completed'] ?? 0 }}/{{ $sectionProgress['lessons_total'] ?? 0 }}</strong></span>
                                    <span><i class="bi bi-clipboard-check me-1"></i>اختبارات <strong>{{ $sectionProgress['quizzes_completed'] ?? 0 }}/{{ $sectionProgress['quizzes_total'] ?? 0 }}</strong></span>
                                    <span><i class="bi bi-question-circle me-1"></i>أسئلة <strong>{{ $sectionProgress['questions_completed'] ?? 0 }}/{{ $sectionProgress['questions_total'] ?? 0 }}</strong></span>
                                </div>
                                <a href="{{ route('student.progress.section', $section->id) }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-eye me-1"></i>التفاصيل
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="card custom-card student-progress-empty">
                <div class="card-body text-center py-5">
                    <div class="student-progress-empty__icon">
                        <i class="bi bi-inbox"></i>
                    </div>
                    <h5 class="mb-2 text-muted">لا توجد أقسام</h5>
                    <p class="text-muted mb-0">هذه المادة لا تحتوي على أقسام دراسية بعد</p>
                </div>
            </div>
        @endif
    </div>
</div>
@stop
