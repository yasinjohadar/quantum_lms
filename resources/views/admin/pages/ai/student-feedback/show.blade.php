@extends('admin.layouts.master')

@section('page-title')
    ملاحظات AI للطالب
@stop

@section('css')
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">ملاحظات AI للطالب: {{ $studentFeedback->student->name }}</h5>
            </div>
            <div>
                <a href="{{ route('admin.ai.student-feedback.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card custom-card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-chat-left-text me-2"></i>
                            الملاحظات
                            <span class="badge bg-{{ $studentFeedback->feedback_type === 'performance' ? 'info' : ($studentFeedback->feedback_type === 'improvement' ? 'warning' : 'secondary') }} ms-2">
                                {{ \App\Models\AIStudentFeedback::FEEDBACK_TYPES[$studentFeedback->feedback_type] ?? $studentFeedback->feedback_type }}
                            </span>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            {!! nl2br(e($studentFeedback->feedback_text)) !!}
                        </div>

                        @if($studentFeedback->suggestions && count($studentFeedback->suggestions) > 0)
                            <div class="alert alert-info">
                                <h6><i class="bi bi-lightbulb me-2"></i>اقتراحات التحسين:</h6>
                                <ul class="mb-0">
                                    @foreach($studentFeedback->suggestions as $suggestion)
                                        <li>{{ $suggestion }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card custom-card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>معلومات</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">الطالب:</td>
                                <td>{{ $studentFeedback->student->name }}</td>
                            </tr>
                            @if($studentFeedback->quizAttempt)
                                <tr>
                                    <td class="text-muted">الاختبار:</td>
                                    <td>{{ $studentFeedback->quizAttempt->quiz->title ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">الدرجة:</td>
                                    <td>{{ $studentFeedback->quizAttempt->score ?? 0 }} / {{ $studentFeedback->quizAttempt->max_score ?? 0 }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="text-muted">الموديل:</td>
                                <td>{{ $studentFeedback->aiModel->name ?? '-' }}</td>
                            </tr>
                            @if($studentFeedback->tokens_used)
                                <tr>
                                    <td class="text-muted">Tokens:</td>
                                    <td>{{ number_format($studentFeedback->tokens_used) }}</td>
                                </tr>
                            @endif
                            @if($studentFeedback->cost)
                                <tr>
                                    <td class="text-muted">التكلفة:</td>
                                    <td>${{ number_format($studentFeedback->cost, 6) }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="text-muted">التاريخ:</td>
                                <td>{{ $studentFeedback->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
@stop




