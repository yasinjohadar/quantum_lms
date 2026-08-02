@php
    $attemptCount = $experience->user_attempts_count ?? ($experience->user_attempts?->count() ?? 0);
    $lastAttempt = $experience->last_attempt ?? null;
    $qCount = $experience->questionsCount();
@endphp

<div class="student-quiz-card h-100">
    <div class="student-quiz-card__head">
        <div class="student-quiz-card__head-top">
            <div class="student-quiz-card__icon" style="background: rgba(5,150,105,.14); color: #059669;">
                <i class="bi bi-joystick"></i>
            </div>
            @if($attemptCount > 0)
                <span class="student-quiz-card__status student-quiz-card__status--attempts">
                    {{ $attemptCount }} محاولة
                </span>
            @else
                <span class="student-quiz-card__status student-quiz-card__status--available">تفاعلي</span>
            @endif
        </div>
        <h3 class="student-quiz-card__title">
            <a href="{{ route('learning-experiences.show', $experience) }}">{{ $experience->title }}</a>
        </h3>
    </div>

    <div class="student-quiz-card__body">
        <div class="student-quiz-card__meta">
            <span><i class="bi bi-book"></i>{{ $experience->subject->name ?? 'عام' }}</span>
            @if($experience->unit)
                <span><i class="bi bi-file-text"></i>{{ $experience->unit->title }}</span>
            @endif
        </div>

        @if($experience->description)
            <p class="student-quiz-card__desc">{{ Str::limit($experience->description, 90) }}</p>
        @endif

        <div class="student-quiz-card__chips">
            <span class="student-quiz-card__chip student-quiz-card__chip--points">
                <i class="bi bi-joystick"></i>اختبار تفاعلي
            </span>
            <span class="student-quiz-card__chip student-quiz-card__chip--attempts">
                <i class="bi bi-question-circle"></i>{{ $qCount }} سؤال
            </span>
        </div>

        @if($lastAttempt)
            <div class="student-quiz-card__last">
                <i class="bi bi-info-circle me-1"></i>
                آخر محاولة: {{ optional($lastAttempt->finished_at ?? $lastAttempt->created_at)->diffForHumans() }}
                — النتيجة: {{ number_format((float) $lastAttempt->percentage, 1) }}%
            </div>
        @endif

        <div class="student-quiz-card__footer">
            <a href="{{ route('learning-experiences.show', $experience) }}" class="btn btn-success flex-grow-1">
                <i class="bi bi-play-circle me-1"></i>{{ $attemptCount > 0 ? 'إعادة اللعب' : 'بدء الاختبار' }}
            </a>
        </div>
    </div>
</div>
