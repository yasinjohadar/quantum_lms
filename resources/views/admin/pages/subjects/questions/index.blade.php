@extends('admin.layouts.master')

@include('partials.question-math-assets')

@section('page-title')
    بنك أسئلة — {{ $subject->name }}
@stop

@section('css')
@include('admin.pages.questions.partials.index-styles')
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">بنك أسئلة — {{ $subject->name }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.subjects.index') }}">المواد</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.subjects.show', $subject->id) }}">{{ $subject->name }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">بنك الأسئلة</li>
                        </ol>
                    </nav>
                    @if($subject->schoolClass)
                        <p class="text-muted small mb-0 mt-1">
                            {{ $subject->schoolClass->name }}
                            @if($subject->schoolClass->stage)
                                ({{ $subject->schoolClass->stage->name }})
                            @endif
                        </p>
                    @endif
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-collection me-1"></i> البنك الرئيسي
                    </a>
                    @can('question-show-import')
                        <a href="{{ route('admin.subjects.questions.import', $subject->id) }}" class="btn btn-success btn-sm">
                            <i class="bi bi-upload me-1"></i> استيراد
                        </a>
                    @endcan
                    @can('question-create')
                        <a href="{{ route('admin.subjects.questions.ai-create', $subject->id) }}" class="btn btn-info btn-sm text-white">
                            <i class="bi bi-robot me-1"></i> توليد AI
                        </a>
                        <a href="{{ route('admin.subjects.questions.ai-create-from-image', $subject->id) }}" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-file-earmark-image me-1"></i> من صورة/PDF
                        </a>
                        <a href="{{ route('admin.subjects.questions.create', $subject->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i> إضافة سؤال
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
@stop
