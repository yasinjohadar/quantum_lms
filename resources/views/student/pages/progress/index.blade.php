@extends('student.layouts.master')

@section('page-title')
    مراقبة التقدم
@stop

@push('styles')
    @include('student.pages.lessons.partials.subject-content-breadcrumb-styles')
    @include('student.pages.progress.partials.progress-page-styles')
@endpush

@section('content')
<div class="main-content app-content">
    <div class="container-fluid pt-3">
        <nav class="student-content-breadcrumb mb-4" aria-label="مسار التنقل">
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
                        <i class="bi bi-graph-up-arrow"></i>
                        <span>تقدمي الدراسي</span>
                    </span>
                </li>
            </ol>

            <h1 class="student-content-breadcrumb__heading">
                <i class="bi bi-graph-up-arrow me-2 text-warning"></i>مراقبة التقدم
            </h1>
            <p class="student-content-breadcrumb__meta mb-0">عرض تقدمك في جميع المواد الدراسية</p>
        </nav>

        @php
            $progressList = collect($progressList ?? []);
        @endphp

        @if($progressList->isEmpty())
            <div class="card custom-card student-progress-empty">
                <div class="card-body text-center py-5">
                    <div class="student-progress-empty__icon">
                        <i class="bi bi-inbox"></i>
                    </div>
                    <h5 class="mb-2">لا توجد مواد مسجلة</h5>
                    <p class="text-muted mb-0">لم يتم تسجيلك في أي مادة دراسية بعد</p>
                    <a href="{{ route('student.classes') }}" class="btn btn-primary mt-4">
                        <i class="bi bi-plus-circle me-1"></i>
                        تصفح الصفوف والمواد
                    </a>
                </div>
            </div>
        @else
            <div class="row g-3 student-progress-grid">
                @foreach($progressList as $item)
                    @php
                        $subject = $item['subject'] ?? null;
                        $progress = $item['progress'] ?? [];
                    @endphp
                    @if($subject)
                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                            @include('student.pages.progress.partials.subject-progress-card', [
                                'subject' => $subject,
                                'progress' => $progress,
                            ])
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>
@stop
