@extends('student.layouts.master')

@section('page-title')
    {{ $lesson->title }}
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">{{ $lesson->title }}</h4>
                <p class="mb-0 text-muted">
                    {{ $subject->name }} - {{ $lesson->unit->title }}
                </p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.subjects') }}">المواد الدراسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.subjects.show', $subject->id) }}">{{ $subject->name }}</a></li>
                    <li class="breadcrumb-item active">{{ $lesson->title }}</li>
                </ol>
            </nav>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <!-- مشغل الفيديو -->
            <div class="col-xl-8 col-lg-12">
                <div class="card">
                    <div class="card-body">
                        @if($lesson->embed_url)
                            @php
                                $actualType = $lesson->actual_video_type;
                            @endphp
                            <div class="ratio ratio-16x9 mb-3 bg-dark rounded overflow-hidden">
                                @if($actualType === 'youtube')
                                    <iframe
                                        src="{{ $lesson->embed_url }}?rel=0&modestbranding=1"
                                        title="{{ $lesson->title }}"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                        allowfullscreen
                                        loading="lazy"
                                    ></iframe>
                                @elseif($actualType === 'vimeo')
                                    <iframe
                                        src="{{ $lesson->embed_url }}?title=0&byline=0&portrait=0"
                                        title="{{ $lesson->title }}"
                                        frameborder="0"
                                        allow="autoplay; fullscreen; picture-in-picture"
                                        allowfullscreen
                                        loading="lazy"
                                    ></iframe>
                                @elseif($actualType === 'upload')
                                    <video controls class="w-100 h-100" 
                                           poster="{{ $lesson->thumbnail ? asset('storage/'.$lesson->thumbnail) : '' }}"
                                           controlsList="nodownload">
                                        <source src="{{ $lesson->embed_url }}" type="video/mp4">
                                        <source src="{{ $lesson->embed_url }}" type="video/webm">
                                        <source src="{{ $lesson->embed_url }}" type="video/ogg">
                                        المتصفح لا يدعم تشغيل الفيديو.
                                    </video>
                                @else
                                    <video controls class="w-100 h-100" 
                                           poster="{{ $lesson->thumbnail ? asset('storage/'.$lesson->thumbnail) : '' }}">
                                        <source src="{{ $lesson->embed_url }}" type="video/mp4">
                                        المتصفح لا يدعم تشغيل الفيديو.
                                    </video>
                                @endif
                            </div>
                        @else
                            <div class="text-center py-5 text-muted bg-light rounded">
                                <i class="bi bi-collection-play display-5 d-block mb-2"></i>
                                <p class="mb-0">لم يتم ضبط فيديو لهذا الدرس بعد.</p>
                            </div>
                        @endif

                        @if($lesson->description)
                            <div class="mt-3">
                                <h6 class="mb-2">وصف الدرس</h6>
                                <p class="text-muted mb-0">{{ $lesson->description }}</p>
                            </div>
                        @endif
                        
                        <!-- زر تحديد حالة الدرس -->
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="mb-2">حالة الدرس</h6>
                                    @if($lessonCompletion)
                                        <span class="badge bg-{{ $lessonCompletion->status === 'completed' ? 'success' : 'info' }} fs-6">
                                            <i class="bi bi-{{ $lessonCompletion->status === 'completed' ? 'check-circle-fill' : 'calendar-check' }} me-1"></i>
                                            {{ $lessonCompletion->status === 'completed' ? 'تم الإكمال' : 'تم الحضور' }}
                                        </span>
                                        <small class="text-muted d-block mt-1">
                                            <i class="bi bi-clock me-1"></i>
                                            {{ $lessonCompletion->marked_at->format('Y-m-d H:i') }}
                                        </small>
                                    @else
                                        <span class="badge bg-secondary fs-6">
                                            <i class="bi bi-circle me-1"></i>
                                            لم يتم التحديد
                                        </span>
                                    @endif
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" 
                                            class="btn btn-{{ $lessonCompletion && $lessonCompletion->status === 'attended' ? 'info' : 'outline-info' }} btn-sm" 
                                            id="mark-attended-btn"
                                            data-status="attended">
                                        <i class="bi bi-calendar-check me-1"></i>
                                        تم الحضور
                                    </button>
                                    <button type="button" 
                                            class="btn btn-{{ $lessonCompletion && $lessonCompletion->status === 'completed' ? 'success' : 'outline-success' }} btn-sm" 
                                            id="mark-completed-btn"
                                            data-status="completed">
                                        <i class="bi bi-check-circle me-1"></i>
                                        تم الإكمال
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- التنقل بين الدروس -->
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                @if($previousLesson)
                                    <a href="{{ route('student.lessons.show', $previousLesson->id) }}" class="btn btn-outline-primary">
                                        <i class="bi bi-chevron-right me-1"></i>
                                        الدرس السابق
                                    </a>
                                @endif
                            </div>
                            <div>
                                <a href="{{ route('student.subjects.show', $subject->id) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-list me-1"></i>
                                    قائمة الدروس
                                </a>
                            </div>
                            <div>
                                @if($nextLesson)
                                    <a href="{{ route('student.lessons.show', $nextLesson->id) }}" class="btn btn-outline-primary">
                                        الدرس التالي
                                        <i class="bi bi-chevron-left ms-1"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- الاختبارات المرتبطة بالدرس -->
                @if($lessonQuizzes->count() > 0)
                    <div class="card mt-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-clipboard-check me-2"></i>
                                اختبارات هذا الدرس
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($lessonQuizzes as $quiz)
                                    @php
                                        $attempt = $quizAttempts[$quiz->id] ?? null;
                                        $hasAttempt = $attempt !== null;
                                        $isInProgress = $attempt && $attempt->status === 'in_progress';
                                        $isCompleted = $attempt && in_array($attempt->status, ['completed', 'timed_out']);
                                    @endphp
                                    <div class="col-md-6 mb-3">
                                        <div class="card border">
                                            <div class="card-body">
                                                <div class="d-flex align-items-start">
                                                    @if($quiz->image)
                                                        <img src="{{ asset('storage/' . $quiz->image) }}" 
                                                             alt="{{ $quiz->title }}" 
                                                             class="rounded me-3" 
                                                             style="width: 60px; height: 60px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-primary-transparent rounded d-flex align-items-center justify-content-center me-3" 
                                                             style="width: 60px; height: 60px;">
                                                            <i class="bi bi-clipboard-check text-primary fs-4"></i>
                                                        </div>
                                                    @endif
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                                            <h6 class="mb-0 fw-semibold">{{ $quiz->title }}</h6>
                                                            @if($hasAttempt)
                                                                @if($isInProgress)
                                                                    <span class="badge bg-warning">
                                                                        <i class="bi bi-clock me-1"></i>
                                                                        جاري
                                                                    </span>
                                                                @elseif($isCompleted)
                                                                    <span class="badge bg-{{ $attempt->passed ? 'success' : 'danger' }}">
                                                                        <i class="bi bi-{{ $attempt->passed ? 'check-circle' : 'x-circle' }} me-1"></i>
                                                                        {{ $attempt->passed ? 'نجح' : 'رسب' }}
                                                                    </span>
                                                                @endif
                                                            @else
                                                                <span class="badge bg-secondary">
                                                                    <i class="bi bi-circle me-1"></i>
                                                                    لم يتم البدء
                                                                </span>
                                                            @endif
                                                        </div>
                                                        @if($quiz->description)
                                                            <p class="text-muted small mb-2">{{ \Illuminate\Support\Str::limit($quiz->description, 60) }}</p>
                                                        @endif
                                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                                            @if($quiz->duration_minutes)
                                                                <span class="badge bg-info-transparent text-info">
                                                                    <i class="bi bi-clock me-1"></i>
                                                                    {{ $quiz->duration_minutes }} دقيقة
                                                                </span>
                                                            @endif
                                                            <span class="badge bg-success-transparent text-success">
                                                                <i class="bi bi-question-circle me-1"></i>
                                                                {{ $quiz->questions->count() }} سؤال
                                                            </span>
                                                            <span class="badge bg-warning-transparent text-warning">
                                                                <i class="bi bi-star me-1"></i>
                                                                {{ $quiz->total_points }} نقطة
                                                            </span>
                                                        </div>
                                                        @if($isInProgress)
                                                            <a href="{{ route('student.quizzes.show', ['quiz' => $quiz->id, 'attempt' => $attempt->id]) }}" class="btn btn-sm btn-warning">
                                                                <i class="bi bi-arrow-left-circle me-1"></i>
                                                                متابعة الاختبار
                                                            </a>
                                                        @elseif($isCompleted)
                                                            <div class="d-flex gap-2">
                                                                <a href="{{ route('student.quizzes.result', ['quiz' => $quiz->id, 'attempt' => $attempt->id]) }}" class="btn btn-sm btn-info">
                                                                    <i class="bi bi-eye me-1"></i>
                                                                    عرض النتيجة
                                                                </a>
                                                                @if($quiz->max_attempts == 0 || $attempt->attempt_number < $quiz->max_attempts)
                                                                    <a href="{{ route('student.quizzes.start', $quiz->id) }}" class="btn btn-sm btn-primary">
                                                                        <i class="bi bi-arrow-clockwise me-1"></i>
                                                                        محاولة جديدة
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <a href="{{ route('student.quizzes.start', $quiz->id) }}" class="btn btn-sm btn-primary">
                                                                <i class="bi bi-play-circle me-1"></i>
                                                                بدء الاختبار
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- الأسئلة المرتبطة بالدرس -->
                @if($questions->count() > 0)
                    @php
                        $allCompleted = true;
                        $hasInProgress = false;
                        $completedCount = 0;
                        foreach($questions as $question) {
                            $attempt = $questionAttempts[$question->id] ?? null;
                            $isCompleted = $attempt && in_array($attempt->status, ['completed', 'timed_out']);
                            $isInProgress = $attempt && $attempt->status === 'in_progress';
                            if ($isInProgress) {
                                $hasInProgress = true;
                            }
                            if ($isCompleted) {
                                $completedCount++;
                            } else {
                                $allCompleted = false;
                            }
                        }
                    @endphp
                    <div class="card mt-3">
                        <div class="card-header bg-info text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="bi bi-question-circle me-2"></i>
                                    الأسئلة المرتبطة بهذا الدرس
                                </h6>
                                <span class="badge bg-white text-info">
                                    {{ $completedCount }} / {{ $questions->count() }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($allCompleted)
                                <div class="alert alert-success mb-3">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <strong>تهانينا!</strong> لقد أكملت جميع الأسئلة.
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('student.questions.report', $lesson->id) }}" class="btn btn-primary">
                                        <i class="bi bi-file-text me-2"></i>
                                        عرض التقرير النهائي
                                    </a>
                                    <a href="{{ route('student.questions.start', ['lesson_id' => $lesson->id]) }}" class="btn btn-outline-primary">
                                        <i class="bi bi-arrow-clockwise me-2"></i>
                                        إعادة المحاولة
                                    </a>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <div class="mb-3">
                                        <i class="bi bi-question-circle display-4 text-info"></i>
                                    </div>
                                    <h5 class="mb-2">ابدأ الإجابة على الأسئلة</h5>
                                    <p class="text-muted mb-4">
                                        سيتم عرض الأسئلة بشكل متسلسل. يجب إكمال كل سؤال قبل الانتقال للسؤال التالي.
                                    </p>
                                    @if($hasInProgress)
                                        <a href="{{ route('student.questions.start', ['lesson_id' => $lesson->id]) }}" class="btn btn-warning btn-lg">
                                            <i class="bi bi-arrow-left-circle me-2"></i>
                                            متابعة الأسئلة
                                        </a>
                                    @else
                                        <a href="{{ route('student.questions.start', ['lesson_id' => $lesson->id]) }}" class="btn btn-primary btn-lg">
                                            <i class="bi bi-play-circle me-2"></i>
                                            بدء الإجابة على الأسئلة
                                        </a>
                                    @endif
                                    @if($completedCount > 0)
                                        <div class="mt-3">
                                            <small class="text-muted">
                                                تم إكمال {{ $completedCount }} من {{ $questions->count() }} سؤال
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- معلومات الدرس والمرفقات -->
            <div class="col-xl-4 col-lg-12">
                <!-- معلومات الدرس -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">معلومات الدرس</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="fw-semibold">المادة:</span>
                            <span class="text-muted d-block">{{ $subject->name }}</span>
                        </div>
                        <div class="mb-2">
                            <span class="fw-semibold">الوحدة:</span>
                            <span class="text-muted d-block">{{ $lesson->unit->title }}</span>
                        </div>
                        <div class="mb-2">
                            <span class="fw-semibold">القسم:</span>
                            <span class="text-muted d-block">{{ $lesson->unit->section->title }}</span>
                        </div>
                        @if($lesson->duration)
                            <div class="mb-2">
                                <span class="fw-semibold">المدة:</span>
                                <span class="text-muted d-block">
                                    <i class="bi bi-clock me-1"></i>
                                    {{ $lesson->formatted_duration }}
                                </span>
                            </div>
                        @endif
                        <div class="mb-2">
                            <span class="fw-semibold">نوع الفيديو:</span>
                            <span class="badge bg-primary-transparent text-primary">
                                {{ $videoTypes[$lesson->video_type] ?? $lesson->video_type }}
                            </span>
                        </div>
                        @if($lesson->is_free)
                            <div class="mt-2">
                                <span class="badge bg-success">درس مجاني</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- المرفقات -->
                @if($lesson->attachments->count() > 0)
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">مرفقات الدرس</h6>
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                @foreach($lesson->attachments as $attachment)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">
                                                    <i class="bi {{ $attachment->type_icon }} me-2"></i>
                                                    {{ $attachment->title }}
                                                </h6>
                                                @if($attachment->description)
                                                    <p class="text-muted mb-1 small">{{ $attachment->description }}</p>
                                                @endif
                                                @if($attachment->file_size)
                                                    <small class="text-muted">
                                                        <i class="bi bi-file-earmark me-1"></i>
                                                        {{ $attachment->formatted_file_size }}
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            @if($attachment->type === 'link')
                                                <a href="{{ $attachment->access_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-box-arrow-up-right me-1"></i>
                                                    فتح الرابط
                                                </a>
                                            @elseif($attachment->is_downloadable)
                                                <a href="{{ $attachment->access_url }}" download class="btn btn-sm btn-outline-success">
                                                    <i class="bi bi-download me-1"></i>
                                                    تحميل
                                                </a>
                                            @else
                                                <a href="{{ $attachment->access_url }}" target="_blank" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye me-1"></i>
                                                    عرض
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- قائمة دروس الوحدة -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">دروس الوحدة</h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            @foreach($unitLessons as $unitLesson)
                                <a href="{{ route('student.lessons.show', $unitLesson->id) }}" 
                                   class="list-group-item list-group-item-action {{ $unitLesson->id === $lesson->id ? 'active' : '' }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">
                                                <i class="bi bi-play-circle me-2"></i>
                                                {{ $unitLesson->title }}
                                            </h6>
                                            @if($unitLesson->duration)
                                                <small class="text-muted">
                                                    <i class="bi bi-clock me-1"></i>
                                                    {{ $unitLesson->formatted_duration }}
                                                </small>
                                            @endif
                                        </div>
                                        @if($unitLesson->id === $lesson->id)
                                            <i class="bi bi-check-circle-fill text-white"></i>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- الأكورديون لعرض جميع الوحدات والدروس والاختبارات -->
                @if($sections->count() > 0)
                    <div class="card mt-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-folder me-2"></i>
                                محتوى المادة: {{ $subject->name }}
                            </h6>
                        </div>
                        <div class="card-body">
                            @foreach($sections as $section)
                                <div class="mb-4">
                                    <h5 class="mb-3">
                                        <i class="bi bi-folder me-2"></i>
                                        {{ $section->title }}
                                    </h5>
                                    @if($section->description)
                                        <p class="text-muted mb-3 small">{{ $section->description }}</p>
                                    @endif
                                    
                                    @if($section->units->count() > 0)
                                        <div class="accordion" id="section-{{ $section->id }}">
                                            @foreach($section->units as $unitIndex => $unit)
                                                @php
                                                    // تحديد إذا كانت هذه الوحدة تحتوي على الدرس الحالي
                                                    $containsCurrentLesson = $unit->lessons->contains('id', $lesson->id);
                                                @endphp
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="unit-heading-{{ $unit->id }}">
                                                        <button class="accordion-button {{ ($unitIndex > 0 && !$containsCurrentLesson) ? 'collapsed' : '' }}" 
                                                                type="button" 
                                                                data-bs-toggle="collapse" 
                                                                data-bs-target="#unit-{{ $unit->id }}" 
                                                                aria-expanded="{{ ($unitIndex === 0 || $containsCurrentLesson) ? 'true' : 'false' }}">
                                                            <i class="bi bi-file-text me-2"></i>
                                                            <span class="small">{{ $unit->title }}</span>
                                                            <span class="badge bg-secondary ms-2 small">{{ $unit->lessons->count() }} درس</span>
                                                        </button>
                                                    </h2>
                                                    <div id="unit-{{ $unit->id }}" 
                                                         class="accordion-collapse collapse {{ ($unitIndex === 0 || $containsCurrentLesson) ? 'show' : '' }}" 
                                                         data-bs-parent="#section-{{ $section->id }}">
                                                        <div class="accordion-body">
                                                            @if($unit->description)
                                                                <p class="text-muted mb-3 small">{{ $unit->description }}</p>
                                                            @endif
                                                            
                                                            @if($unit->lessons->count() > 0)
                                                                <div class="list-group mb-3">
                                                                    @foreach($unit->lessons as $unitLesson)
                                                                        <div class="list-group-item {{ $unitLesson->id === $lesson->id ? 'active' : '' }}">
                                                                            <div class="d-flex justify-content-between align-items-center">
                                                                                <div class="flex-grow-1">
                                                                                    <a href="{{ route('student.lessons.show', $unitLesson->id) }}" 
                                                                                       class="text-decoration-none {{ $unitLesson->id === $lesson->id ? 'text-white' : 'text-reset' }}">
                                                                                        <h6 class="mb-1 small">
                                                                                            <i class="bi bi-play-circle me-2 {{ $unitLesson->id === $lesson->id ? 'text-white' : 'text-primary' }}"></i>
                                                                                            {{ $unitLesson->title }}
                                                                                        </h6>
                                                                                    </a>
                                                                                    @if($unitLesson->description)
                                                                                        <p class="text-muted mb-0 small" style="font-size: 0.75rem;">{{ \Illuminate\Support\Str::limit($unitLesson->description, 60) }}</p>
                                                                                    @endif
                                                                                    <div class="mt-2">
                                                                                        @if($unitLesson->duration)
                                                                                            <span class="badge bg-secondary me-2 small">
                                                                                                <i class="bi bi-clock me-1"></i>
                                                                                                {{ $unitLesson->formatted_duration }}
                                                                                            </span>
                                                                                        @endif
                                                                                        @if($unitLesson->is_free)
                                                                                            <span class="badge bg-success small">مجاني</span>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                                @if($unitLesson->id === $lesson->id)
                                                                                    <i class="bi bi-check-circle-fill text-white"></i>
                                                                                @endif
                                                                            </div>

                                                                            {{-- اختبارات هذا الدرس --}}
                                                                            @if(isset($unitLesson->quizzes) && $unitLesson->quizzes->count() > 0)
                                                                                <div class="mt-3 ms-4">
                                                                                    <h6 class="text-primary mb-2 small" style="font-size: 0.75rem;">
                                                                                        <i class="bi bi-clipboard-check me-1"></i>
                                                                                        اختبارات هذا الدرس
                                                                                    </h6>
                                                                                    <div class="list-group list-group-flush">
                                                                                        @foreach($unitLesson->quizzes as $quiz)
                                                                                            @php
                                                                                                $userAttempt = $quiz->attempts->where('user_id', auth()->id())->sortByDesc('created_at')->first();
                                                                                            @endphp
                                                                                            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center bg-primary-transparent p-2">
                                                                                                <div class="flex-grow-1">
                                                                                                    <h6 class="mb-1 small" style="font-size: 0.75rem;">{{ $quiz->title }}</h6>
                                                                                                    <div class="d-flex flex-wrap gap-1">
                                                                                                        @if($quiz->duration_minutes)
                                                                                                            <span class="badge bg-secondary small" style="font-size: 0.65rem;">
                                                                                                                <i class="bi bi-clock me-1"></i>
                                                                                                                {{ $quiz->duration_minutes }} د
                                                                                                            </span>
                                                                                                        @endif
                                                                                                        <span class="badge bg-info text-dark small" style="font-size: 0.65rem;">
                                                                                                            <i class="bi bi-question-circle me-1"></i>
                                                                                                            {{ $quiz->questions_count ?? $quiz->questions->count() }} سؤال
                                                                                                        </span>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <a href="{{ route('student.quizzes.start', $quiz->id) }}" class="btn btn-sm btn-primary" style="font-size: 0.7rem; padding: 0.2rem 0.4rem;">
                                                                                                    بدء
                                                                                                </a>
                                                                                            </div>
                                                                                        @endforeach
                                                                                    </div>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @else
                                                                <p class="text-muted mb-0 small">لا توجد دروس في هذه الوحدة</p>
                                                            @endif
                                                            
                                                            <!-- اختبارات الوحدة -->
                                                            @if(isset($unit->unitQuizzes) && $unit->unitQuizzes->count() > 0)
                                                                <div class="mt-4">
                                                                    <h6 class="text-info mb-3 small" style="font-size: 0.8rem;">
                                                                        <i class="bi bi-clipboard-check me-2"></i>
                                                                        اختبارات الوحدة
                                                                    </h6>
                                                                    <div class="list-group">
                                                                        @foreach($unit->unitQuizzes->where('is_published', true) as $quiz)
                                                                            @php
                                                                                $userAttempt = $quiz->attempts->where('user_id', auth()->id())->sortByDesc('created_at')->first();
                                                                                $hasAttempt = $userAttempt !== null;
                                                                                $isInProgress = $userAttempt && $userAttempt->status === 'in_progress';
                                                                                $isCompleted = $userAttempt && in_array($userAttempt->status, ['completed', 'timed_out']);
                                                                            @endphp
                                                                            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center bg-info-transparent p-2">
                                                                                <div class="flex-grow-1">
                                                                                    <h6 class="mb-1 small" style="font-size: 0.75rem;">
                                                                                        <i class="bi bi-clipboard-check me-2 text-info"></i>
                                                                                        {{ $quiz->title }}
                                                                                    </h6>
                                                                                    @if($quiz->description)
                                                                                        <p class="text-muted mb-1 small" style="font-size: 0.7rem;">{{ \Illuminate\Support\Str::limit($quiz->description, 40) }}</p>
                                                                                    @endif
                                                                                    <div class="d-flex flex-wrap gap-1">
                                                                                        @if($quiz->duration_minutes)
                                                                                            <span class="badge bg-secondary small" style="font-size: 0.65rem;">
                                                                                                <i class="bi bi-clock me-1"></i>
                                                                                                {{ $quiz->duration_minutes }} د
                                                                                            </span>
                                                                                        @endif
                                                                                        <span class="badge bg-primary small" style="font-size: 0.65rem;">
                                                                                            <i class="bi bi-question-circle me-1"></i>
                                                                                            {{ $quiz->questions_count ?? $quiz->questions->count() }} سؤال
                                                                                        </span>
                                                                                        @if($hasAttempt)
                                                                                            @if($isInProgress)
                                                                                                <span class="badge bg-warning small" style="font-size: 0.65rem;">
                                                                                                    جاري
                                                                                                </span>
                                                                                            @elseif($isCompleted)
                                                                                                <span class="badge bg-{{ $userAttempt->passed ? 'success' : 'danger' }} small" style="font-size: 0.65rem;">
                                                                                                    {{ $userAttempt->passed ? 'نجح' : 'رسب' }}
                                                                                                </span>
                                                                                            @endif
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                                <div>
                                                                                    @if($isInProgress)
                                                                                        <a href="{{ route('student.quizzes.show', ['quiz' => $quiz->id, 'attempt' => $userAttempt->id]) }}" 
                                                                                           class="btn btn-sm btn-warning" style="font-size: 0.7rem; padding: 0.2rem 0.4rem;">
                                                                                            متابعة
                                                                                        </a>
                                                                                    @elseif($isCompleted)
                                                                                        <div class="d-flex flex-column gap-1">
                                                                                            <a href="{{ route('student.quizzes.result', ['quiz' => $quiz->id, 'attempt' => $userAttempt->id]) }}" 
                                                                                               class="btn btn-sm btn-info" style="font-size: 0.7rem; padding: 0.2rem 0.4rem;">
                                                                                                النتيجة
                                                                                            </a>
                                                                                            @if($quiz->max_attempts == 0 || $userAttempt->attempt_number < $quiz->max_attempts)
                                                                                                <a href="{{ route('student.quizzes.start', $quiz->id) }}" 
                                                                                                   class="btn btn-sm btn-primary" style="font-size: 0.7rem; padding: 0.2rem 0.4rem;">
                                                                                                    جديد
                                                                                                </a>
                                                                                            @endif
                                                                                        </div>
                                                                                    @else
                                                                                        <a href="{{ route('student.quizzes.start', $quiz->id) }}" 
                                                                                           class="btn btn-sm btn-primary" style="font-size: 0.7rem; padding: 0.2rem 0.4rem;">
                                                                                            بدء
                                                                                        </a>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <!-- Container closed -->
