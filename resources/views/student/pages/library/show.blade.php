@extends('student.layouts.master')

@section('page-title')
    {{ $item->title }}
@stop

@push('styles')
    @include('student.partials.dashboard-widget-styles')
    @include('student.pages.lessons.partials.subject-content-breadcrumb-styles')
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
                    <a href="{{ route('student.library.index') }}" class="student-content-breadcrumb__link">
                        <i class="bi bi-collection"></i>
                        <span>المكتبة</span>
                    </a>
                </li>
                <li class="student-content-breadcrumb__sep" aria-hidden="true"><i class="bi bi-chevron-left"></i></li>
                <li class="student-content-breadcrumb__item" aria-current="page">
                    <span class="student-content-breadcrumb__current">{{ $item->title }}</span>
                </li>
            </ol>
        </nav>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card dashboard-panel">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h4 class="fw-bold mb-0">{{ $item->title }}</h4>
                            <span class="badge bg-info">{{ \App\Models\LibraryItem::TYPES[$item->type] ?? $item->type }}</span>
                        </div>

                        @if ($item->description)
                            <p class="text-muted">{{ $item->description }}</p>
                        @endif

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @if ($item->category)
                                <span class="badge bg-light text-dark border">{{ $item->category->name }}</span>
                            @endif
                            @if ($item->subject)
                                <span class="badge bg-light text-dark border">{{ $item->subject->name }}</span>
                            @endif
                            @if ($item->schoolClass)
                                <span class="badge bg-light text-dark border">{{ $item->schoolClass->name }}</span>
                            @endif
                        </div>

                        @if ($item->file_path)
                            <form method="POST" action="{{ route('student.library.download', $item->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-download me-1"></i> تحميل ({{ $item->formatted_file_size }})
                                </button>
                            </form>
                        @elseif ($item->external_url)
                            <form method="POST" action="{{ route('student.library.download', $item->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> فتح الرابط
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card dashboard-panel">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">معلومات إضافية</h6>
                        <p class="mb-2 small text-muted">أضافه: {{ $item->uploader?->name ?? '—' }}</p>
                        <p class="mb-0 small text-muted">تاريخ الإضافة: {{ $item->created_at?->format('Y-m-d') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
