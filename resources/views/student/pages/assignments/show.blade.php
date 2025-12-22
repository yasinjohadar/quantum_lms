@extends('student.layouts.master')

@section('page-title')
    {{ $assignment->title }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header d-flex justify-content-between align-items-center my-4">
                <h5 class="page-title mb-0">{{ $assignment->title }}</h5>
                <a href="{{ route('student.assignments.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">تفاصيل الواجب</h5>
                            
                            @if($assignment->description)
                                <div class="mb-3">
                                    <p class="text-muted">{{ $assignment->description }}</p>
                                </div>
                            @endif

                            @if($assignment->instructions)
                                <div class="mb-3">
                                    <h6>التعليمات:</h6>
                                    <div class="bg-light p-3 rounded">
                                        {!! nl2br(e($assignment->instructions)) !!}
                                    </div>
                                </div>
                            @endif

                            @if($assignment->questions->count() > 0)
                                <div class="mb-3">
                                    <h6>الأسئلة:</h6>
                                    <ul class="list-group">
                                        @foreach($assignment->questions as $question)
                                            <li class="list-group-item">
                                                <strong>السؤال {{ $loop->iteration }}:</strong> {{ $question->question_text }}
                                                <span class="badge bg-info float-end">{{ $question->points }} نقطة</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($lastSubmission && $lastSubmission->status == 'graded')
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3">النتيجة</h5>
                                <div class="text-center mb-3">
                                    <h2 class="text-primary">{{ $lastSubmission->total_score ?? 0 }} / {{ $assignment->max_score }}</h2>
                                    <p class="text-muted">النسبة: {{ $lastSubmission->grade_percentage ?? 0 }}%</p>
                                </div>
                                @if($lastSubmission->feedback)
                                    <div class="alert alert-info">
                                        <strong>ملاحظات المعلم:</strong><br>
                                        {{ $lastSubmission->feedback }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if(!$lastSubmission || $lastSubmission->canResubmit())
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <h5 class="card-title mb-3">إرسال الواجب</h5>
                                <form method="POST" action="{{ route('student.assignments.submit', $assignment) }}" enctype="multipart/form-data">
                                    @csrf

                                    @if($assignment->questions->count() > 0)
                                        <div class="mb-3">
                                            <h6>الإجابات:</h6>
                                            @foreach($assignment->questions as $question)
                                                <div class="mb-3">
                                                    <label class="form-label">{{ $question->question_text }}</label>
                                                    @if($question->question_type == 'single_choice' || $question->question_type == 'true_false')
                                                        <select name="answers[{{ $question->id }}]" class="form-select">
                                                            <option value="">اختر الإجابة</option>
                                                            @if($question->question_type == 'true_false')
                                                                <option value="true">صح</option>
                                                                <option value="false">خطأ</option>
                                                            @else
                                                                @foreach($question->options ?? [] as $option)
                                                                    <option value="{{ $option }}">{{ $option }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    @elseif($question->question_type == 'multiple_choice')
                                                        @foreach($question->options ?? [] as $option)
                                                            <div class="form-check">
                                                                <input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $option }}" class="form-check-input" id="q{{ $question->id }}_{{ $loop->index }}">
                                                                <label class="form-check-label" for="q{{ $question->id }}_{{ $loop->index }}">{{ $option }}</label>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <textarea name="answers[{{ $question->id }}]" class="form-control" rows="3"></textarea>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if($assignment->max_files_per_submission > 0)
                                        <div class="mb-3">
                                            <label class="form-label">رفع الملفات (حد أقصى {{ $assignment->max_files_per_submission }} ملف)</label>
                                            <input type="file" name="files[]" class="form-control" multiple 
                                                   accept="{{ implode(',', array_map(fn($t) => '.' . $t, $assignment->getAllowedFileTypesArray() ?: [])) }}">
                                            <small class="text-muted">
                                                أنواع الملفات المسموحة: {{ $assignment->getAllowedFileTypesArray() ? implode(', ', $assignment->getAllowedFileTypesArray()) : 'جميع الأنواع' }}
                                                | الحد الأقصى: {{ $assignment->max_file_size }} MB
                                            </small>
                                        </div>
                                    @endif

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-1"></i> إرسال الواجب
                                    </button>
                                </form>
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
                                        <i class="fas fa-clock text-{{ $assignment->isOverdue() ? 'danger' : 'info' }} me-2"></i>
                                        <strong>موعد التسليم:</strong><br>
                                        {{ $assignment->due_date->format('Y-m-d H:i') }}
                                    </li>
                                @endif
                                <li class="mb-2">
                                    <i class="fas fa-redo text-primary me-2"></i>
                                    <strong>عدد المحاولات:</strong> {{ $lastSubmission ? $lastSubmission->attempt_number : 0 }} / {{ $assignment->max_attempts }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-file text-success me-2"></i>
                                    <strong>الحد الأقصى للملفات:</strong> {{ $assignment->max_files_per_submission }}
                                </li>
                            </ul>

                            @if($lastSubmission)
                                <a href="{{ route('student.assignments.submission', $assignment) }}" class="btn btn-info btn-sm w-100 mt-3">
                                    <i class="fas fa-eye me-1"></i> عرض الإرسال السابق
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

