@extends('student.layouts.master')

@section('page-title')
    {{ $section->title }} - {{ $subject->name }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">{{ $section->title }}</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.subjects') }}">المواد الدراسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.subjects.show', $subject) }}">{{ $subject->name }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.subjects.folders', $subject) }}">عرض المجلدات</a></li>
                        <li class="breadcrumb-item active">{{ $section->title }}</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('student.subjects.show', $subject) }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-card-list me-1"></i> العرض العادي
            </a>
        </div>

        @if($section->type === \App\Models\SubjectSection::TYPE_QUIZZES)
            {{-- قسم الاختبارات: أقسام فرعية ثم الوحدات، الاختبارات تظهر داخل صفحة الوحدة --}}
            @if($children->count() > 0)
                <div class="mb-4">
                    <h5 class="mb-3 fw-semibold"><i class="bi bi-folder2 me-2 text-warning"></i> أقسام فرعية</h5>
                    <div class="row g-3">
                        @foreach($children as $child)
                            <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                <a href="{{ route('student.subjects.folders.section', [$subject, $child]) }}" class="text-decoration-none text-reset">
                                    <div class="card custom-card h-100 border folder-card">
                                        <div class="card-body text-center py-4">
                                            <i class="bi bi-folder2 text-warning" style="font-size: 2.5rem;"></i>
                                            <h6 class="card-title mt-2 mb-0 fw-semibold">{{ $child->title }}</h6>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($units->count() > 0)
                <div>
                    <h5 class="mb-3 fw-semibold"><i class="bi bi-folder2 me-2 text-secondary"></i> الوحدات</h5>
                    <div class="row g-3">
                        @foreach($units as $unit)
                            <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                <a href="{{ route('student.subjects.folders.unit', [$subject, $section, $unit]) }}" class="text-decoration-none text-reset">
                                    <div class="card custom-card h-100 border folder-card">
                                        <div class="card-body text-center py-4">
                                            <i class="bi bi-folder2 text-secondary" style="font-size: 2.5rem;"></i>
                                            <h6 class="card-title mt-2 mb-0 fw-semibold">{{ $unit->title }}</h6>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($children->count() === 0 && $units->count() === 0)
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-folder-x fs-1 text-muted mb-3 d-block"></i>
                        <h5 class="mb-2">لا يوجد محتوى</h5>
                        <p class="text-muted">لا توجد أقسام فرعية أو وحدات في هذا القسم</p>
                    </div>
                </div>
            @endif
        @else
            {{-- قسم الدروس: أقسام فرعية ووحدات فقط (الفيديو يظهر داخل صفحة الوحدة فقط) --}}
            @if($children->count() > 0)
                <div class="mb-4">
                    <h5 class="mb-3 fw-semibold"><i class="bi bi-folder2 me-2 text-warning"></i> أقسام فرعية</h5>
                    <div class="row g-3">
                        @foreach($children as $child)
                            <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                <a href="{{ route('student.subjects.folders.section', [$subject, $child]) }}" class="text-decoration-none text-reset">
                                    <div class="card custom-card h-100 border folder-card">
                                        <div class="card-body text-center py-4">
                                            <i class="bi bi-folder2 text-warning" style="font-size: 2.5rem;"></i>
                                            <h6 class="card-title mt-2 mb-0 fw-semibold">{{ $child->title }}</h6>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($units->count() > 0)
                <div>
                    <h5 class="mb-3 fw-semibold"><i class="bi bi-folder2 me-2 text-secondary"></i> الوحدات</h5>
                    <div class="row g-3">
                        @foreach($units as $unit)
                            <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                <a href="{{ route('student.subjects.folders.unit', [$subject, $section, $unit]) }}" class="text-decoration-none text-reset">
                                    <div class="card custom-card h-100 border folder-card">
                                        <div class="card-body text-center py-4">
                                            <i class="bi bi-folder2 text-secondary" style="font-size: 2.5rem;"></i>
                                            <h6 class="card-title mt-2 mb-0 fw-semibold">{{ $unit->title }}</h6>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($children->count() === 0 && $units->count() === 0)
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-folder-x fs-1 text-muted mb-3 d-block"></i>
                        <h5 class="mb-2">لا يوجد محتوى</h5>
                        <p class="text-muted">لا توجد أقسام فرعية أو وحدات في هذا القسم</p>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
@stop
