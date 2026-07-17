@extends('admin.layouts.master')

@include('partials.question-math-assets')

@section('page-title')
    بنك أسئلة — {{ $subject->name }}
@stop

@push('styles')
    @include('admin.pages.questions.partials.index-styles')
@endpush

@section('content')
    <div class="main-content app-content qb-page">
        <div class="container-fluid">

            <div class="qb-hero my-4">
                <div class="qb-hero__icon">
                    <i class="bi bi-journal-bookmark-fill"></i>
                </div>
                <div class="qb-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            @php
                                $returnClassId = request()->filled('return_to_class_id')
                                    ? (int) request('return_to_class_id')
                                    : null;
                                $breadcrumbClass = null;
                                if ($returnClassId && $subject->schoolClass && (int) $subject->schoolClass->id === $returnClassId) {
                                    $breadcrumbClass = $subject->schoolClass;
                                } elseif ($returnClassId) {
                                    $breadcrumbClass = \App\Models\SchoolClass::with('stage')->find($returnClassId);
                                } elseif ($subject->schoolClass) {
                                    $breadcrumbClass = $subject->schoolClass;
                                }
                                $subjectShowUrl = route('admin.subjects.show', $subject->id)
                                    .($returnClassId ? '?return_to_class_id='.$returnClassId : '');
                            @endphp
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            @if($breadcrumbClass)
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.classes.index') }}">الصفوف الدراسية</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.classes.show', $breadcrumbClass->id) }}">{{ $breadcrumbClass->name }}</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ $subjectShowUrl }}">{{ $subject->name }}</a>
                                </li>
                            @else
                                <li class="breadcrumb-item"><a href="{{ route('admin.subjects.index') }}">المواد</a></li>
                                <li class="breadcrumb-item">
                                    <a href="{{ $subjectShowUrl }}">{{ $subject->name }}</a>
                                </li>
                            @endif
                            <li class="breadcrumb-item active" aria-current="page">بنك الأسئلة</li>
                        </ol>
                    </nav>
                    <h4 class="qb-hero__title">بنك أسئلة — {{ $subject->name }}</h4>
                    <p class="qb-hero__subtitle">
                        @if($subject->schoolClass)
                            {{ $subject->schoolClass->name }}
                            @if($subject->schoolClass->stage)
                                — {{ $subject->schoolClass->stage->name }}
                            @endif
                        @else
                            إدارة أسئلة هذه المادة
                        @endif
                    </p>
                </div>
                <div class="qb-hero__stat">
                    <span class="qb-hero__stat-value">{{ number_format($questions->total()) }}</span>
                    <span class="qb-hero__stat-label">سؤال</span>
                </div>
                <div class="qb-hero__actions">
                    <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-collection me-1"></i> البنك الرئيسي
                    </a>
                    @can('question-export')
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#exportQuestionsWordModal">
                            <i class="bi bi-file-earmark-word me-1"></i> تصدير
                        </button>
                    @endcan
                    @can('question-show-import')
                        <a href="{{ route('admin.subjects.questions.import', $subject->id) }}" class="btn btn-success btn-sm">
                            <i class="bi bi-upload me-1"></i> استيراد
                        </a>
                    @endcan
                    @can('question-create')
                        <a href="{{ route('admin.subjects.questions.ai-create', $subject->id) }}" class="btn btn-info btn-sm text-white">
                            <i class="bi bi-robot me-1"></i> AI
                        </a>
                        <a href="{{ route('admin.subjects.questions.create', $subject->id) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-lg me-1"></i> سؤال جديد
                        </a>
                    @endcan
                </div>
            </div>

            @include('admin.pages.questions.partials.bank-index-content', [
                'subject' => $subject,
                'questions' => $questions,
                'units' => $units,
                'schoolClasses' => collect(),
                'initialSubjects' => collect(),
                'formAction' => route('admin.subjects.questions.index', $subject->id),
                'createRoute' => route('admin.subjects.questions.create', $subject->id),
                'showGlobalTools' => false,
            ])

        </div>
    </div>
@stop

@section('js')
    @include('partials.question-math-scripts')
    @if($enableAjaxFilters ?? false)
        @include('admin.pages.questions.partials.bank-index-ajax')
    @endif
@stop
