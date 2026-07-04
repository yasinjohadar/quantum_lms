@php
    $attemptCount = $quiz->user_attempts->count();
@endphp

<div class="student-quiz-card h-100">
    <div class="student-quiz-card__head">
        <div class="student-quiz-card__head-top">
            <div class="student-quiz-card__icon">
                <i class="bi bi-clipboard-check"></i>
            </div>
            @if($attemptCount > 0)
                <span class="student-quiz-card__status student-quiz-card__status--attempts">
                    {{ $attemptCount }} محاولة
                </span>
            @elseif($quiz->can_attempt)
                <span class="student-quiz-card__status student-quiz-card__status--available">متاح الآن</span>
            @else
                <span class="student-quiz-card__status student-quiz-card__status--locked">غير متاح</span>
            @endif
        </div>
        <h3 class="student-quiz-card__title">
            <a href="{{ route('student.quizzes.start', $quiz->id) }}">{{ $quiz->title }}</a>
        </h3>
    </div>

    <div class="student-quiz-card__body">
        <div class="student-quiz-card__meta">
            <span><i class="bi bi-book"></i>{{ $quiz->subject->name ?? 'عام' }}</span>
            @if($quiz->unit)
                <span><i class="bi bi-file-text"></i>{{ $quiz->unit->title }}</span>
            @endif
        </div>

        @if($quiz->description)
            <p class="student-quiz-card__desc">{{ Str::limit($quiz->description, 90) }}</p>
        @endif

        <div class="student-quiz-card__chips">
            @if($quiz->hasTimeLimit())
                <span class="student-quiz-card__chip student-quiz-card__chip--time">
                    <i class="bi bi-clock"></i>{{ $quiz->formatted_duration }}
                </span>
            @endif
            @if($quiz->total_points)
                <span class="student-quiz-card__chip student-quiz-card__chip--points">
                    <i class="bi bi-star-fill"></i>{{ $quiz->total_points }} نقطة
                </span>
            @endif
            @if($quiz->max_attempts)
                <span class="student-quiz-card__chip student-quiz-card__chip--attempts">
                    <i class="bi bi-repeat"></i>{{ $quiz->max_attempts }} محاولة كحد أقصى
                </span>
            @endif
        </div>

        @if($quiz->available_from || $quiz->available_to)
            <div class="student-quiz-card__dates">
                @if($quiz->available_from)
                    <div><i class="bi bi-calendar-check me-1"></i>متاح من: {{ $quiz->available_from->format('Y-m-d H:i') }}</div>
                @endif
                @if($quiz->available_to)
                    <div><i class="bi bi-calendar-x me-1"></i>ينتهي: {{ $quiz->available_to->format('Y-m-d H:i') }}</div>
                @endif
            </div>
        @endif

        @if($quiz->last_attempt)
            <div class="student-quiz-card__last">
                <i class="bi bi-info-circle me-1"></i>
                آخر محاولة: {{ $quiz->last_attempt->started_at->diffForHumans() }}
                @if($quiz->last_attempt->status === 'completed')
                    — النتيجة: {{ $quiz->last_attempt->score ?? 0 }}/{{ $quiz->total_points }}
                @endif
            </div>
        @endif

        <div class="student-quiz-card__footer">
            @if($quiz->can_attempt)
                <a href="{{ route('student.quizzes.start', $quiz->id) }}" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-play-circle me-1"></i>بدء الاختبار
                </a>
            @else
                <button class="btn btn-secondary flex-grow-1" disabled>
                    <i class="bi bi-lock me-1"></i>غير متاح
                </button>
            @endif
            @if($quiz->last_attempt && $quiz->last_attempt->status === 'completed')
                <a href="{{ route('student.quizzes.result', ['quiz' => $quiz->id, 'attempt' => $quiz->last_attempt->id]) }}" class="btn btn-outline-info">
                    <i class="bi bi-eye"></i>
                </a>
            @endif
        </div>
    </div>
</div>
