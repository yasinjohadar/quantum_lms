@php
    $pageTitle = $pageTitle ?? null;
    $pageIcon = $pageIcon ?? 'bi-folder2-open';
    $sectionAncestors = collect();

    if (!empty($section)) {
        $ancestor = $section->parent;
        $depth = 0;
        while ($ancestor && $depth < 10) {
            if (!$ancestor->relationLoaded('parent')) {
                $ancestor->load('parent');
            }
            $sectionAncestors->push($ancestor);
            $ancestor = $ancestor->parent;
            $depth++;
        }
        $sectionAncestors = $sectionAncestors->reverse()->values();
    }

    $currentLabel = $currentLabel
        ?? ($unit->title ?? null)
        ?? ($section->title ?? null)
        ?? ($pageTitle ?? 'الأقسام');

    $currentIcon = $currentIcon
        ?? (isset($unit) ? 'bi-folder2' : (isset($section) ? 'bi-folder2-open' : 'bi-grid-3x3-gap'));
@endphp

@include('student.pages.lessons.partials.subject-content-breadcrumb-styles')

<nav class="student-content-breadcrumb mb-4" aria-label="مسار التنقل">
    <ol class="student-content-breadcrumb__trail">
        <li class="student-content-breadcrumb__item">
            <a href="{{ route('student.dashboard') }}" class="student-content-breadcrumb__link">
                <i class="bi bi-house-door-fill"></i>
                <span>الرئيسية</span>
            </a>
        </li>

        <li class="student-content-breadcrumb__sep" aria-hidden="true"><i class="bi bi-chevron-left"></i></li>

        <li class="student-content-breadcrumb__item">
            <a href="{{ route('student.subjects') }}" class="student-content-breadcrumb__link">
                <i class="bi bi-journal-bookmark-fill"></i>
                <span>المواد الدراسية</span>
            </a>
        </li>

        <li class="student-content-breadcrumb__sep" aria-hidden="true"><i class="bi bi-chevron-left"></i></li>

        @if(isset($unit) || isset($section))
            <li class="student-content-breadcrumb__item">
                <a href="{{ route('student.subjects.show', $subject) }}" class="student-content-breadcrumb__link" title="{{ $subject->name }}">
                    <i class="bi bi-book-half"></i>
                    <span>{{ $subject->name }}</span>
                </a>
            </li>
            <li class="student-content-breadcrumb__sep" aria-hidden="true"><i class="bi bi-chevron-left"></i></li>
        @endif

        @foreach($sectionAncestors as $ancestor)
            <li class="student-content-breadcrumb__item">
                <a href="{{ route('student.subjects.folders.section', [$subject, $ancestor]) }}" class="student-content-breadcrumb__link" title="{{ $ancestor->title }}">
                    <i class="bi bi-folder"></i>
                    <span>{{ $ancestor->title }}</span>
                </a>
            </li>
            <li class="student-content-breadcrumb__sep" aria-hidden="true"><i class="bi bi-chevron-left"></i></li>
        @endforeach

        @if(isset($unit) && isset($section))
            <li class="student-content-breadcrumb__item">
                <a href="{{ route('student.subjects.folders.section', [$subject, $section]) }}" class="student-content-breadcrumb__link" title="{{ $section->title }}">
                    <i class="bi bi-folder2-open"></i>
                    <span>{{ $section->title }}</span>
                </a>
            </li>
            <li class="student-content-breadcrumb__sep" aria-hidden="true"><i class="bi bi-chevron-left"></i></li>
        @endif

        <li class="student-content-breadcrumb__item" aria-current="page">
            <span class="student-content-breadcrumb__current">
                <i class="bi {{ $currentIcon }}"></i>
                <span>{{ $currentLabel }}</span>
            </span>
        </li>
    </ol>

    @if($pageTitle)
        <h1 class="student-content-breadcrumb__heading">
            <i class="bi {{ $pageIcon }} me-2 text-warning"></i>{{ $pageTitle }}
        </h1>
        @if($subject->schoolClass ?? null)
            <p class="student-content-breadcrumb__meta mb-0">
                <i class="bi bi-building me-1"></i>{{ $subject->schoolClass->name }}
            </p>
        @endif
    @endif
</nav>
