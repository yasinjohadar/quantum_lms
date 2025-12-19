@extends('student.layouts.master')

@section('page-title')
    {{ $subject->name }}
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
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
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.subjects') }}">المواد الدراسية</a></li>
                    <li class="breadcrumb-item active">{{ $subject->name }}</li>
                </ol>
            </nav>
        </div>
        <!-- End Page Header -->

        <!-- إحصائيات المادة -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ $stats['total_sections'] }}</h3>
                        <p class="mb-0">أقسام</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ $stats['total_units'] }}</h3>
                        <p class="mb-0">وحدات</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ $stats['total_lessons'] }}</h3>
                        <p class="mb-0">دروس</p>
                    </div>
                </div>
            </div>
        </div>

        @if($subject->description)
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="mb-2">وصف المادة</h6>
                    <p class="mb-0">{{ $subject->description }}</p>
                </div>
            </div>
        @endif

        <!-- الأقسام والوحدات والدروس -->
        @if($sections->count() > 0)
            <div class="row">
                <div class="col-12">
                    @foreach($sections as $section)
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="bi bi-folder me-2"></i>
                                    {{ $section->title }}
                                </h5>
                                @if($section->description)
                                    <p class="text-muted mb-0 mt-2">{{ $section->description }}</p>
                                @endif
                            </div>
                            <div class="card-body">
                                @if($section->units->count() > 0)
                                    <div class="accordion" id="section-{{ $section->id }}">
                                        @foreach($section->units as $unitIndex => $unit)
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="unit-heading-{{ $unit->id }}">
                                                    <button class="accordion-button {{ $unitIndex > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#unit-{{ $unit->id }}" aria-expanded="{{ $unitIndex === 0 ? 'true' : 'false' }}">
                                                        <i class="bi bi-file-text me-2"></i>
                                                        {{ $unit->title }}
                                                        <span class="badge bg-secondary ms-2">{{ $unit->lessons->count() }} درس</span>
                                                    </button>
                                                </h2>
                                                <div id="unit-{{ $unit->id }}" class="accordion-collapse collapse {{ $unitIndex === 0 ? 'show' : '' }}" data-bs-parent="#section-{{ $section->id }}">
                                                    <div class="accordion-body">
                                                        @if($unit->description)
                                                            <p class="text-muted mb-3">{{ $unit->description }}</p>
                                                        @endif
                                                        
                                                        @if($unit->lessons->count() > 0)
                                                            <div class="list-group">
                                                                @foreach($unit->lessons as $lesson)
                                                                    <a href="{{ route('student.lessons.show', $lesson->id) }}" class="list-group-item list-group-item-action">
                                                                        <div class="d-flex justify-content-between align-items-center">
                                                                            <div>
                                                                                <h6 class="mb-1">
                                                                                    <i class="bi bi-play-circle me-2 text-primary"></i>
                                                                                    {{ $lesson->title }}
                                                                                </h6>
                                                                                @if($lesson->description)
                                                                                    <p class="text-muted mb-0 small">{{ \Illuminate\Support\Str::limit($lesson->description, 80) }}</p>
                                                                                @endif
                                                                                <div class="mt-2">
                                                                                    @if($lesson->duration)
                                                                                        <span class="badge bg-secondary me-2">
                                                                                            <i class="bi bi-clock me-1"></i>
                                                                                            {{ $lesson->formatted_duration }}
                                                                                        </span>
                                                                                    @endif
                                                                                    @if($lesson->is_free)
                                                                                        <span class="badge bg-success">مجاني</span>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                            <i class="bi bi-chevron-left"></i>
                                                                        </div>
                                                                    </a>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <p class="text-muted mb-0">لا توجد دروس في هذه الوحدة</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted mb-0">لا توجد وحدات في هذا القسم</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-folder-x fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="mb-2">لا يوجد محتوى</h5>
                    <p class="text-muted">لم يتم إضافة محتوى لهذه المادة بعد</p>
                </div>
            </div>
        @endif
    </div>
    <!-- Container closed -->
</div>
<!-- main-content closed -->
@stop

