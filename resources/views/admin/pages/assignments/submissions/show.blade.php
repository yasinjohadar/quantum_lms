@extends('admin.layouts.master')

@section('page-title')
    تصحيح الواجب
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header d-flex justify-content-between align-items-center my-4">
                <h5 class="page-title mb-0">تصحيح الواجب - {{ $submission->student->name }}</h5>
                <a href="{{ route('admin.assignments.submissions.index', $assignment) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">معلومات الإرسال</h5>
                            
                            <div class="mb-3">
                                <strong>الطالب:</strong> {{ $submission->student->name }} ({{ $submission->student->email }})
                            </div>
                            <div class="mb-3">
                                <strong>المحاولة:</strong> {{ $submission->attempt_number }}
                            </div>
                            <div class="mb-3">
                                <strong>تاريخ الإرسال:</strong> {{ $submission->submitted_at?->format('Y-m-d H:i:s') ?? 'N/A' }}
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
                                                <a href="{{ Storage::disk('public')->url($file->file_path) }}" 
                                                   target="_blank" class="btn btn-sm btn-primary">
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

                    @if($submission->status != 'graded')
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <h5 class="card-title mb-3">تصحيح الواجب</h5>
                                <form method="POST" action="{{ route('admin.assignments.submissions.grade', [$assignment, $submission]) }}">
                                    @csrf

                                    @if(in_array($assignment->grading_type, ['manual', 'mixed']))
                                        <div class="mb-3">
                                            <label class="form-label">الدرجة اليدوية</label>
                                            <input type="number" name="manual_score" class="form-control" 
                                                   min="0" max="{{ $assignment->max_score }}" step="0.01"
                                                   value="{{ $submission->grades->sum('manual_score') ?? 0 }}">
                                        </div>
                                    @endif

                                    <div class="mb-3">
                                        <label class="form-label">ملاحظات عامة</label>
                                        <textarea name="feedback" class="form-control" rows="5">{{ $submission->feedback }}</textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-check me-1"></i> حفظ التصحيح
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <h5 class="card-title mb-3">النتيجة</h5>
                                <div class="text-center mb-3">
                                    <h2 class="text-primary">{{ $submission->total_score ?? 0 }} / {{ $assignment->max_score }}</h2>
                                    <p class="text-muted">النسبة: {{ $submission->grade_percentage ?? 0 }}%</p>
                                </div>
                                @if($submission->feedback)
                                    <div class="alert alert-info">
                                        <strong>ملاحظات:</strong><br>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