</div>
<!-- main-content closed -->
@stop

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const markAttendedBtn = document.getElementById('mark-attended-btn');
        const markCompletedBtn = document.getElementById('mark-completed-btn');
        const lessonId = {{ $lesson->id }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        function markLessonStatus(status) {
            const btn = status === 'attended' ? markAttendedBtn : markCompletedBtn;
            const originalText = btn.innerHTML;
            const originalClass = btn.className;
            
            // تعطيل الزر وإظهار التحميل
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>جاري الحفظ...';
            
            fetch(`{{ route('student.lessons.mark-status', ':id') }}`.replace(':id', lessonId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // تحديث الواجهة
                    location.reload(); // إعادة تحميل الصفحة لعرض الحالة الجديدة
                } else {
                    alert('حدث خطأ: ' + (data.message || 'فشل في حفظ الحالة'));
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    btn.className = originalClass;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ أثناء الاتصال بالخادم');
                btn.disabled = false;
                btn.innerHTML = originalText;
                btn.className = originalClass;
            });
        }
        
        if (markAttendedBtn) {
            markAttendedBtn.addEventListener('click', function() {
                markLessonStatus('attended');
            });
        }
        
        if (markCompletedBtn) {
            markCompletedBtn.addEventListener('click', function() {
                markLessonStatus('completed');
            });
        }
    });
</script>
@endpush
