@extends('student.layouts.master')

@section('page-title')
    الاختبارات المتاحة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">الاختبارات المتاحة</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">الاختبارات المتاحة</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Filters -->
        <div class="card custom-card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('student.quizzes.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">المادة</label>
                        <select name="subject_id" class="form-select" onchange="this.form.submit()">
                            <option value="">جميع المواد</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">جميع الحالات</option>
                            <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>متاح الآن</option>
                            <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>قادم</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>منتهي</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Quizzes List -->
        <div class="row">
            @forelse($quizzes as $quiz)
                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-2">
                                        <a href="{{ route('student.quizzes.start', $quiz->id) }}" class="text-dark">
                                            {{ $quiz->title }}
                                        </a>
                                    </h5>
                                    <p class="text-muted small mb-2">
                                        <i class="bi bi-book me-1"></i>
                                        {{ $quiz->subject->name ?? 'عام' }}
                                    </p>
                                    @if($quiz->unit)
                                        <p class="text-muted small mb-2">
                                            <i class="bi bi-file-text me-1"></i>
                                            {{ $quiz->unit->title }}
                                        </p>
                                    @endif
                                </div>
                                @if($quiz->user_attempts->count() > 0)
                                    <span class="badge bg-success-transparent">
                                        <i class="bi bi-check-circle me-1"></i>
                                        {{ $quiz->user_attempts->count() }} محاولة
                                    </span>
                                @endif
                            </div>

                            @if($quiz->description)
                                <p class="text-muted small mb-3">{{ Str::limit($quiz->description, 100) }}</p>
                            @endif

                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @if($quiz->duration_minutes)
                                    <span class="badge bg-info-transparent">
                                        <i class="bi bi-clock me-1"></i>
                                        {{ $quiz->duration_minutes }} دقيقة
                                    </span>
                                @endif
                                @if($quiz->max_attempts)
                                    <span class="badge bg-warning-transparent">
                                        <i class="bi bi-repeat me-1"></i>
                                        {{ $quiz->max_attempts }} محاولة كحد أقصى
                                    </span>
                                @endif
                                @if($quiz->total_points)
                                    <span class="badge bg-primary-transparent">
                                        <i class="bi bi-star me-1"></i>
                                        {{ $quiz->total_points }} نقطة
                                    </span>
                                @endif
                            </div>

                            @if($quiz->available_from || $quiz->available_to)
                                <div class="mb-3">
                                    @if($quiz->available_from)
                                        <small class="text-muted d-block">
                                            <i class="bi bi-calendar-check me-1"></i>
                                            متاح من: {{ $quiz->available_from->format('Y-m-d H:i') }}
                                        </small>
                                    @endif
                                    @if($quiz->available_to)
                                        <small class="text-muted d-block">
                                            <i class="bi bi-calendar-x me-1"></i>
                                            ينتهي في: {{ $quiz->available_to->format('Y-m-d H:i') }}
                                        </small>
                                    @endif
                                </div>
                            @endif

                            @if($quiz->last_attempt)
                                <div class="alert alert-info mb-3">
                                    <small>
                                        <i class="bi bi-info-circle me-1"></i>
                                        آخر محاولة: {{ $quiz->last_attempt->started_at->diffForHumans() }}
                                        @if($quiz->last_attempt->status === 'completed')
                                            - النتيجة: {{ $quiz->last_attempt->score ?? 0 }} / {{ $quiz->total_points }}
                                        @endif
                                    </small>
                                </div>
                            @endif

                            <div class="d-flex gap-2">
                                @if($quiz->can_attempt)
                                    <a href="{{ route('student.quizzes.start', $quiz->id) }}" class="btn btn-primary btn-sm flex-grow-1">
                                        <i class="bi bi-play-circle me-1"></i>
                                        بدء الاختبار
                                    </a>
                                @else
                                    <button class="btn btn-secondary btn-sm flex-grow-1" disabled>
                                        <i class="bi bi-lock me-1"></i>
                                        غير متاح
                                    </button>
                                @endif
                                @if($quiz->last_attempt && $quiz->last_attempt->status === 'completed')
                                    <a href="{{ route('student.quizzes.result', ['quiz' => $quiz->id, 'attempt' => $quiz->last_attempt->id]) }}" class="btn btn-info btn-sm">
                                        <i class="bi bi-eye me-1"></i>
                                        النتيجة
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card custom-card">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                            <h5 class="mb-2">لا توجد اختبارات متاحة</h5>
                            <p class="text-muted">لم يتم العثور على أي اختبارات متاحة حالياً</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($quizzes->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $quizzes->links() }}
            </div>
        @endif
    </div>
</div>
@stop

