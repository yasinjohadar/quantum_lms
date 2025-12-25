@extends('student.layouts.master')

@section('page-title')
    تقدمي في {{ $section->title }}
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">تقدمي في {{ $section->title }}</h4>
                <p class="mb-0 text-muted">عرض تفصيلي لتقدمك في هذا القسم الدراسي</p>
            </div>
            <div>
                <a href="{{ route('student.progress.subject', $section->subject_id) }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-right me-1"></i>
                    العودة للمادة
                </a>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Overall Progress Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">التقدم الكلي</h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="text-primary mb-3">{{ round($progress['overall_percentage'] ?? 0, 1) }}%</h2>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-primary" role="progressbar" 
                                         style="width: {{ $progress['overall_percentage'] ?? 0 }}%"
                                         aria-valuenow="{{ $progress['overall_percentage'] ?? 0 }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        {{ round($progress['overall_percentage'] ?? 0, 1) }}%
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row text-center">
                                    <div class="col-6 mb-3">
                                        <div class="border rounded p-2">
                                            <h4 class="mb-0 text-success">{{ $progress['lessons_completed'] ?? 0 }}</h4>
                                            <small class="text-muted">دروس مكتملة</small>
                                            <div><small class="text-muted">من {{ $progress['lessons_total'] ?? 0 }}</small></div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="border rounded p-2">
                                            <h4 class="mb-0 text-info">{{ $progress['quizzes_completed'] ?? 0 }}</h4>
                                            <small class="text-muted">اختبارات</small>
                                            <div><small class="text-muted">من {{ $progress['quizzes_total'] ?? 0 }}</small></div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="border rounded p-2">
                                            <h4 class="mb-0 text-warning">{{ $progress['questions_completed'] ?? 0 }}</h4>
                                            <small class="text-muted">أسئلة</small>
                                            <div><small class="text-muted">من {{ $progress['questions_total'] ?? 0 }}</small></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lessons -->
        @if(isset($sectionDetails['lessons']) && count($sectionDetails['lessons']) > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">الدروس</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>الدرس</th>
                                            <th>الوحدة</th>
                                            <th>الحالة</th>
                                            <th>التقدم</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sectionDetails['lessons'] as $item)
                                            @php
                                                $lesson = $item['lesson'];
                                                $unit = $item['unit'];
                                                $lessonProgress = $item['progress'];
                                                $isCompleted = $lessonProgress['completed'] ?? false;
                                                $isAttended = $lessonProgress['attended'] ?? false;
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong>{{ $lesson->title }}</strong>
                                                    @if($lesson->duration)
                                                        <br><small class="text-muted">
                                                            <i class="bi bi-clock me-1"></i>
                                                            {{ gmdate('H:i', $lesson->duration) }}
                                                        </small>
                                                    @endif
                                                </td>
                                                <td>{{ $unit->title }}</td>
                                                <td>
                                                    @if($isCompleted)
                                                        <span class="badge bg-success">مكتمل</span>
                                                    @elseif($isAttended)
                                                        <span class="badge bg-info">تم الحضور</span>
                                                    @else
                                                        <span class="badge bg-secondary">لم يبدأ</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar {{ $isCompleted ? 'bg-success' : ($isAttended ? 'bg-info' : 'bg-secondary') }}" 
                                                             role="progressbar" 
                                                             style="width: {{ $lessonProgress['percentage'] ?? 0 }}%">
                                                            {{ round($lessonProgress['percentage'] ?? 0, 0) }}%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <a href="{{ route('student.lessons.show', $lesson->id) }}" 
                                                       class="btn btn-primary btn-sm">
                                                        <i class="bi bi-play-circle me-1"></i>
                                                        عرض الدرس
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

        <!-- Quizzes -->
        @if(isset($sectionDetails['quizzes']) && count($sectionDetails['quizzes']) > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">الاختبارات</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>الاختبار</th>
                                            <th>الوحدة</th>
                                            <th>الحالة</th>
                                            <th>النتيجة</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sectionDetails['quizzes'] as $item)
                                            @php
                                                $quiz = $item['quiz'];
                                                $unit = $item['unit'];
                                                $attempt = $item['attempt'];
                                                $isCompleted = $item['completed'] ?? false;
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong>{{ $quiz->title }}</strong>
                                                    @if($quiz->duration_minutes)
                                                        <br><small class="text-muted">
                                                            <i class="bi bi-clock me-1"></i>
                                                            {{ $quiz->duration_minutes }} دقيقة
                                                        </small>
                                                    @endif
                                                </td>
                                                <td>{{ $unit->title }}</td>
                                                <td>
                                                    @if($isCompleted)
                                                        <span class="badge bg-success">مكتمل</span>
                                                    @elseif($attempt)
                                                        <span class="badge bg-warning">قيد التنفيذ</span>
                                                    @else
                                                        <span class="badge bg-secondary">لم يبدأ</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($attempt && $isCompleted)
                                                        <strong class="text-primary">{{ round($attempt->percentage, 1) }}%</strong>
                                                        <br><small class="text-muted">
                                                            {{ $attempt->score }}/{{ $attempt->max_score }}
                                                        </small>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($attempt && !$isCompleted)
                                                        <a href="{{ route('student.quizzes.show', ['quiz' => $quiz->id, 'attempt' => $attempt->id]) }}" 
                                                           class="btn btn-warning btn-sm">
                                                            <i class="bi bi-play-circle me-1"></i>
                                                            متابعة الاختبار
                                                        </a>
                                                    @else
                                                        <a href="{{ route('student.quizzes.start', $quiz->id) }}" 
                                                           class="btn btn-primary btn-sm">
                                                            <i class="bi bi-play-circle me-1"></i>
                                                            {{ $isCompleted ? 'إعادة المحاولة' : 'بدء الاختبار' }}
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

        <!-- Questions -->
        @if(isset($sectionDetails['questions']) && count($sectionDetails['questions']) > 0)
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">الأسئلة المنفصلة</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>السؤال</th>
                                            <th>الوحدة</th>
                                            <th>النوع</th>
                                            <th>الصعوبة</th>
                                            <th>الحالة</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sectionDetails['questions'] as $item)
                                            @php
                                                $question = $item['question'];
                                                $unit = $item['unit'];
                                                $attempt = $item['attempt'];
                                                $isCompleted = $item['completed'] ?? false;
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong>{{ \Illuminate\Support\Str::limit($question->question_text, 50) }}</strong>
                                                    @if($question->duration_minutes)
                                                        <br><small class="text-muted">
                                                            <i class="bi bi-clock me-1"></i>
                                                            {{ $question->duration_minutes }} دقيقة
                                                        </small>
                                                    @endif
                                                </td>
                                                <td>{{ $unit->title }}</td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        {{ \App\Models\Question::TYPES[$question->type] ?? $question->type }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $question->difficulty === 'hard' ? 'danger' : ($question->difficulty === 'medium' ? 'warning' : 'success') }}">
                                                        {{ \App\Models\Question::DIFFICULTIES[$question->difficulty] ?? $question->difficulty }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($isCompleted)
                                                        <span class="badge bg-success">مكتمل</span>
                                                    @elseif($attempt)
                                                        <span class="badge bg-warning">قيد التنفيذ</span>
                                                    @else
                                                        <span class="badge bg-secondary">لم يبدأ</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($attempt && !$isCompleted)
                                                        <a href="{{ route('student.questions.show', ['question' => $question->id, 'attempt' => $attempt->id]) }}" 
                                                           class="btn btn-warning btn-sm">
                                                            <i class="bi bi-play-circle me-1"></i>
                                                            متابعة السؤال
                                                        </a>
                                                    @else
                                                        <a href="{{ route('student.questions.start.specific', $question->id) }}" 
                                                           class="btn btn-primary btn-sm">
                                                            <i class="bi bi-play-circle me-1"></i>
                                                            {{ $isCompleted ? 'إعادة المحاولة' : 'بدء السؤال' }}
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

        @if((!isset($sectionDetails['lessons']) || count($sectionDetails['lessons']) == 0) && 
            (!isset($sectionDetails['quizzes']) || count($sectionDetails['quizzes']) == 0) && 
            (!isset($sectionDetails['questions']) || count($sectionDetails['questions']) == 0))
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="mb-2">لا يوجد محتوى</h5>
                    <p class="text-muted">هذا القسم لا يحتوي على دروس أو اختبارات أو أسئلة بعد</p>
                </div>
            </div>
        @endif
    </div>
</div>
<!-- End::app-content -->
@stop

