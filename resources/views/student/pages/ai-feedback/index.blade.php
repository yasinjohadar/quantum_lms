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
        </div>

        @if($feedbacks->isEmpty())
            <div class="card custom-card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-robot fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="text-muted">لا توجد ملاحظات حالياً</h5>
                    <p class="text-muted">سيتم إظهار الملاحظات المولدة من AI هنا</p>
                </div>
            </div>
        @else
            <div class="row">
                @foreach($feedbacks as $feedback)
                    <div class="col-md-6 mb-3">
                        <div class="card custom-card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="bi bi-robot me-2"></i>
                                    {{ \App\Models\AIStudentFeedback::FEEDBACK_TYPES[$feedback->feedback_type] ?? $feedback->feedback_type }}
                                </h6>
                                @if($feedback->quizAttempt)
                                    <span class="badge bg-info">{{ $feedback->quizAttempt->quiz->title ?? '' }}</span>
                                @endif
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-2">{{ Str::limit($feedback->feedback_text, 150) }}</p>
                                <small class="text-muted">
                                    <i class="bi bi-calendar me-1"></i>
                                    {{ $feedback->created_at->format('Y-m-d H:i') }}
                                </small>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('student.ai-feedback.show', $feedback) }}" class="btn btn-sm btn-primary w-100">
                                    <i class="bi bi-eye me-1"></i> قراءة الملاحظات
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-3">
                {{ $feedbacks->links() }}
            </div>
        @endif
    </div>
</div>
@stop

@section('js')
@stop



