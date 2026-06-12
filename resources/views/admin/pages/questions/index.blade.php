@extends('admin.layouts.master')

@include('partials.question-math-assets')

@section('page-title')
    بنك الأسئلة
@stop

@push('styles')
    @include('admin.pages.questions.partials.index-styles')
@endpush

@section('content')
    <div class="main-content app-content qb-page">
        <div class="container-fluid">

            <div class="qb-hero my-4">
                <div class="qb-hero__icon">
                    <i class="bi bi-patch-question-fill"></i>
                </div>
                <div class="qb-hero__content">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">بنك الأسئلة</li>
                        </ol>
                    </nav>
                    <h4 class="qb-hero__title">بنك الأسئلة</h4>
                    <p class="qb-hero__subtitle">إدارة، تصفية، وتصدير الأسئلة عبر الصفوف والمواد</p>
                </div>
                <div class="qb-hero__stat">
                    <span class="qb-hero__stat-value">{{ number_format($questions->total()) }}</span>
                    <span class="qb-hero__stat-label">سؤال مطابق</span>
                </div>
                <div class="qb-hero__actions">
                    @can('question-export')
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#exportQuestionsWordModal">
                            <i class="bi bi-file-earmark-word me-1"></i> تصدير Word
                        </button>
                    @endcan
                    @can('question-show-import')
                        <a href="{{ route('admin.questions.import.show') }}" class="btn btn-success btn-sm">
                            <i class="bi bi-upload me-1"></i> استيراد
                        </a>
                    @endcan
                    @can('question-create')
                        <a href="{{ route('admin.questions.create') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-lg me-1"></i> سؤال جديد
                        </a>
                    @endcan
                </div>
            </div>

            @include('admin.pages.questions.partials.bank-index-content', [
                'subject' => null,
                'questions' => $questions,
                'units' => $units,
                'schoolClasses' => $schoolClasses ?? collect(),
                'initialSubjects' => $initialSubjects ?? collect(),
                'formAction' => route('admin.questions.index'),
                'createRoute' => $createRoute ?? route('admin.questions.create'),
                'showGlobalTools' => $showGlobalTools ?? true,
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
