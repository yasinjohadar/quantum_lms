@extends('student.layouts.master')

@section('page-title')
    {{ $subject->name }}
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">{{ $subject->name }}</h4>
                <p class="mb-0 text-muted">
                    @if($subject->schoolClass)
                        {{ $subject->schoolClass->name }}
                        @if($subject->schoolClass->stage)
                            - {{ $subject->schoolClass->stage->name }}
                        @endif
                    @endif
                </p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.subjects') }}">المواد الدراسية</a></li>
                    <li class="breadcrumb-item active">{{ $subject->name }}</li>
                </ol>
            </nav>
        </div>
        <!-- End Page Header -->

        <!-- إحصائيات المادة -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ $stats['total_sections'] }}</h3>
                        <p class="mb-0">أقسام</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ $stats['total_units'] }}</h3>
                        <p class="mb-0">وحدات</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ $stats['total_lessons'] }}</h3>
                        <p class="mb-0">دروس</p>
                    </div>
                </div>
            </div>
        </div>

        @if($subject->description)
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="mb-2">وصف المادة</h6>
                    <p class="mb-0">{{ $subject->description }}</p>
                </div>
            </div>
        @endif

        <!-- الأقسام والوحدات والدروس -->
        @if($sections->count() > 0)
            <div class="row">
                <div class="col-12">
                    @foreach($sections as $section)
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="bi bi-folder me-2"></i>
                                    {{ $section->title }}
                                </h5>
                                @if($section->description)
                                    <p class="text-muted mb-0 mt-2">{{ $section->description }}</p>
                                @endif
                            </div>
                            <div class="card-body">
                                @if($section->units->count() > 0)
                                    <div class="accordion" id="section-{{ $section->id }}">
                                        @foreach($section->units as $unitIndex => $unit)
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="unit-heading-{{ $unit->id }}">
                                                    <button class="accordion-button {{ $unitIndex > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#unit-{{ $unit->id }}" aria-expanded="{{ $unitIndex === 0 ? 'true' : 'false' }}">
                                                        <i class="bi bi-file-text me-2"></i>
                                                        {{ $unit->title }}
                                                        <span class="badge bg-secondary ms-2">{{ $unit->lessons->count() }} درس</span>
                                                    </button>
                                                </h2>
                                                <div id="unit-{{ $unit->id }}" class="accordion-collapse collapse {{ $unitIndex === 0 ? 'show' : '' }}" data-bs-parent="#section-{{ $section->id }}">
                                                    <div class="accordion-body">
                                                        @if($unit->description)
                                                            <p class="text-muted mb-3">{{ $unit->description }}</p>
                                                        @endif
                                                        
                                                        @if($unit->lessons->count() > 0)
                                                            <div class="list-group">
                                                                @foreach($unit->lessons as $lesson)
                                                                    <a href="{{ route('student.lessons.show', $lesson->id) }}" class="list-group-item list-group-item-action">
                                                                        <div class="d-flex justify-content-between align-items-center">
                                                                            <div>
                                                                                <h6 class="mb-1">
                                                                                    <i class="bi bi-play-circle me-2 text-primary"></i>
                                                                                    {{ $lesson->title }}
                                                                                </h6>
                                                                                @if($lesson->description)
                                                                                    <p class="text-muted mb-0 small">{{ \Illuminate\Support\Str::limit($lesson->description, 80) }}</p>
                                                                                @endif
                                                                                <div class="mt-2">
                                                                                    @if($lesson->duration)
                                                                                        <span class="badge bg-secondary me-2">
                                                                                            <i class="bi bi-clock me-1"></i>
                                                                                            {{ $lesson->formatted_duration }}
                                                                                        </span>
                                                                                    @endif
                                                                                    @if($lesson->is_free)
                                                                                        <span class="badge bg-success">مجاني</span>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                            <i class="bi bi-chevron-left"></i>
                                                                        </div>
                                                                    </a>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <p class="text-muted mb-0">لا توجد دروس في هذه الوحدة</p>
                                                        @endif
                                                        
                                                        <!-- اختبارات الوحدة -->
                                                        @if(isset($unit->quizzes) && $unit->quizzes->count() > 0)
                                                            <div class="mt-4">
                                                                <h6 class="text-info mb-3">
                                                                    <i class="bi bi-clipboard-check me-2"></i>
                                                                    اختبارات الوحدة
                                                                </h6>
                                                                <div class="list-group">
                                                                    @foreach($unit->quizzes->where('is_published', true) as $quiz)
                                                                        @php
                                                                            $userAttempt = $quiz->attempts->where('user_id', auth()->id())->sortByDesc('created_at')->first();
                                                                        @endphp
                                                                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center bg-info-transparent">
                                                                            <div>
                                                                                <h6 class="mb-1">
                                                                                    <i class="bi bi-clipboard-check me-2 text-info"></i>
                                                                                    {{ $quiz->title }}
                                                                                </h6>
                                                                                @if($quiz->description)
                                                                                    <p class="text-muted mb-1 small">{{ \Illuminate\Support\Str::limit($quiz->description, 60) }}</p>
                                                                                @endif
                                                                                <div class="d-flex flex-wrap gap-2">
                                                                                    @if($quiz->duration_minutes)
                                                                                        <span class="badge bg-secondary">
                                                                                            <i class="bi bi-clock me-1"></i>
                                                                                            {{ $quiz->duration_minutes }} دقيقة
                                                                                        </span>
                                                                                    @endif
                                                                                    <span class="badge bg-primary">
                                                                                        <i class="bi bi-question-circle me-1"></i>
                                                                                        {{ $quiz->questions_count ?? $quiz->questions->count() }} سؤال
                                                                                    </span>
                                                                                    @if($userAttempt)
                                                                                        @if($userAttempt->status === 'completed' || $userAttempt->status === 'graded')
                                                                                            <span class="badge bg-success">
                                                                                                <i class="bi bi-check-circle me-1"></i>
                                                                                                تم الاختبار ({{ number_format(($userAttempt->score / max($userAttempt->max_score, 1)) * 100, 0) }}%)
                                                                                            </span>
                                                                                        @elseif($userAttempt->status === 'in_progress')
                                                                                            <span class="badge bg-warning">
                                                                                                <i class="bi bi-hourglass-split me-1"></i>
                                                                                                قيد الاختبار
                                                                                            </span>
                                                                                        @endif
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                            <div>
                                                                                @if($userAttempt && $userAttempt->status === 'in_progress')
                                                                                    <a href="{{ route('student.quizzes.show', ['quiz' => $quiz->id, 'attempt' => $userAttempt->id]) }}" class="btn btn-sm btn-warning">
                                                                                        <i class="bi bi-play-fill me-1"></i>
                                                                                        متابعة
                                                                                    </a>
                                                                                @elseif($userAttempt && ($userAttempt->status === 'completed' || $userAttempt->status === 'graded'))
                                                                                    <a href="{{ route('student.quizzes.result', ['quiz' => $quiz->id, 'attempt' => $userAttempt->id]) }}" class="btn btn-sm btn-info">
                                                                                        <i class="bi bi-bar-chart me-1"></i>
                                                                                        النتيجة
                                                                                    </a>
                                                                                    @if($quiz->max_attempts == 0 || $userAttempt->attempt_number < $quiz->max_attempts)
                                                                                        <a href="{{ route('student.quizzes.start', $quiz->id) }}" class="btn btn-sm btn-outline-primary">
                                                                                            <i class="bi bi-arrow-repeat me-1"></i>
                                                                                            إعادة
                                                                                        </a>
                                                                                    @endif
                                                                                @else
                                                                                    <a href="{{ route('student.quizzes.start', $quiz->id) }}" class="btn btn-sm btn-primary">
                                                                                        <i class="bi bi-play-fill me-1"></i>
                                                                                        بدء الاختبار
                                                                                    </a>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                        
                                                        <!-- أسئلة الوحدة -->
                                                        @if(isset($unit->questions) && $unit->questions->count() > 0)
                                                            <div class="mt-4">
                                                                <h6 class="text-primary mb-3">
                                                                    <i class="bi bi-question-circle me-2"></i>
                                                                    أسئلة الوحدة ({{ $unit->questions->count() }})
                                                                </h6>
                                                                <div class="list-group">
                                                                    @foreach($unit->questions as $question)
                                                                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center bg-primary-transparent">
                                                                            <div class="flex-grow-1">
                                                                                <h6 class="mb-1">
                                                                                    <i class="bi bi-question-circle me-2 text-primary"></i>
                                                                                    {{ $question->title ?? \Illuminate\Support\Str::limit($question->content, 60) }}
                                                                                </h6>
                                                                                <div class="d-flex flex-wrap gap-2 mt-2">
                                                                                    <span class="badge bg-secondary">
                                                                                        {{ $question->type_name ?? $question->type }}
                                                                                    </span>
                                                                                    @if($question->difficulty)
                                                                                        <span class="badge bg-{{ $question->difficulty === 'easy' ? 'success' : ($question->difficulty === 'medium' ? 'warning' : 'danger') }}">
                                                                                            {{ $question->difficulty === 'easy' ? 'سهل' : ($question->difficulty === 'medium' ? 'متوسط' : 'صعب') }}
                                                                                        </span>
                                                                                    @endif
                                                                                    <span class="badge bg-info">
                                                                                        <i class="bi bi-star me-1"></i>
                                                                                        {{ $question->default_points ?? 10 }} نقطة
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                            <div>
                                                                                <a href="{{ route('student.questions.start.specific', $question->id) }}" class="btn btn-sm btn-primary">
                                                                                    <i class="bi bi-play-fill me-1"></i>
                                                                                    بدء السؤال
                                                                                </a>
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
                                @else
                                    <p class="text-muted mb-0">لا توجد وحدات في هذا القسم</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-folder-x fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="mb-2">لا يوجد محتوى</h5>
                    <p class="text-muted">لم يتم إضافة محتوى لهذه المادة بعد</p>
                </div>
            </div>
        @endif

        <!-- قسم التقييمات -->
        @php
            $userReview = \App\Models\Review::where('user_id', Auth::id())
                ->where('reviewable_type', 'App\Models\Subject')
                ->where('reviewable_id', $subject->id)
                ->whereNull('deleted_at')
                ->first();
            $approvedReviews = \App\Models\Review::where('reviewable_type', 'App\Models\Subject')
                ->where('reviewable_id', $subject->id)
                ->where('status', 'approved')
                ->whereNull('deleted_at')
                ->with('user')
                ->latest()
                ->take(10)
                ->get();
            $averageRating = \App\Models\Review::where('reviewable_type', 'App\Models\Subject')
                ->where('reviewable_id', $subject->id)
                ->where('status', 'approved')
                ->whereNull('deleted_at')
                ->avg('rating');
            $totalReviews = $approvedReviews->count();
        @endphp
        
        <div class="card mb-4 mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-star me-2 text-warning"></i>
                    التقييمات
                </h5>
                @if(!$userReview)
                    <a href="{{ route('student.reviews.create', ['type' => 'subject', 'id' => $subject->id]) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>
                        أضف تقييمك
                    </a>
                @else
                    <a href="{{ route('student.reviews.edit', $userReview) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i>
                        تعديل تقييمك
                    </a>
                @endif
            </div>
            <div class="card-body">
                @if($totalReviews > 0 || $userReview)
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="text-center">
                                <h2 class="mb-0 text-warning">{{ number_format($averageRating, 1) }}</h2>
                                <div class="d-flex justify-content-center gap-1 mb-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= round($averageRating) ? '-fill' : '' }} text-warning"></i>
                                    @endfor
                                </div>
                                <p class="text-muted mb-0">بناءً على {{ $totalReviews }} تقييم</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if($userReview)
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    لديك تقييم {{ $userReview->status === 'approved' ? 'معتمد' : 'قيد المراجعة' }}
                                    @if($userReview->status === 'approved')
                                        <div class="mt-2">
                                            <div class="d-flex gap-1">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="bi bi-star{{ $i <= $userReview->rating ? '-fill' : '' }} text-warning"></i>
                                                @endfor
                                            </div>
                                            @if($userReview->title)
                                                <p class="mb-0 mt-2"><strong>{{ $userReview->title }}</strong></p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if($approvedReviews->count() > 0)
                    <div class="reviews-list">
                        <h6 class="mb-3">آخر التقييمات</h6>
                        @foreach($approvedReviews as $review)
                            @include('student.components.reviews.review-card', ['review' => $review])
                        @endforeach
                    </div>
                @elseif(!$userReview)
                    <div class="text-center py-4">
                        <i class="bi bi-star fs-1 text-muted mb-3 d-block"></i>
                        <p class="text-muted mb-3">لا توجد تقييمات بعد</p>
                        <a href="{{ route('student.reviews.create', ['type' => 'subject', 'id' => $subject->id]) }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>
                            كن أول من يقيم
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <!-- Container closed -->
</div>
<!-- main-content closed -->
@stop

