@extends('student.layouts.master')

@section('page-title')
    نتائج الاختبارات
@stop

@push('styles')
    @include('student.partials.dashboard-widget-styles')
    @include('student.pages.lessons.partials.subject-content-breadcrumb-styles')
    @include('student.pages.quizzes.partials.quizzes-page-styles')
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
                    <a href="{{ route('student.quizzes.index') }}" class="student-content-breadcrumb__link">
                        <i class="bi bi-clipboard-check"></i>
                        <span>الاختبارات</span>
                    </a>
                </li>
                <li class="student-content-breadcrumb__sep" aria-hidden="true"><i class="bi bi-chevron-left"></i></li>
                <li class="student-content-breadcrumb__item" aria-current="page">
                    <span class="student-content-breadcrumb__current">
                        <i class="bi bi-bar-chart-line"></i>
                        <span>نتائج الاختبارات</span>
                    </span>
                </li>
            </ol>
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                <div>
                    <h1 class="student-content-breadcrumb__heading mb-0">
                        <i class="bi bi-bar-chart-line me-2 text-warning"></i>نتائج الاختبارات
                    </h1>
                    <p class="student-content-breadcrumb__meta mb-0">سجل محاولاتك ودرجاتك في جميع الاختبارات</p>
                </div>
                <a href="{{ route('student.quizzes.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-right me-1"></i>الاختبارات المتاحة
                </a>
            </div>
        </nav>

        <div class="card dashboard-panel mb-3">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('student.quizzes.results') }}" class="student-quizzes-toolbar mb-0">
                    <div class="student-quizzes-filters student-quizzes-filters--results">
                        <div class="student-quizzes-filter-field">
                            <label class="student-quizzes-filter-field__label">المادة</label>
                            <select name="subject_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">جميع المواد</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="student-quizzes-filter-field">
                            <label class="student-quizzes-filter-field__label">الحالة</label>
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">جميع الحالات</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
                                <option value="graded" {{ request('status') == 'graded' ? 'selected' : '' }}>مصحح</option>
                                <option value="timeout" {{ request('status') == 'timeout' ? 'selected' : '' }}>انتهى الوقت</option>
                            </select>
                        </div>
                        <div class="student-quizzes-filter-field">
                            <label class="student-quizzes-filter-field__label">النتيجة</label>
                            <select name="passed" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">الكل</option>
                                <option value="1" {{ request('passed') == '1' ? 'selected' : '' }}>ناجح</option>
                                <option value="0" {{ request('passed') == '0' ? 'selected' : '' }}>راسب</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($attempts->count() > 0)
            <div class="student-quiz-results-list">
                @foreach($attempts as $row)
                    @if(($row['kind'] ?? 'quiz') === 'interactive')
                        @include('student.pages.quizzes.partials.ile-result-row', ['attempt' => $row['attempt']])
                    @else
                        @include('student.pages.quizzes.partials.quiz-result-row', ['attempt' => $row['attempt'] ?? $row])
                    @endif
                @endforeach
            </div>

            @if($attempts->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $attempts->links() }}
                </div>
            @endif
        @else
            <div class="student-quizzes-empty">
                <div class="student-quizzes-empty__icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <h5 class="mb-2 text-muted">لا توجد نتائج</h5>
                <p class="text-muted mb-3">لم يتم العثور على أي نتائج اختبارات</p>
                <a href="{{ route('student.quizzes.index') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>عرض الاختبارات المتاحة
                </a>
            </div>
        @endif
    </div>
</div>
@stop
