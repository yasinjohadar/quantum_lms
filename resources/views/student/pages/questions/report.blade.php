@extends('student.layouts.master')

@section('page-title')
    تقرير الأسئلة - {{ $lesson->title }}
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">تقرير الأسئلة</h4>
                <p class="mb-0 text-muted">
                    {{ $subject->name }} - {{ $lesson->title }}
                </p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.subjects') }}">المواد الدراسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.subjects.show', $subject->id) }}">{{ $subject->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.lessons.show', $lesson->id) }}">{{ $lesson->title }}</a></li>
                    <li class="breadcrumb-item active">تقرير الأسئلة</li>
                </ol>
            </nav>
        </div>
        <!-- End Page Header -->

        <!-- ملخص النتائج -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-lg bg-primary-transparent rounded-circle">
                                    <i class="bi bi-question-circle text-primary fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="mb-1 text-muted">إجمالي الأسئلة</p>
                                <h4 class="mb-0 fw-semibold">{{ $totalQuestions }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-lg bg-success-transparent rounded-circle">
                                    <i class="bi bi-check-circle text-success fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="mb-1 text-muted">الإجابات الصحيحة</p>
                                <h4 class="mb-0 fw-semibold">{{ $correctAnswers }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-lg bg-warning-transparent rounded-circle">
                                    <i class="bi bi-star text-warning fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="mb-1 text-muted">النقاط المكتسبة</p>
                                <h4 class="mb-0 fw-semibold">{{ number_format($earnedPoints, 2) }} / {{ number_format($totalPoints, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-lg bg-info-transparent rounded-circle">
                                    <i class="bi bi-percent text-info fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="mb-1 text-muted">النسبة المئوية</p>
                                <h4 class="mb-0 fw-semibold">{{ number_format($percentage, 1) }}%</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- شريط التقدم -->
        <div class="card custom-card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-semibold">التقدم الإجمالي</span>
                    <span class="text-muted">{{ number_format($percentage, 1) }}%</span>
                </div>
                <div class="progress" style="height: 25px;">
                    <div class="progress-bar {{ $percentage >= 70 ? 'bg-success' : ($percentage >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                         role="progressbar" 
                         style="width: {{ $percentage }}%" 
                         aria-valuenow="{{ $percentage }}" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                        {{ number_format($percentage, 1) }}%
                    </div>
                </div>
            </div>
        </div>

        <!-- تفاصيل الأسئلة -->
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-list-check me-2"></i>
                    تفاصيل الإجابات
                </h6>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @foreach($questions as $index => $question)
                        @php
                            $attempt = $attempts[$question->id] ?? null;
                            $answer = $attempt ? $attempt->answer : null;
                            $isCorrect = $attempt ? $attempt->is_correct : false;
                            $pointsEarned = $answer ? $answer->points_earned : 0;
                            $maxPoints = $question->default_points;
                        @endphp
                        <div class="list-group-item {{ $isCorrect ? 'border-success' : 'border-danger' }}">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-md bg-{{ $isCorrect ? 'success' : 'danger' }}-transparent rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-{{ $isCorrect ? 'check-circle-fill' : 'x-circle-fill' }} text-{{ $isCorrect ? 'success' : 'danger' }}"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">
                                                <span class="badge bg-secondary me-2">#{{ $index + 1 }}</span>
                                                {{ $question->title }}
                                            </h6>
                                            @if($question->content)
                                                <p class="text-muted small mb-0">{{ Str::limit(strip_tags($question->content), 150) }}</p>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $isCorrect ? 'success' : 'danger' }} mb-2">
                                                {{ $isCorrect ? 'صحيحة' : 'خاطئة' }}
                                            </span>
                                            <div>
                                                <small class="text-muted">
                                                    {{ number_format($pointsEarned, 2) }} / {{ number_format($maxPoints, 2) }} نقطة
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <span class="badge bg-{{ $questionTypeColors[$question->type] ?? 'primary' }}-transparent text-{{ $questionTypeColors[$question->type] ?? 'primary' }}">
                                            <i class="bi {{ $questionTypeIcons[$question->type] ?? 'bi-question' }} me-1"></i>
                                            {{ $questionTypes[$question->type] ?? $question->type }}
                                        </span>
                                        @if($question->difficulty)
                                            <span class="badge bg-secondary-transparent text-secondary">
                                                {{ $questionDifficulties[$question->difficulty] ?? $question->difficulty }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- عرض الإجابة -->
                                    <div class="card bg-light mb-2">
                                        <div class="card-body">
                                            <h6 class="mb-2">
                                                <i class="bi bi-pencil-square me-2"></i>
                                                إجابتك:
                                            </h6>
                                            @if($answer)
                                                @if($question->type === 'single_choice' || $question->type === 'true_false')
                                                    @php
                                                        $selectedOption = $question->options->firstWhere('id', $answer->selected_options[0] ?? null);
                                                    @endphp
                                                    @if($selectedOption)
                                                        <p class="mb-0">{{ $selectedOption->content }}</p>
                                                    @else
                                                        <p class="text-muted mb-0">لم يتم اختيار إجابة</p>
                                                    @endif
                                                @elseif($question->type === 'multiple_choice')
                                                    @php
                                                        $selectedOptions = $question->options->whereIn('id', $answer->selected_options ?? []);
                                                    @endphp
                                                    @if($selectedOptions->count() > 0)
                                                        <ul class="mb-0">
                                                            @foreach($selectedOptions as $option)
                                                                <li>{{ $option->content }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <p class="text-muted mb-0">لم يتم اختيار إجابات</p>
                                                    @endif
                                                @elseif($question->type === 'short_answer' || $question->type === 'essay')
                                                    <p class="mb-0">{{ $answer->answer_text ?? 'لم يتم الإجابة' }}</p>
                                                @elseif($question->type === 'matching')
                                                    @php
                                                        $pairs = is_array($answer->matching_pairs) ? $answer->matching_pairs : json_decode($answer->matching_pairs, true);
                                                    @endphp
                                                    @if($pairs)
                                                        <ul class="mb-0">
                                                            @foreach($pairs as $optionId => $target)
                                                                @php
                                                                    $option = $question->options->firstWhere('id', $optionId);
                                                                @endphp
                                                                @if($option)
                                                                    <li><strong>{{ $option->content }}</strong> → {{ $target }}</li>
                                                                @endif
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <p class="text-muted mb-0">لم يتم المطابقة</p>
                                                    @endif
                                                @elseif($question->type === 'ordering')
                                                    @php
                                                        $order = is_array($answer->ordering) ? $answer->ordering : json_decode($answer->ordering, true);
                                                    @endphp
                                                    @if($order)
                                                        <ol class="mb-0">
                                                            @foreach($order as $optionId)
                                                                @php
                                                                    $option = $question->options->firstWhere('id', $optionId);
                                                                @endphp
                                                                @if($option)
                                                                    <li>{{ $option->content }}</li>
                                                                @endif
                                                            @endforeach
                                                        </ol>
                                                    @else
                                                        <p class="text-muted mb-0">لم يتم الترتيب</p>
                                                    @endif
                                                @elseif($question->type === 'numerical')
                                                    <p class="mb-0">{{ $answer->numeric_answer ?? 'لم يتم الإجابة' }}</p>
                                                @elseif($question->type === 'fill_blanks')
                                                    @php
                                                        $blanks = is_array($answer->fill_blanks_answers) ? $answer->fill_blanks_answers : json_decode($answer->fill_blanks_answers, true);
                                                    @endphp
                                                    @if($blanks)
                                                        <ul class="mb-0">
                                                            @foreach($blanks as $blank)
                                                                <li>{{ $blank }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <p class="text-muted mb-0">لم يتم ملء الفراغات</p>
                                                    @endif
                                                @else
                                                    <p class="text-muted mb-0">نوع سؤال غير معروف</p>
                                                @endif
                                            @else
                                                <p class="text-muted mb-0">لم يتم حفظ إجابة</p>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- عرض الإجابة الصحيحة -->
                                    <div class="card {{ $isCorrect ? 'bg-success-transparent' : 'bg-danger-transparent' }} border-{{ $isCorrect ? 'success' : 'danger' }}">
                                        <div class="card-body">
                                            <h6 class="mb-2">
                                                <i class="bi bi-check-circle me-2"></i>
                                                الإجابة الصحيحة:
                                            </h6>
                                            @if($question->type === 'single_choice' || $question->type === 'true_false')
                                                @php
                                                    $correctOption = $question->correctOptions->first();
                                                @endphp
                                                @if($correctOption)
                                                    <p class="mb-0">{{ $correctOption->content }}</p>
                                                @endif
                                            @elseif($question->type === 'multiple_choice')
                                                <ul class="mb-0">
                                                    @foreach($question->correctOptions as $option)
                                                        <li>{{ $option->content }}</li>
                                                    @endforeach
                                                </ul>
                                            @elseif($question->type === 'matching')
                                                <ul class="mb-0">
                                                    @foreach($question->options as $option)
                                                        @if($option->match_target)
                                                            <li><strong>{{ $option->content }}</strong> → {{ $option->match_target }}</li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            @elseif($question->type === 'ordering')
                                                <ol class="mb-0">
                                                    @foreach($question->options->sortBy('correct_order') as $option)
                                                        <li>{{ $option->content }}</li>
                                                    @endforeach
                                                </ol>
                                            @else
                                                <p class="text-muted mb-0">يتم تقييم هذه الإجابة يدوياً</p>
                                            @endif
                                        </div>
                                    </div>

                                    @if($question->explanation)
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="bi bi-info-circle me-1"></i>
                                                <strong>شرح:</strong> {{ $question->explanation }}
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- أزرار التنقل -->
        <div class="card custom-card mt-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('student.lessons.show', $lesson->id) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-right me-1"></i>
                        العودة إلى الدرس
                    </a>
                    <a href="{{ route('student.subjects.show', $subject->id) }}" class="btn btn-primary">
                        <i class="bi bi-list me-1"></i>
                        عرض جميع الدروس
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Container closed -->
</div>
<!-- main-content closed -->
@stop


