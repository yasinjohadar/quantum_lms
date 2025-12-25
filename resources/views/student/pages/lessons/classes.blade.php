@extends('student.layouts.master')

@section('page-title')
    الصفوف المشترك فيها
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">الصفوف المشترك فيها</h4>
                <p class="mb-0 text-muted">عرض الصفوف الدراسية التي أنت مشترك فيها مع المواد الدراسية</p>
            </div>
        </div>
        <!-- End Page Header -->

        @if($classes->count() > 0)
            @foreach($classes as $classData)
                @php
                    $class = $classData['class'];
                    $subjects = $classData['subjects'];
                @endphp
                
                <!-- مواد الصف -->
                @if($subjects->count() > 0)
                    <div class="row mb-5">
                        <div class="col-12 mb-3">
                            <h5 class="fw-semibold">
                                <i class="bi bi-book-half me-2 text-primary"></i>
                                المواد الدراسية في {{ $class->name }}
                                @if($class->stage)
                                    <small class="text-muted">({{ $class->stage->name }})</small>
                                @endif
                            </h5>
                        </div>
                        @foreach($subjects as $subject)
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
                                        @if($subject->description)
                                            <p class="card-text text-muted">{{ \Illuminate\Support\Str::limit($subject->description, 100) }}</p>
                                        @endif
                                        <a href="{{ route('student.subjects.show', $subject->id) }}" class="btn btn-primary btn-sm">
                                            <i class="bi bi-eye me-1"></i>
                                            عرض المحتوى
                                        </a>
                                    </div>
                                    @php
                                        $enrollment = $subject->enrollments->first();
                                    @endphp
                                    @if($enrollment && $enrollment->enrolled_at)
                                        <div class="card-footer">
                                            <span class="card-text text-muted">
                                                <i class="bi bi-calendar me-1"></i>
                                                تاريخ الانضمام: {{ $enrollment->enrolled_at->format('Y-m-d') }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="row mb-5">
                        <div class="col-12">
                            <div class="card custom-card">
                                <div class="card-body text-center py-4">
                                    <i class="bi bi-book fs-1 text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-0">لا توجد مواد دراسية في هذا الصف</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-building fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="mb-2">لا توجد صفوف مسجلة</h5>
                    <p class="text-muted">لم يتم تسجيلك في أي صف دراسي بعد</p>
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
