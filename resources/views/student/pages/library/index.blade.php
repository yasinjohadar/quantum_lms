@extends('student.layouts.master')

@section('page-title')
    المكتبة
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
                <li class="student-content-breadcrumb__item" aria-current="page">
                    <span class="student-content-breadcrumb__current">
                        <i class="bi bi-collection"></i>
                        <span>المكتبة</span>
                    </span>
                </li>
            </ol>
            <div>
                <h1 class="student-content-breadcrumb__heading mb-0">
                    <i class="bi bi-collection me-2 text-warning"></i>المكتبة
                </h1>
                <p class="student-content-breadcrumb__meta mb-0">ملفات ومصادر متاحة لصفوفك وموادك المسجَّل بها</p>
            </div>
        </nav>

        <div class="card dashboard-panel mb-3">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('student.library.index') }}" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="بحث بالعنوان أو الوصف" value="{{ $filters['search'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <select name="category_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">كل التصنيفات</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ (string) ($filters['category_id'] ?? '') === (string) $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="subject_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">كل موادي</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ (string) ($filters['subject_id'] ?? '') === (string) $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">بحث</button>
                        <a href="{{ route('student.library.index') }}" class="btn btn-outline-secondary btn-sm">مسح</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3">
            @forelse ($items as $item)
                <div class="col-md-6 col-lg-4">
                    <div class="card dashboard-subject-card h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-info">{{ \App\Models\LibraryItem::TYPES[$item->type] ?? $item->type }}</span>
                                @if ($item->is_featured)
                                    <span class="badge bg-warning text-dark">مميز</span>
                                @endif
                            </div>
                            <h6 class="fw-bold mb-1">{{ $item->title }}</h6>
                            <p class="text-muted small mb-2">{{ \Illuminate\Support\Str::limit($item->description, 90) }}</p>
                            <div class="small text-muted mb-3">
                                @if ($item->category)
                                    <span class="badge bg-light text-dark border me-1">{{ $item->category->name }}</span>
                                @endif
                                @if ($item->subject)
                                    <span class="badge bg-light text-dark border">{{ $item->subject->name }}</span>
                                @endif
                            </div>
                            <a href="{{ route('student.library.show', $item->id) }}" class="btn btn-primary btn-sm mt-auto">
                                عرض التفاصيل
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info mb-0 text-center">
                        لا توجد عناصر مكتبة متاحة لك حالياً.
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-3">
            {{ $items->links() }}
        </div>
    </div>
</div>
@stop
