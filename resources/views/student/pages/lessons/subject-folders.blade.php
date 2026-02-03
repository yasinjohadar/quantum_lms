@extends('student.layouts.master')

@section('page-title')
    {{ $subject->name }} - عرض المجلدات
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">{{ $subject->name }}</h4>
                <p class="mb-0 text-muted">
                    @if($subject->schoolClass)
                        {{ $subject->schoolClass->name }}
                        @if($subject->schoolClass->stage)
                            - {{ $subject->schoolClass->stage->name }}
                        @endif
                    @endif
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('student.subjects.show', $subject) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-card-list me-1"></i> العرض العادي
                </a>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.subjects') }}">المواد الدراسية</a></li>
                        <li class="breadcrumb-item active">{{ $subject->name }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        @if($sections->count() > 0)
            <div class="row">
                <div class="col-12">
                    <h5 class="mb-3 fw-semibold"><i class="bi bi-folder2-open me-2 text-warning"></i> الأقسام</h5>
                    <div class="row g-3">
                        @foreach($sections as $section)
                            <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                <a href="{{ route('student.subjects.folders.section', [$subject, $section]) }}" class="text-decoration-none text-reset">
                                    <div class="card custom-card h-100 border folder-card">
                                        <div class="card-body text-center py-4">
                                            <i class="bi bi-folder2-open text-warning" style="font-size: 3rem;"></i>
                                            <h6 class="card-title mt-2 mb-1 fw-semibold">{{ $section->title }}</h6>
                                            @if($section->description)
                                                <p class="text-muted small mb-0">{{ \Illuminate\Support\Str::limit($section->description, 50) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-folder-x fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="mb-2">لا يوجد محتوى</h5>
                    <p class="text-muted">لم يتم إضافة أقسام لهذه المادة بعد</p>
                </div>
            </div>
        @endif
    </div>
</div>
@stop
