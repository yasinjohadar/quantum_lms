@extends('frontend.layouts.master')

@section('content')

<section class="search-results-section py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                @if($query !== '')
                    <h1 class="section-title">نتائج البحث عن «{{ $query }}»</h1>
                    <p class="section-description text-muted">الصفوف والمواد المطابقة للبحث</p>
                @else
                    <h1 class="section-title">بحث الصفوف والمواد</h1>
                    <p class="section-description text-muted">استخدم مربع البحث في الأعلى للبحث عن صف أو مادة</p>
                @endif
            </div>
        </div>

        @if($query === '')
            <div class="alert alert-info">
                <i class="fa-solid fa-search me-2"></i>
                اكتب كلمة البحث ثم اضغط Enter أو زر البحث.
            </div>
        @else
            @php
                $hasClasses = $classes->isNotEmpty();
                $hasSubjects = $subjects->isNotEmpty();
            @endphp

            @if(!$hasClasses && !$hasSubjects)
                <div class="alert alert-warning">
                    <i class="fa-solid fa-info-circle me-2"></i>
                    لا توجد نتائج لـ «{{ $query }}». جرّب كلمات أخرى.
                </div>
            @else
                @if($hasClasses)
                    <div class="mb-5">
                        <h2 class="h5 mb-3">
                            <i class="fa-solid fa-graduation-cap me-2 text-primary"></i>
                            الصفوف الدراسية ({{ $classes->count() }})
                        </h2>
                        <div class="row g-3">
                            @foreach($classes as $class)
                                <div class="col-md-6 col-lg-4">
                                    <a href="{{ route('frontend.class.show', $class->slug) }}" class="card h-100 text-decoration-none search-result-card border shadow-sm">
                                        <div class="card-body d-flex align-items-center gap-3">
                                            @if($class->image)
                                                <img src="{{ asset('storage/' . $class->image) }}" alt="{{ $class->name }}" class="rounded" style="width: 56px; height: 56px; object-fit: cover;">
                                            @else
                                                <div class="rounded bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                                    <i class="fa-solid fa-graduation-cap text-primary"></i>
                                                </div>
                                            @endif
                                            <div class="flex-grow-1 min-w-0">
                                                <h3 class="h6 mb-1 text-dark">{{ $class->name }}</h3>
                                                @if($class->description)
                                                    <p class="small text-muted mb-0 text-truncate">{{ Str::limit($class->description, 60) }}</p>
                                                @endif
                                            </div>
                                            <i class="fa-solid fa-angles-left text-muted"></i>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($hasSubjects)
                    <div>
                        <h2 class="h5 mb-3">
                            <i class="fa-solid fa-book me-2 text-primary"></i>
                            المواد الدراسية ({{ $subjects->count() }})
                        </h2>
                        <div class="row g-3">
                            @foreach($subjects as $subject)
                                @php
                                    $classSlug = $subject->schoolClass ? $subject->schoolClass->slug : null;
                                @endphp
                                @if($classSlug)
                                    <div class="col-md-6 col-lg-4">
                                        <a href="{{ route('frontend.class.show', $classSlug) }}#subject-{{ $subject->id }}" class="card h-100 text-decoration-none search-result-card border shadow-sm">
                                            <div class="card-body d-flex align-items-center gap-3">
                                                <div class="rounded bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                                    <i class="fa-solid fa-book text-primary"></i>
                                                </div>
                                                <div class="flex-grow-1 min-w-0">
                                                    <h3 class="h6 mb-1 text-dark">{{ $subject->name }}</h3>
                                                    <p class="small text-muted mb-0">
                                                        {{ $subject->schoolClass->name ?? '' }}
                                                    </p>
                                                </div>
                                                <i class="fa-solid fa-angles-left text-muted"></i>
                                            </div>
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        @endif
    </div>
</section>

@endsection
