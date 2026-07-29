@extends('student.layouts.master')

@section('page-title')
    إحصائياتي
@stop

@push('styles')
    @include('student.partials.dashboard-widget-styles')
    @include('student.pages.lessons.partials.subject-content-breadcrumb-styles')
    @include('student.pages.gamification.partials.gamification-dashboard-styles')
    @include('student.pages.gamification.partials.gamification-stats-styles')
@endpush

@section('content')
@php
    $progress = $stats['level_progress'] ?? null;
    $pointTypeLabels = [
        'attendance' => 'حضور',
        'lesson_attended' => 'حضور',
        'lesson_completion' => 'إكمال درس',
        'lesson_completed' => 'إكمال درس',
        'quiz' => 'اختبار',
        'quiz_completed' => 'إكمال اختبار',
        'question' => 'سؤال',
        'question_answered' => 'إجابة سؤال',
        'achievement' => 'إنجاز',
        'challenge' => 'تحدي',
        'reward' => 'مكافأة',
        'manual' => 'يدوي',
    ];
    $pointTypeIcons = [
        'attendance' => 'bi-calendar-check',
        'lesson_attended' => 'bi-calendar-check',
        'lesson_completion' => 'bi-book',
        'lesson_completed' => 'bi-book',
        'quiz' => 'bi-clipboard-check',
        'quiz_completed' => 'bi-clipboard-check',
        'question' => 'bi-question-circle',
        'question_answered' => 'bi-question-circle',
        'achievement' => 'bi-trophy',
        'challenge' => 'bi-fire',
        'reward' => 'bi-gift',
        'manual' => 'bi-pencil-square',
    ];
    $breakdown = [
        ['key' => 'lesson_attended', 'label' => 'حضور', 'icon' => 'bi-calendar-check', 'class' => 'attendance'],
        ['key' => 'lesson_completed', 'label' => 'إكمال دروس', 'icon' => 'bi-book', 'class' => 'lessons'],
        ['key' => 'quiz_completed', 'label' => 'اختبارات', 'icon' => 'bi-clipboard-check', 'class' => 'quiz'],
        ['key' => 'question_answered', 'label' => 'أسئلة', 'icon' => 'bi-question-circle', 'class' => 'question'],
        ['key' => 'achievement', 'label' => 'إنجازات', 'icon' => 'bi-trophy', 'class' => 'achievement'],
    ];
@endphp

