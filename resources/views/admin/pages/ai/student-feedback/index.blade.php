@extends('admin.layouts.master')

@section('page-title')
    ملاحظات AI للطلاب
@stop

@section('css')
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">ملاحظات AI للطلاب</h5>
            </div>
        </div>

        <div class="card custom-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الطالب</th>
                                <th>نوع الملاحظات</th>
                                <th>الاختبار</th>
                                <th>الموديل</th>
                                <th>التاريخ</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($feedbacks as $feedback)
                                <tr>
                                    <td>{{ $feedback->id }}</td>
                                    <td>{{ $feedback->student->name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $feedback->feedback_type === 'performance' ? 'info' : ($feedback->feedback_type === 'improvement' ? 'warning' : 'secondary') }}">
                                            {{ \App\Models\AIStudentFeedback::FEEDBACK_TYPES[$feedback->feedback_type] ?? $feedback->feedback_type }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($feedback->quizAttempt)
                                            {{ $feedback->quizAttempt->quiz->title ?? '-' }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $feedback->aiModel->name ?? '-' }}</td>
                                    <td>{{ $feedback->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.ai.student-feedback.show', $feedback) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye me-1"></i> عرض
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">لا توجد ملاحظات</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $feedbacks->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
@stop


