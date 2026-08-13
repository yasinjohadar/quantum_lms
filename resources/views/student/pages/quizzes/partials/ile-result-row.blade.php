@php
    /** @var \App\InteractiveLearning\Models\LearningExperienceAttempt $attempt */
    $scorePct = round((float) ($attempt->percentage ?? 0), 1);
    // النجاح محسوب على الخادم مقابل passing_score للتجربة (بدل 50 مكتوبة هنا)
    $passed = (bool) $attempt->passed;
    $scoreClass = $passed ? 'passed' : 'failed';
@endphp

<div class="student-quiz-result-row" style="animation-delay: {{ ($loop->index ?? 0) * 0.04 }}s;">
    <div class="student-quiz-result-row__score student-quiz-result-row__score--{{ $scoreClass }}">
        <span class="student-quiz-result-row__score-value">{{ $scorePct }}%</span>
        <span class="student-quiz-result-row__score-sub">{{ $attempt->score }}/{{ $attempt->total }}</span>
    </div>

    <div class="student-quiz-result-row__main">
        <div class="student-quiz-result-row__head">
            <h3 class="student-quiz-result-row__title">
                {{ $attempt->experience->title ?? 'اختبار تفاعلي' }}
            </h3>
            <div class="student-quiz-result-row__badges">
                <span class="student-quiz-result-row__badge student-quiz-result-row__badge--info">تفاعلي</span>
                <span class="student-quiz-result-row__badge student-quiz-result-row__badge--success">مكتمل</span>
                @if($passed)
                    <span class="student-quiz-result-row__badge student-quiz-result-row__badge--passed">ناجح</span>
                @else
                    <span class="student-quiz-result-row__badge student-quiz-result-row__badge--failed">راسب</span>
                @endif
            </div>
        </div>

        <div class="student-quiz-result-row__meta">
            <span><i class="bi bi-book me-1"></i>{{ $attempt->experience->subject->name ?? 'عام' }}</span>
            @if($attempt->started_at)
                <span><i class="bi bi-calendar-event me-1"></i>{{ $attempt->started_at->format('Y-m-d H:i') }}</span>
            @endif
            @if($attempt->finished_at)
                <span><i class="bi bi-check2-circle me-1"></i>{{ $attempt->finished_at->format('Y-m-d H:i') }}</span>
            @endif
            @if($attempt->duration)
                <span><i class="bi bi-clock me-1"></i>{{ $attempt->duration }} ث</span>
            @endif
        </div>
    </div>

    <div class="student-quiz-result-row__action">
        @if($attempt->experience)
            <a href="{{ route('learning-experiences.show', $attempt->experience) }}"
               class="btn btn-success btn-sm">
                <i class="bi bi-joystick me-1"></i>إعادة اللعب
            </a>
        @endif
    </div>
</div>
