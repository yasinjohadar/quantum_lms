@extends('student.layouts.master')

@section('page-title')
    ملاحظات AI
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">ملاحظات AI</h5>
            </div>
            <div>
                <a href="{{ route('student.ai-feedback.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>

        <div class="card custom-card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-robot me-2"></i>
                    {{ \App\Models\AIStudentFeedback::FEEDBACK_TYPES[$aiFeedback->feedback_type] ?? $aiFeedback->feedback_type }}
                    @if($aiFeedback->quizAttempt)
                        <span class="badge bg-info ms-2">{{ $aiFeedback->quizAttempt->quiz->title ?? '' }}</span>
                    @endif
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    {!! nl2br(e($aiFeedback->feedback_text)) !!}
                </div>

                @if($aiFeedback->suggestions && count($aiFeedback->suggestions) > 0)
                    <div class="alert alert-info">
                        <h6><i class="bi bi-lightbulb me-2"></i>اقتراحات للتحسين:</h6>
                        <ul class="mb-0">
                            @foreach($aiFeedback->suggestions as $suggestion)
                                <li>{{ $suggestion }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mt-4 pt-4 border-top">
                    <small class="text-muted">
                        <i class="bi bi-calendar me-1"></i>
                        {{ $aiFeedback->created_at->format('Y-m-d H:i') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
@stop




