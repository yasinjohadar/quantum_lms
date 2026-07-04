@php
    $subject = $subject ?? null;
    $progress = $progress ?? [];
    $detailsUrl = $detailsUrl ?? ($subject ? route('student.progress.subject', $subject->id) : '#');
    $overallPercentage = (float) ($progress['overall_percentage'] ?? 0);
    $percentLabel = rtrim(rtrim(number_format($overallPercentage, 1), '0'), '.');
@endphp

@if($subject)
    <div class="student-progress-card h-100">
        <div class="student-progress-card__header">
            <div class="student-progress-card__title-wrap">
                <h5 class="student-progress-card__title">{{ $subject->name }}</h5>
                @if($subject->schoolClass)
                    <p class="student-progress-card__meta mb-0">
                        {{ $subject->schoolClass->name }}
                        @if($subject->schoolClass->stage)
                            — {{ $subject->schoolClass->stage->name }}
                        @endif
                    </p>
                @endif
            </div>
            <span class="student-progress-card__badge">{{ $percentLabel }}%</span>
        </div>

        <div class="student-progress-card__body">
            <div class="student-progress-card__bar-wrap">
                <div class="progress student-progress-card__bar">
                    <div class="progress-bar"
                         role="progressbar"
                         style="width: {{ min(100, $overallPercentage) }}%;"
                         aria-valuenow="{{ $overallPercentage }}"
                         aria-valuemin="0"
                         aria-valuemax="100"></div>
                </div>
            </div>

            <div class="student-progress-card__stats">
                <div class="student-progress-card__stat student-progress-card__stat--lessons">
                    <span class="student-progress-card__stat-value">{{ $progress['lessons_completed'] ?? 0 }}</span>
                    <span class="student-progress-card__stat-label">دروس</span>
                    <span class="student-progress-card__stat-total">من {{ $progress['lessons_total'] ?? 0 }}</span>
                </div>
                <div class="student-progress-card__stat student-progress-card__stat--quizzes">
                    <span class="student-progress-card__stat-value">{{ $progress['quizzes_completed'] ?? 0 }}</span>
                    <span class="student-progress-card__stat-label">اختبارات</span>
                    <span class="student-progress-card__stat-total">من {{ $progress['quizzes_total'] ?? 0 }}</span>
                </div>
                <div class="student-progress-card__stat student-progress-card__stat--questions">
                    <span class="student-progress-card__stat-value">{{ $progress['questions_completed'] ?? 0 }}</span>
                    <span class="student-progress-card__stat-label">أسئلة</span>
                    <span class="student-progress-card__stat-total">من {{ $progress['questions_total'] ?? 0 }}</span>
                </div>
            </div>

            <a href="{{ $detailsUrl }}" class="btn btn-primary btn-sm w-100 student-progress-card__btn">
                <i class="bi bi-eye me-1"></i>
                عرض التفاصيل
            </a>
        </div>
    </div>
@endif
