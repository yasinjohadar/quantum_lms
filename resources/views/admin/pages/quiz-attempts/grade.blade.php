@extends('admin.layouts.master')

@section('page-title')
    تصحيح المحاولة
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
                    <h5 class="page-title fs-21 mb-1">تصحيح محاولة: {{ $attempt->user->name ?? 'محذوف' }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.quiz-attempts.needs-grading') }}">بحاجة للتصحيح</a></li>
                            <li class="breadcrumb-item active">تصحيح</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- Page Header Close -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        <strong>الاختبار:</strong> {{ $attempt->quiz->title }}
        <span class="mx-2">|</span>
        <strong>الطالب:</strong> {{ $attempt->user->name ?? 'محذوف' }}
        <span class="mx-2">|</span>
        <strong>الأسئلة التي تحتاج تصحيح:</strong> {{ $attempt->answers->count() }}
    </div>

    @php
        $essayAnswers = $attempt->answers->filter(function($answer) {
            return $answer->question->type === 'essay' && !empty($answer->answer_text);
        });
    @endphp

    @if($essayAnswers->count() > 0)
        <div class="alert alert-primary d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-robot me-2"></i>
                <strong>تصحيح تلقائي بالAI:</strong> 
                يوجد {{ $essayAnswers->count() }} سؤال مقالي يمكن تصحيحه تلقائياً
            </div>
            <form action="{{ route('admin.quiz-attempts.ai-grade-all', $attempt->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من تصحيح جميع الأسئلة المقالية باستخدام AI؟');">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-robot me-1"></i> تصحيح الكل بالAI
                </button>
            </form>
        </div>
    @endif

    <form action="{{ route('admin.quiz-attempts.save-grade', $attempt->id) }}" method="POST">
        @csrf
        
        @foreach($attempt->answers as $index => $answer)
            <div class="card custom-card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <span class="badge bg-secondary me-2">{{ $index + 1 }}</span>
                        <span class="badge bg-{{ $answer->question->type_color }}-transparent text-{{ $answer->question->type_color }}">
                            {{ $answer->question->type_name }}
                        </span>
                    </h6>
                    <span class="text-muted">الحد الأقصى: {{ $answer->max_points }} درجة</span>
                </div>
                <div class="card-body">
                    <h5 class="mb-3">{{ $answer->question->title }}</h5>
                    
                    @if($answer->question->content)
                        <div class="text-muted mb-3">{!! $answer->question->content !!}</div>
                    @endif

                    <div class="p-3 bg-light rounded mb-3">
                        <strong class="d-block mb-2 text-primary">
                            <i class="bi bi-chat-left-text me-1"></i>
                            إجابة الطالب:
                        </strong>
                        @if($answer->answer_text)
                            <p class="mb-0">{{ $answer->answer_text }}</p>
                        @else
                            <p class="text-muted mb-0">لم يجب الطالب</p>
                        @endif
                    </div>

                    {{-- AI Grading Results --}}
                    @if($answer->ai_graded && $answer->ai_grading_data)
                        @php
                            $aiData = $answer->ai_grading_data;
                        @endphp
                        <div class="alert alert-success mb-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <strong>
                                    <i class="bi bi-robot me-1"></i>
                                    تم التصحيح تلقائياً بالAI
                                </strong>
                                @if($answer->aiGradingModel)
                                    <span class="badge bg-secondary">{{ $answer->aiGradingModel->name }}</span>
                                @endif
                            </div>
                            
                            @if(isset($aiData['points']) && isset($aiData['max_points']))
                                <div class="mb-2">
                                    <strong>الدرجة:</strong> 
                                    <span class="badge bg-success">{{ $aiData['points'] }} / {{ $aiData['max_points'] }}</span>
                                    ({{ number_format(($aiData['points'] / $aiData['max_points']) * 100, 1) }}%)
                                </div>
                            @endif

                            @if(isset($aiData['feedback']) && !empty($aiData['feedback']))
                                <div class="mb-2">
                                    <strong>التعليق:</strong>
                                    <p class="mb-0">{{ $aiData['feedback'] }}</p>
                                </div>
                            @endif

                            @if(isset($aiData['strengths']) && is_array($aiData['strengths']) && count($aiData['strengths']) > 0)
                                <div class="mb-2">
                                    <strong class="text-success">نقاط القوة:</strong>
                                    <ul class="mb-0">
                                        @foreach($aiData['strengths'] as $strength)
                                            <li>{{ $strength }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if(isset($aiData['weaknesses']) && is_array($aiData['weaknesses']) && count($aiData['weaknesses']) > 0)
                                <div class="mb-2">
                                    <strong class="text-danger">نقاط الضعف:</strong>
                                    <ul class="mb-0">
                                        @foreach($aiData['weaknesses'] as $weakness)
                                            <li>{{ $weakness }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if(isset($aiData['suggestions']) && is_array($aiData['suggestions']) && count($aiData['suggestions']) > 0)
                                <div class="mb-0">
                                    <strong class="text-info">اقتراحات للتحسين:</strong>
                                    <ul class="mb-0">
                                        @foreach($aiData['suggestions'] as $suggestion)
                                            <li>{{ $suggestion }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- AI Grading Button (for essay questions) --}}
                    @if($answer->question->type === 'essay' && !empty($answer->answer_text) && !$answer->ai_graded)
                        <div class="mb-3">
                            <form action="{{ route('admin.quiz-attempts.ai-grade', [$attempt->id, $answer->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('هل تريد تصحيح هذا السؤال باستخدام AI؟');">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary btn-sm" id="ai-grade-btn-{{ $answer->id }}">
                                    <i class="bi bi-robot me-1"></i> تصحيح تلقائي بالAI
                                </button>
                            </form>
                        </div>
                    @endif

                    <input type="hidden" name="grades[{{ $index }}][answer_id]" value="{{ $answer->id }}">
                    
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">الدرجة الممنوحة</label>
                            <div class="input-group">
                                <input type="number" name="grades[{{ $index }}][points]" 
                                       class="form-control" min="0" max="{{ $answer->max_points }}" 
                                       step="0.5" value="{{ $answer->points_earned ?? ($answer->ai_graded && isset($answer->ai_grading_data['points']) ? $answer->ai_grading_data['points'] : '') }}" required>
                                <span class="input-group-text">/ {{ $answer->max_points }}</span>
                            </div>
                            @if($answer->ai_graded)
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    يمكنك تعديل الدرجة المقترحة من AI
                                </small>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">ملاحظة للطالب (اختياري)</label>
                            <textarea name="grades[{{ $index }}][feedback]" 
                                      class="form-control" rows="2"
                                      placeholder="ملاحظة أو تعليق على الإجابة...">{{ $answer->feedback ?? ($answer->ai_graded && isset($answer->ai_grading_data['feedback']) ? $answer->ai_grading_data['feedback'] : '') }}</textarea>
                        </div>
                    </div>

                    @if($answer->question->explanation)
                        <div class="mt-3 p-2 bg-success-transparent rounded">
                            <small class="text-success">
                                <i class="bi bi-lightbulb me-1"></i>
                                <strong>الإجابة النموذجية:</strong> {{ $answer->question->explanation }}
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        <div class="card custom-card">
            <div class="card-body d-flex justify-content-between align-items-center">
                <a href="{{ route('admin.quiz-attempts.show', $attempt->id) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-right me-1"></i> رجوع
                </a>
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="bi bi-check-lg me-1"></i> حفظ التصحيح
                </button>
            </div>
        </div>
    </form>

        </div>
    </div>
    <!-- End::app-content -->
@stop

@section('js')
@stop

