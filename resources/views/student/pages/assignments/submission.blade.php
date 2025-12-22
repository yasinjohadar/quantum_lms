@extends('student.layouts.master')

@section('page-title')
    عرض الإرسال
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header d-flex justify-content-between align-items-center my-4">
                <h5 class="page-title mb-0">عرض الإرسال - {{ $assignment->title }}</h5>
                <a href="{{ route('student.assignments.show', $assignment) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">معلومات الإرسال</h5>
                            
                            <div class="mb-3">
                                <strong>تاريخ الإرسال:</strong> {{ $submission->submitted_at?->format('Y-m-d H:i:s') ?? 'N/A' }}
                            </div>

                            <div class="mb-3">
                                <strong>المحاولة رقم:</strong> {{ $submission->attempt_number }}
                            </div>

                            <div class="mb-3">
                                <strong>الحالة:</strong>
                                <span class="badge bg-{{ $submission->status == 'graded' ? 'success' : ($submission->status == 'submitted' ? 'warning' : 'info') }}">
                                    {{ $submission->getStatusLabel() }}
                                </span>
                            </div>

                            @if($submission->is_late)
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    تم إرسال الواجب متأخراً
                                </div>
                            @endif

                            @if($submission->files->count() > 0)
                                <div class="mb-3">
                                    <h6>الملفات المرفوعة:</h6>
                                    <ul class="list-group">
                                        @foreach($submission->files as $file)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>
                                                    <i class="fas fa-file me-2"></i>
                                                    {{ $file->file_name }}
                                                    <small class="text-muted">({{ $file->getFormattedSize() }})</small>
                                                </span>
                                                <a href="{{ route('student.assignments.files.download', [$assignment, $submission, $file->id]) }}" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if($submission->answers->count() > 0)
                                <div class="mb-3">
                                    <h6>الإجابات:</h6>
                                    @foreach($submission->answers as $answer)
                                        <div class="card mb-2">
                                            <div class="card-body">
                                                <strong>{{ $answer->question->question_text }}</strong><br>
                                                <span class="text-muted">الإجابة: {{ is_array($answer->answer) ? implode(', ', $answer->answer) : $answer->answer }}</span>
                                                @if($answer->is_correct !== null)
                                                    <span class="badge bg-{{ $answer->is_correct ? 'success' : 'danger' }} float-end">
                                                        {{ $answer->is_correct ? 'صحيح' : 'خطأ' }} ({{ $answer->points_earned }} / {{ $answer->question->points }})
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($submission->status == 'graded')
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3">النتيجة</h5>
                                <div class="text-center mb-3">
                                    <h2 class="text-primary">{{ $submission->total_score ?? 0 }} / {{ $assignment->max_score }}</h2>
                                    <p class="text-muted">النسبة: {{ $submission->grade_percentage ?? 0 }}%</p>
                                </div>
                                @if($submission->feedback)
                                    <div class="alert alert-info">
                                        <strong>ملاحظات المعلم:</strong><br>
                                        {{ $submission->feedback }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="card-title">معلومات الواجب</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-star text-warning me-2"></i>
                                    <strong>الدرجة الكاملة:</strong> {{ $assignment->max_score }}
                                </li>
                                @if($assignment->due_date)
                                    <li class="mb-2">
                                        <i class="fas fa-clock text-info me-2"></i>
                                        <strong>موعد التسليم:</strong><br>
                                        {{ $assignment->due_date->format('Y-m-d H:i') }}
                                    </li>
                                @endif
                            </ul>

                            @if($submission->canResubmit())
                                <a href="{{ route('student.assignments.show', $assignment) }}" class="btn btn-primary btn-sm w-100 mt-3">
                                    <i class="fas fa-redo me-1"></i> إعادة إرسال
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