<div class="main-content app-content">
    <div class="container-fluid pt-3">
        <nav class="student-content-breadcrumb mb-3" aria-label="مسار التنقل">
            <ol class="student-content-breadcrumb__trail">
                <li class="student-content-breadcrumb__item">
                    <a href="{{ route('student.dashboard') }}" class="student-content-breadcrumb__link">
                        <i class="bi bi-house-door-fill"></i>
                        <span>الرئيسية</span>
                    </a>
                </li>
                <li class="student-content-breadcrumb__sep" aria-hidden="true"><i class="bi bi-chevron-left"></i></li>
                <li class="student-content-breadcrumb__item">
                    <a href="{{ route('student.gamification.dashboard') }}" class="student-content-breadcrumb__link">
                        <i class="bi bi-trophy"></i>
                        <span>لوحة التحفيز</span>
                    </a>
                </li>
                <li class="student-content-breadcrumb__sep" aria-hidden="true"><i class="bi bi-chevron-left"></i></li>
                <li class="student-content-breadcrumb__item" aria-current="page">
                    <span class="student-content-breadcrumb__current">
                        <i class="bi bi-graph-up"></i>
                        <span>إحصائياتي</span>
                    </span>
                </li>
            </ol>
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                <div>
                    <h1 class="student-content-breadcrumb__heading mb-0">
                        <i class="bi bi-graph-up me-2 text-warning"></i>إحصائياتي
                    </h1>
                    <p class="student-content-breadcrumb__meta mb-0">تفاصيل نقاطك ونشاطك خلال آخر 30 معاملة</p>
                </div>
                <a href="{{ route('student.gamification.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-right me-1"></i>لوحة التحفيز
                </a>
            </div>
        </nav>

        <div class="student-stats-total">
            <div>
                <div class="student-stats-total__label">إجمالي النقاط</div>
                <div class="student-stats-total__value">{{ number_format($stats['total_points']) }}</div>
            </div>
            <div class="student-stats-total__icon">
                <i class="bi bi-star-fill"></i>
            </div>
        </div>

        <div class="student-stats-breakdown">
            @foreach($breakdown as $item)
                <div class="student-stats-breakdown-card student-stats-breakdown-card--{{ $item['class'] }}">
                    <div class="student-stats-breakdown-card__icon">
                        <i class="bi {{ $item['icon'] }}"></i>
                    </div>
                    <div class="student-stats-breakdown-card__label">{{ $item['label'] }}</div>
                    <div class="student-stats-breakdown-card__value">
                        {{ number_format($stats['points_by_type'][$item['key']] ?? 0) }}
                    </div>
                </div>
            @endforeach
        </div>

        @if($progress)
            <div class="student-gamification-level">
                <div class="student-gamification-level__head">
                    <h5><i class="bi bi-graph-up me-2 text-primary"></i>تقدم المستوى</h5>
                </div>
                <div class="student-gamification-level__body">
                    <div class="student-gamification-level__ends">
                        <div>
                            <p class="student-gamification-level__end-title">
                                {{ $progress['current_level']?->name ?? 'لا يوجد' }}
                            </p>
                            <span class="student-gamification-level__end-sub">المستوى الحالي</span>
                        </div>
                        <div class="text-end">
                            @if($progress['next_level'])
                                <p class="student-gamification-level__end-title">{{ $progress['next_level']->name }}</p>
                                <span class="student-gamification-level__end-sub">المستوى التالي</span>
                            @else
                                <p class="student-gamification-level__end-title">أعلى مستوى</p>
                                <span class="student-gamification-level__end-sub">تهانينا!</span>
                            @endif
                        </div>
                    </div>
                    <div class="progress student-gamification-level__bar">
                        <div class="progress-bar" role="progressbar"
                             style="width: {{ min(100, $progress['progress_percentage']) }}%;"
                             aria-valuenow="{{ $progress['progress_percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                            {{ number_format($progress['progress_percentage'], 1) }}%
                        </div>
                    </div>
                    <div class="student-gamification-level__footer">
                        <span>
                            <i class="bi bi-star me-1"></i>
                            {{ number_format($progress['current_points']) }} / {{ number_format($progress['points_required']) }} نقطة
                        </span>
                        @if($progress['next_level'])
                            <span class="student-gamification-level__chip">
                                تحتاج {{ number_format(max(0, $progress['points_required'] - $progress['current_points'])) }} نقطة
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="student-gamification-panel">
            <div class="student-gamification-panel__head">
                <h5 class="student-gamification-panel__title">
                    <i class="bi bi-clock-history me-1 text-primary"></i>تاريخ النقاط
                </h5>
                <span class="student-gamification-level__chip">آخر 30 معاملة</span>
            </div>
            <div class="student-gamification-panel__body">
                @if($stats['points_history']->count() > 0)
                    <div class="student-stats-history">
                        @foreach($stats['points_history'] as $transaction)
                            @php
                                $typeLabel = $pointTypeLabels[$transaction->type] ?? $transaction->type_name;
                                $typeIcon = $pointTypeIcons[$transaction->type] ?? 'bi-star';
                                $isPositive = $transaction->points > 0;
                            @endphp
                            <div class="student-stats-history-row" style="animation-delay: {{ $loop->index * 0.03 }}s;">
                                <div class="student-stats-history-row__icon">
                                    <i class="bi {{ $typeIcon }}"></i>
                                </div>
                                <div class="student-stats-history-row__main">
                                    <p class="student-stats-history-row__type">{{ $typeLabel }}</p>
                                    <span class="student-stats-history-row__date">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        {{ $transaction->created_at->format('Y-m-d H:i') }}
                                    </span>
                                </div>
                                <span class="student-stats-history-row__points student-stats-history-row__points--{{ $isPositive ? 'positive' : 'negative' }}">
                                    {{ $isPositive ? '+' : '' }}{{ number_format($transaction->points) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="student-gamification-empty">
                        <div class="student-gamification-empty__icon"><i class="bi bi-inbox"></i></div>
                        <p class="text-muted mb-0">لا توجد معاملات نقاط بعد</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
