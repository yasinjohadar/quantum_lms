@extends('admin.layouts.master')

@section('page-title')
    تفاصيل المحاولة
@stop

@section('css')
@stop

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تفاصيل محاولة: {{ $attempt->user->name ?? 'محذوف' }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.index') }}">الاختبارات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quizzes.show', $attempt->quiz_id) }}">{{ Str::limit($attempt->quiz->title, 20) }}</a></li>
                            <li class="breadcrumb-item active">تفاصيل المحاولة</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    @if($attempt->status === 'under_review')
                        <a href="{{ route('admin.quiz-attempts.grade', $attempt->id) }}" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil-square me-1"></i> تصحيح
                        </a>
                    @endif
                </div>
            </div>
            <!-- Page Header Close -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-4 mb-3">
            {{-- معلومات المحاولة --}}
            <div class="card custom-card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i> معلومات المحاولة</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">الطالب:</span>
                            <span class="fw-semibold">{{ $attempt->user->name ?? 'محذوف' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">رقم المحاولة:</span>
                            <span class="badge bg-secondary">{{ $attempt->attempt_number }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">الحالة:</span>
                            <span class="badge bg-{{ $attempt->status_color }}">{{ $attempt->status_name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">بدأ في:</span>
                            <span>{{ $attempt->started_at->format('Y/m/d H:i') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">انتهى في:</span>
                            <span>{{ $attempt->finished_at?->format('Y/m/d H:i') ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">الوقت المستغرق:</span>
                            <span>{{ $attempt->formatted_time_spent }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- النتيجة --}}
            <div class="card custom-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-trophy me-2"></i> النتيجة</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <span class="display-4 fw-bold {{ $attempt->passed ? 'text-success' : 'text-danger' }}">
                            {{ round($attempt->percentage) }}%
                        </span>
                    </div>
                    <div class="mb-3">
                        <span class="badge bg-{{ $attempt->pass_status_color }} fs-6">
                            {{ $attempt->pass_status_name }}
                        </span>
                    </div>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="text-success fw-bold fs-5">{{ $attempt->questions_correct }}</div>
                            <small class="text-muted">صحيحة</small>
                        </div>
                        <div class="col-4">
                            <div class="text-danger fw-bold fs-5">{{ $attempt->questions_wrong }}</div>
                            <small class="text-muted">خاطئة</small>
                        </div>
                        <div class="col-4">
                            <div class="text-secondary fw-bold fs-5">{{ $attempt->questions_skipped }}</div>
                            <small class="text-muted">متروكة</small>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>الدرجة:</span>
                        <span class="fw-bold">{{ $attempt->score }} / {{ $attempt->max_score }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            {{-- الإجابات --}}
            <div class="card custom-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-list-check me-2"></i> الإجابات</h6>
                </div>
                <div class="card-body">
                    @foreach($attempt->answers as $index => $answer)
                        <div class="border rounded p-3 mb-3 {{ $answer->is_correct ? 'border-success' : ($answer->is_graded && !$answer->is_correct ? 'border-danger' : 'border-warning') }}">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-secondary me-2">{{ $index + 1 }}</span>
                                    <span class="badge bg-{{ $answer->question->type_color }}-transparent text-{{ $answer->question->type_color }}">
                                        {{ $answer->question->type_name }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-{{ $answer->status_color }}">
                                        {{ $answer->status_name }}
                                    </span>
                                    <span class="fw-semibold">
                                        {{ $answer->points_earned }} / {{ $answer->max_points }}
                                    </span>
                                </div>
                            </div>
                            
                            <h6 class="mb-3">{{ $answer->question->title }}</h6>
                            
                            {{-- عرض الإجابة حسب نوع السؤال --}}
                            @if(in_array($answer->question->type, ['single_choice', 'multiple_choice', 'true_false']))
                                <div class="mb-2">
                                    <strong class="small text-muted">الخيارات:</strong>
                                    @foreach($answer->question->options as $option)
                                        <div class="d-flex align-items-center gap-2 mt-1 p-2 rounded {{ $option->is_correct ? 'bg-success-transparent' : '' }}">
                                            @php
                                                $selected = is_array($answer->selected_options) && in_array($option->id, $answer->selected_options);
                                            @endphp
                                            @if($selected)
                                                <i class="bi bi-check-circle-fill {{ $option->is_correct ? 'text-success' : 'text-danger' }}"></i>
                                            @else
                                                <i class="bi bi-circle text-muted"></i>
                                            @endif
                                            <span class="{{ $selected && !$option->is_correct ? 'text-danger text-decoration-line-through' : '' }}">
                                                {{ $option->content }}
                                            </span>
                                            @if($option->is_correct)
                                                <span class="badge bg-success small">صحيح</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($answer->question->type === 'essay' || $answer->question->type === 'short_answer')
                                <div class="mb-2">
                                    <strong class="small text-muted">إجابة الطالب:</strong>
                                    <div class="p-2 bg-light rounded mt-1">
                                        {{ $answer->answer_text ?? 'لم يجب' }}
                                    </div>
                                </div>
                            @elseif($answer->question->type === 'numerical')
                                <div class="mb-2">
                                    <strong class="small text-muted">إجابة الطالب:</strong>
                                    <span class="badge bg-{{ $answer->is_correct ? 'success' : 'danger' }} ms-2">
                                        {{ $answer->numeric_answer ?? 'لم يجب' }}
                                    </span>
                                    <br>
                                    <strong class="small text-muted">الإجابة الصحيحة:</strong>
                                    <span class="badge bg-success ms-2">
                                        {{ $answer->question->options->first()->content ?? '-' }}
                                    </span>
                                </div>
                            @endif

                            @if($answer->feedback)
                                <div class="mt-2 p-2 bg-info-transparent rounded">
                                    <small class="text-info">
                                        <i class="bi bi-chat-dots me-1"></i>
                                        ملاحظة المصحح: {{ $answer->feedback }}
                                    </small>
                                </div>
                            @endif

                            @if($answer->question->explanation && $answer->is_graded)
                                <div class="mt-2 p-2 bg-success-transparent rounded">
                                    <small class="text-success">
                                        <i class="bi bi-lightbulb me-1"></i>
                                        الشرح: {{ $answer->question->explanation }}
                                    </small>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

        </div>
    </div>
    <!-- End::app-content -->
@stop

@section('js')
@stop

