@extends('admin.layouts.master')

@include('partials.question-math-assets')

@push('styles')
    @include('partials.questions.mcq-options-styles')
@endpush

@section('page-title')
    عرض حل AI
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">عرض حل AI</h5>
            </div>
            <div>
                <a href="{{ route('admin.ai.question-solutions.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">السؤال</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>نوع السؤال:</strong> {{ \App\Models\Question::TYPES[$solution->question->type] ?? $solution->question->type }}</p>
                        <div class="mb-3">
                            <strong class="d-block mb-1">السؤال:</strong>
                            <div class="question-stem question-text-body">{!! format_question_markup($solution->question->title ?? $solution->question->content ?? '') !!}</div>
                        </div>
                        @if(question_content_differs_from_title($solution->question->title, $solution->question->content))
                            <div class="mb-3 question-text-body text-muted">
                                {!! format_question_markup($solution->question->content) !!}
                            </div>
                        @endif
                        @if($solution->question->options && $solution->question->options->count() > 0)
                            <p class="mb-2"><strong>الخيارات:</strong></p>
                            @if(in_array($solution->question->type, ['single_choice', 'multiple_choice', 'true_false'], true))
                                @include('partials.questions.mcq-options-review', [
                                    'options' => $solution->question->options,
                                    'questionType' => $solution->question->type,
                                    'reviewMode' => false,
                                    'highlightCorrect' => true,
                                ])
                            @else
                                <ul class="mb-0">
                                    @foreach($solution->question->options as $option)
                                        <li class="question-text-body">
                                            {!! format_question_markup($option->content) !!}
                                            @if($option->is_correct) <span class="badge bg-success">صحيح</span> @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">حل AI</h6>
                        @if(!$solution->is_verified)
                            <form action="{{ route('admin.ai.question-solutions.verify', $solution->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-check me-1"></i> التحقق من الحل
                                </button>
                            </form>
                        @endif
                    </div>
                    <div class="card-body">
                        <p><strong>الموديل:</strong> {{ $solution->model->name ?? '-' }}</p>
                        <p><strong>درجة الثقة:</strong>
                            @if($solution->confidence_score)
                                <span class="badge bg-{{ $solution->confidence_score >= 0.8 ? 'success' : ($solution->confidence_score >= 0.5 ? 'warning' : 'danger') }}">
                                    {{ number_format($solution->confidence_score * 100, 1) }}%
                                </span>
                            @else
                                -
                            @endif
                        </p>
                        <p><strong>الدقة:</strong> {{ number_format($accuracy * 100, 1) }}%</p>
                        <hr>
                        <h6>الحل:</h6>
                        <div class="p-3 bg-light rounded question-text-body">
                            {!! format_question_markup($solution->solution) !!}
                        </div>
                        @if($solution->explanation)
                            <hr>
                            <h6>الشرح:</h6>
                            <div class="p-3 bg-light rounded question-text-body">
                                {!! format_question_markup($solution->explanation) !!}
                            </div>
                        @endif
                        @if($solution->is_verified)
                            <div class="alert alert-success mt-3 mb-0">
                                <i class="fas fa-check-circle me-1"></i> تم التحقق من هذا الحل
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
    @include('partials.question-math-scripts')
@stop
