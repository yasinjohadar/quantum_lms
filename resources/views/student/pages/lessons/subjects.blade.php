@extends('student.layouts.master')

@section('page-title')
    المواد الدراسية
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">المواد الدراسية</h4>
                <p class="mb-0 text-muted">جميع المواد المسجل فيها</p>
            </div>
        </div>
        <!-- End Page Header -->

        @if($subjects->count() > 0)
            <div class="row">
                @foreach($subjects as $subject)
                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    @if($subject->image)
                                        <img src="{{ asset('storage/' . $subject->image) }}" alt="{{ $subject->name }}" class="rounded me-3" style="width: 80px; height: 80px; object-fit: cover;">
                                    @else
                                        <div class="bg-primary text-white rounded d-flex align-items-center justify-content-center me-3" style="width: 80px; height: 80px;">
                                            <i class="bi bi-book fs-2"></i>
                                        </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1">{{ $subject->name }}</h5>
                                        @if($subject->schoolClass)
                                            <p class="text-muted mb-0">
                                                <i class="bi bi-building me-1"></i>
                                                {{ $subject->schoolClass->name }}
                                                @if($subject->schoolClass->stage)
                                                    - {{ $subject->schoolClass->stage->name }}
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($subject->description)
                                    <p class="text-muted mb-3">{{ \Illuminate\Support\Str::limit($subject->description, 100) }}</p>
                                @endif
                                
                                @php
                                    $enrollment = $subject->enrollments->first();
                                @endphp
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        @if($enrollment && $enrollment->enrolled_at)
                                            <small class="text-muted">
                                                <i class="bi bi-calendar me-1"></i>
                                                {{ $enrollment->enrolled_at->format('Y-m-d') }}
                                            </small>
                                        @endif
                                    </div>
                                    <a href="{{ route('student.subjects.show', $subject->id) }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-play-circle me-1"></i>
                                        عرض المحتوى
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
                    <i class="bi bi-book fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="mb-2">لا توجد مواد مسجلة</h5>
                    <p class="text-muted">لم يتم تسجيلك في أي مادة دراسية بعد</p>
                </div>
            </div>
        @endif
    </div>
    <!-- Container closed -->
</div>
<!-- main-content closed -->
@stop

