@extends('admin.layouts.master')

@include('partials.question-math-assets')

@section('page-title')
    بنك الأسئلة
@stop

@push('styles')
@include('admin.pages.questions.partials.index-styles')
@endpush

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">بنك الأسئلة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">بنك الأسئلة</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    @can('question-show-import')
                        <a href="{{ route('admin.questions.import.show') }}" class="btn btn-success btn-sm">
                            <i class="bi bi-upload me-1"></i> استيراد أسئلة
                        </a>
                    @endcan
                    @can('question-create')
                        <a href="{{ route('admin.questions.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i> إضافة سؤال جديد
                        </a>
                    @endcan
                </div>
            </div>
            <!-- Page Header Close -->

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
    <!-- End::app-content -->
@stop

@section('js')
    @include('partials.question-math-scripts')
    @if($enableAjaxFilters ?? false)
        @include('admin.pages.questions.partials.bank-index-ajax')
    @endif
@stop