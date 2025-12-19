@extends('student.layouts.master')

@section('page-title')
    طلب الانضمام للمواد الدراسية
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">طلب الانضمام للمواد الدراسية</h4>
                <p class="mb-0 text-muted">تصفح الصفوف والمواد المتاحة واطلب الانضمام</p>
            </div>
        </div>
        <!-- End Page Header -->

        @if($stages->count() > 0)
            @foreach($stages as $stage)
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-mortarboard me-2"></i>
                            {{ $stage->name }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($stage->classes->count() > 0)
                            <div class="row">
                                @foreach($stage->classes as $class)
                                    <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-4">
                                        <!-- كارد الصف -->
                                        <a href="{{ route('student.enrollments.class.show', $class->id) }}" class="text-decoration-none">
                                            <div class="card custom-card">
                                                @if($class->image)
                                                    <img src="{{ asset('storage/' . $class->image) }}" class="card-img-top" alt="{{ $class->name }}">
                                                @else
                                                    <div class="card-img-top bg-primary d-flex align-items-center justify-content-center" style="height: 200px;">
                                                        <i class="bi bi-building text-white" style="font-size: 4rem;"></i>
                                                    </div>
                                                @endif
                                                <div class="card-body">
                                                    <h6 class="card-title fw-semibold">{{ $class->name }}</h6>
                                                    @if($class->description)
                                                        <p class="card-text text-muted">{{ \Illuminate\Support\Str::limit($class->description, 100) }}</p>
                                                    @endif
                                                </div>
                                                <div class="card-footer">
                                                    <span class="card-text">
                                                        <i class="bi bi-book me-1"></i>
                                                        {{ $class->subjects()->where('is_active', true)->count() }} مادة دراسية
                                                    </span>
                                                    <span class="text-muted ms-2">
                                                        <i class="bi bi-arrow-left me-1"></i>
                                                        عرض المواد
                                                    </span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0 text-center">لا توجد صفوف في هذه المرحلة</p>
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            <div class="card custom-card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-book fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="mb-2">لا توجد مواد متاحة</h5>
                    <p class="text-muted mb-0">لا توجد مواد دراسية متاحة للانضمام حالياً</p>
                </div>
            </div>
        @endif
    </div>
    <!-- Container closed -->
</div>
<!-- main-content closed -->
@stop

