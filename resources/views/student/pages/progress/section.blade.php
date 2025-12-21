@extends('student.layouts.master')

@section('page-title')
    {{ $section->title }}
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">{{ $section->title }}</h4>
                <p class="mb-0 text-muted">{{ $subject->name }}</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.progress.index') }}">تقدمي الدراسي</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.progress.subject', $subject->id) }}">{{ $subject->name }}</a></li>
                    <li class="breadcrumb-item active">{{ $section->title }}</li>
                </ol>
            </nav>
        </div>
        <!-- End Page Header -->

        @php
            $progress = $details['progress'];
        @endphp

        <!-- إحصائيات القسم -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
                <div class="card custom-card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">التقدم الإجمالي</h6>
                        <h3 class="mb-0 fw-bold text-primary">{{ number_format($progress['overall_percentage'], 1) }}%</h3>
                        <div class="progress mt-2" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: {{ $progress['overall_percentage'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
                <div class="card custom-card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">الدروس</h6>
                        <h3 class="mb-0 fw-bold text-info">
                            {{ $progress['lessons_completed'] }}/{{ $progress['lessons_total'] }}
                        </h3>
                        <small class="text-muted">{{ number_format($progress['lessons_percentage'], 1) }}%</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
                <div class="card custom-card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">الاختبارات</h6>
                        <h3 class="mb-0 fw-bold text-success">
                            {{ $progress['quizzes_completed'] }}/{{ $progress['quizzes_total'] }}
                        </h3>
                        <small class="text-muted">{{ number_format($progress['quizzes_percentage'], 1) }}%</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
                <div class="card custom-card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">الأسئلة</h6>
                        <h3 class="mb-0 fw-bold text-warning">
                            {{ $progress['questions_completed'] }}/{{ $progress['questions_total'] }}
                        </h3>
                        <small class="text-muted">{{ number_format($progress['questions_percentage'], 1) }}%</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- الدروس -->
        @if(count($details['lessons']) > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-play-circle me-2"></i>
                                الدروس ({{ $progress['lessons_completed'] }}/{{ $progress['lessons_total'] }})
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>الدرس</th>
                                            <th>الوحدة</th>
                                            <th>الحالة</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($details['lessons'] as $lessonItem)
                                            @php
                                                $lesson = $lessonItem['lesson'];
                                                $unit = $lessonItem['unit'];
                                                $lessonProgress = $lessonItem['progress'];
                                            @endphp
                                            <tr>
                                                <td>
                                                    <h6 class="mb-0">{{ $lesson->title }}</h6>
                                                    @if($lesson->duration)
                                                        <small class="text-muted">
                                                            <i class="bi bi-clock me-1"></i>
                                                            {{ $lesson->formatted_duration }}
                                                        </small>
                                                    @endif
                                                </td>
                                                <td>{{ $unit->title }}</td>
                                                <td>
                                                    @if($lessonProgress['completed'])
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check-circle me-1"></i>
                                                            مكتمل
                                                        </span>
                                                    @elseif($lessonProgress['attended'])
                                                        <span class="badge bg-info">
                                                            <i class="bi bi-calendar-check me-1"></i>
                                                            حاضر
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">
                                                            <i class="bi bi-circle me-1"></i>
                                                            غير مكتمل
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('student.lessons.show', $lesson->id) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye me-1"></i>
                                                        عرض
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- الاختبارات -->
        @if(count($details['quizzes']) > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-clipboard-check me-2"></i>
                                الاختبارات ({{ $progress['quizzes_completed'] }}/{{ $progress['quizzes_total'] }})
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($details['quizzes'] as $quizItem)
                                    @php
                                        $quiz = $quizItem['quiz'];
                                        $unit = $quizItem['unit'];
                                        $attempt = $quizItem['attempt'];
                                        $completed = $quizItem['completed'];
                                    @endphp
                                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-3">
                                        <div class="card border h-100">
                                            <div class="card-body">
                                                <h6 class="mb-2">{{ $quiz->title }}</h6>
                                                <p class="text-muted small mb-2">{{ $unit->title }}</p>
                                                <div class="d-flex flex-wrap gap-2 mb-2">
                                                    @if($quiz->duration_minutes)
                                                        <span class="badge bg-info-transparent text-info">
                                                            <i class="bi bi-clock me-1"></i>
                                                            {{ $quiz->duration_minutes }} دقيقة
                                                        </span>
                                                    @endif
                                                    <span class="badge bg-warning-transparent text-warning">
                                                        <i class="bi bi-star me-1"></i>
                                                        {{ $quiz->total_points }} نقطة
                                                    </span>
                                                </div>
                                                <div class="mb-2">
                                                    @if($completed)
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check-circle me-1"></i>
                                                            مكتمل
                                                        </span>
                                                        @if($attempt && $attempt->passed !== null)
                                                            <span class="badge bg-{{ $attempt->passed ? 'success' : 'danger' }}">
                                                                {{ $attempt->passed ? 'نجح' : 'رسب' }}
                                                            </span>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-secondary">
                                                            <i class="bi bi-circle me-1"></i>
                                                            غير مكتمل
                                                        </span>
                                                    @endif
                                                </div>
                                                @if($completed && $attempt)
                                                    <a href="{{ route('student.quizzes.result', ['quiz' => $quiz->id, 'attempt' => $attempt->id]) }}" class="btn btn-sm btn-outline-primary w-100">
                                                        <i class="bi bi-eye me-1"></i>
                                                        عرض النتيجة
                                                    </a>
                                                @else
                                                    <a href="{{ route('student.quizzes.start', $quiz->id) }}" class="btn btn-sm btn-primary w-100">
                                                        <i class="bi bi-play-circle me-1"></i>
                                                        بدء الاختبار
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- الأسئلة -->
        @if(count($details['questions']) > 0)
            <div class="row">
                <div class="col-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-question-circle me-2"></i>
                                الأسئلة ({{ $progress['questions_completed'] }}/{{ $progress['questions_total'] }})
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>السؤال</th>
                                            <th>الوحدة</th>
                                            <th>النوع</th>
                                            <th>الحالة</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($details['questions'] as $questionItem)
                                            @php
                                                $question = $questionItem['question'];
                                                $unit = $questionItem['unit'];
                                                $attempt = $questionItem['attempt'];
                                                $completed = $questionItem['completed'];
                                            @endphp
                                            <tr>
                                                <td>
                                                    <h6 class="mb-0">{{ $question->title }}</h6>
                                                    @if($question->default_points)
                                                        <small class="text-muted">
                                                            <i class="bi bi-star me-1"></i>
                                                            {{ $question->default_points }} نقطة
                                                        </small>
                                                    @endif
                                                </td>
                                                <td>{{ $unit->title }}</td>
                                                <td>
                                                    <span class="badge bg-primary-transparent text-primary">
                                                        {{ \App\Models\Question::TYPES[$question->type] ?? $question->type }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($completed)
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check-circle me-1"></i>
                                                            مكتمل
                                                        </span>
                                                        @if($attempt && $attempt->is_correct !== null)
                                                            <span class="badge bg-{{ $attempt->is_correct ? 'success' : 'danger' }}">
                                                                {{ $attempt->is_correct ? 'صحيح' : 'خاطئ' }}
                                                            </span>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-secondary">
                                                            <i class="bi bi-circle me-1"></i>
                                                            غير مكتمل
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($completed && $attempt)
                                                        <a href="{{ route('student.questions.show', ['question' => $question->id, 'attempt' => $attempt->id]) }}" class="btn btn-sm btn-outline-info">
                                                            <i class="bi bi-eye me-1"></i>
                                                            عرض
                                                        </a>
                                                    @else
                                                        <a href="{{ route('student.questions.start', ['question' => $question->id]) }}" class="btn btn-sm btn-primary">
                                                            <i class="bi bi-play-circle me-1"></i>
                                                            بدء الإجابة
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <!-- Container closed -->
</div>
<!-- main-content closed -->
@stop

