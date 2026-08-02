@extends('student.layouts.master')

@section('page-title')
    الاختبارات المتاحة
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
                <li class="student-content-breadcrumb__item" aria-current="page">
                    <span class="student-content-breadcrumb__current">
                        <i class="bi bi-clipboard-check"></i>
                        <span>الاختبارات</span>
                    </span>
                </li>
            </ol>
            <h1 class="student-content-breadcrumb__heading">
                <i class="bi bi-clipboard-check me-2 text-warning"></i>الاختبارات المتاحة
            </h1>
            <p class="student-content-breadcrumb__meta mb-0">اختباراتك الجاهزة للبدء ومتابعة التقدم</p>
        </nav>

        <div class="card dashboard-panel mb-4">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('student.quizzes.index') }}" class="student-quizzes-toolbar mb-0">
                    <div class="student-quizzes-filters">
                        <select name="subject_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">جميع المواد</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">جميع الحالات</option>
                            <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>متاح الآن</option>
                            <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>قادم</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>منتهي</option>
                        </select>
                    </div>
                    <a href="{{ route('student.quizzes.results') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-bar-chart-line me-1"></i>
                        <span class="d-none d-sm-inline">نتائج الاختبارات</span>
                    </a>
                </form>
            </div>
        </div>

        @if($quizzes->count() > 0)
            <div class="row g-3 student-quizzes-grid">
                @foreach($quizzes as $row)
                    @php
                        $kind = is_array($row) ? ($row['kind'] ?? 'quiz') : 'quiz';
                        $item = is_array($row) ? ($row['model'] ?? $row) : $row;
                    @endphp
                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                        @if($kind === 'interactive')
                            @include('student.pages.quizzes.partials.ile-card', ['experience' => $item])
                        @else
                            @include('student.pages.quizzes.partials.quiz-card', ['quiz' => $item])
                        @endif
                    </div>
                @endforeach
            </div>

            @if($quizzes->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $quizzes->links() }}
                </div>
            @endif
        @else
            <div class="student-quizzes-empty">
                <div class="student-quizzes-empty__icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <h5 class="mb-2 text-muted">لا توجد اختبارات متاحة</h5>
                <p class="text-muted mb-0">لم يتم العثور على اختبارات تطابق الفلتر المحدد</p>
            </div>
        @endif
    </div>
</div>
@stop
