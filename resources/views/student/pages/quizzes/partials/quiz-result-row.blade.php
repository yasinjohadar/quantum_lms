@php
    $scorePct = ($attempt->max_score ?? 0) > 0 && $attempt->score !== null
        ? round(($attempt->score / $attempt->max_score) * 100, 1)
        : null;
    $scoreClass = $attempt->score === null
        ? 'pending'
        : ($attempt->passed ? 'passed' : 'failed');
@endphp

<div class="student-quiz-result-row" style="animation-delay: {{ ($loop->index ?? 0) * 0.04 }}s;">
    <div class="student-quiz-result-row__score student-quiz-result-row__score--{{ $scoreClass }}">
        @if($scorePct !== null)
            <span class="student-quiz-result-row__score-value">{{ $scorePct }}%</span>
            <span class="student-quiz-result-row__score-sub">{{ $attempt->score }}/{{ $attempt->max_score }}</span>
        @else
            <span class="student-quiz-result-row__score-value">—</span>
            <span class="student-quiz-result-row__score-sub">قيد التصحيح</span>
        @endif
    </div>

    <div class="student-quiz-result-row__main">
        <div class="student-quiz-result-row__head">
            <h3 class="student-quiz-result-row__title">
                {{ $attempt->quiz->title }}
                @if($attempt->attempt_number > 1)
                    <span class="student-quiz-result-row__attempt">محاولة {{ $attempt->attempt_number }}</span>
                @endif
            </h3>
            <div class="student-quiz-result-row__badges">
                @if($attempt->status === 'completed')
                    <span class="student-quiz-result-row__badge student-quiz-result-row__badge--info">مكتمل</span>
                @elseif($attempt->status === 'graded')
                    <span class="student-quiz-result-row__badge student-quiz-result-row__badge--success">مصحح</span>
                @elseif($attempt->status === 'timeout')
                    <span class="student-quiz-result-row__badge student-quiz-result-row__badge--warning">انتهى الوقت</span>
                @endif
                @if($attempt->passed !== null)
                    @if($attempt->passed)
                        <span class="student-quiz-result-row__badge student-quiz-result-row__badge--passed">ناجح</span>
                    @else
                        <span class="student-quiz-result-row__badge student-quiz-result-row__badge--failed">راسب</span>
                    @endif
                @endif
            </div>
        </div>

        <div class="student-quiz-result-row__meta">
            <span><i class="bi bi-book me-1"></i>{{ $attempt->quiz->subject->name ?? 'عام' }}</span>
            <span><i class="bi bi-calendar-event me-1"></i>{{ $attempt->started_at->format('Y-m-d H:i') }}</span>
            @if($attempt->finished_at)
                <span><i class="bi bi-check2-circle me-1"></i>{{ $attempt->finished_at->format('Y-m-d H:i') }}</span>
            @endif
        </div>
    </div>

    <div class="student-quiz-result-row__action">
        <a href="{{ route('student.quizzes.result', ['quiz' => $attempt->quiz_id, 'attempt' => $attempt->id]) }}"
           class="btn btn-primary btn-sm">
            <i class="bi bi-eye me-1"></i>التفاصيل
        </a>
    </div>
</div>
