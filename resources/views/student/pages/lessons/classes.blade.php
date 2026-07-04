@extends('student.layouts.master')

@section('page-title')
    الصفوف المشترك فيها
@stop

@push('styles')
    @include('student.pages.lessons.partials.subject-content-breadcrumb-styles')
    @include('student.pages.lessons.partials.student-classes-page-styles')
@endpush

@section('content')
<div class="main-content app-content">
    <div class="container-fluid pt-3">
        @include('student.partials.pending-purchases-review-banner')

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
                        <i class="bi bi-building"></i>
                        <span>صفوفي</span>
                    </span>
                </li>
            </ol>

            <h1 class="student-content-breadcrumb__heading">
                <i class="bi bi-building me-2 text-warning"></i>الصفوف المشترك فيها
            </h1>
        </nav>

        @if($classes->count() === 0)
            <div class="card custom-card student-classes-empty">
                <div class="card-body text-center py-5">
                    <div class="student-classes-empty__icon">
                        <i class="bi bi-building"></i>
                    </div>
                    <h5 class="mb-2">لا توجد صفوف مسجلة</h5>
                    <p class="text-muted mb-0">لم يتم تسجيلك في أي صف دراسي بعد</p>
                    <a href="{{ route('student.enrollments.index') }}" class="btn btn-primary mt-4">
                        <i class="bi bi-plus-circle me-1"></i>
                        طلب الانضمام
                    </a>
                </div>
            </div>
        @else
            <div class="card custom-card student-classes-panel">
                <div class="card-header border-bottom-0 pb-0">
                    <ul class="nav nav-tabs student-class-tabs card-header-tabs" role="tablist">
                        @foreach($classes as $classData)
                            @php
                                $class = $classData['class'];
                            @endphp
                            <li class="nav-item" role="presentation">
                                <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                                   id="class-{{ $class->id }}-tab"
                                   data-bs-toggle="tab"
                                   data-bs-target="#class-{{ $class->id }}"
                                   href="#class-{{ $class->id }}"
                                   role="tab"
                                   aria-controls="class-{{ $class->id }}"
                                   aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                    {{ $class->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="card-body pt-4">
                    <div class="tab-content" id="student-class-tab-content">
                        @foreach($classes as $classData)
                            @php
                                $class = $classData['class'];
                                $subjects = $classData['subjects'];
                            @endphp
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                 id="class-{{ $class->id }}"
                                 role="tabpanel"
                                 aria-labelledby="class-{{ $class->id }}-tab">
                                @include('student.pages.lessons.partials.class-section-content', [
                                    'class' => $class,
                                    'subjects' => $subjects,
                                ])
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@stop
