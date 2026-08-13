@extends('student.layouts.master')

@section('page-title')
    الملف الشخصي
@stop

@push('styles')
    @include('student.partials.dashboard-widget-styles')
    @include('student.pages.lessons.partials.subject-content-breadcrumb-styles')
    @include('student.pages.profile.partials.profile-page-styles')
@endpush

@section('content')
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
                <li class="student-content-breadcrumb__item" aria-current="page">
                    <span class="student-content-breadcrumb__current">
                        <i class="bi bi-person-fill"></i>
                        <span>الملف الشخصي</span>
                    </span>
                </li>
            </ol>
            <h1 class="student-content-breadcrumb__heading">
                <i class="bi bi-person-circle me-2 text-warning"></i>الملف الشخصي
            </h1>
            <p class="student-content-breadcrumb__meta mb-0">معلوماتك، موادك، ونشاطك الدراسي</p>
        </nav>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="row g-3">
            <div class="col-xl-4 col-lg-5">
                <div class="student-profile-hero">
                    <div class="student-profile-hero__banner" aria-hidden="true"></div>
                    <div class="student-profile-hero__body">
                        <div class="student-profile-hero__avatar-wrap">
                            <div class="student-profile-hero__avatar">
                                @if($user->photo)
                                    <img src="{{ media_public_url($user->photo) }}" alt="{{ $user->name }}">
                                @else
                                    {{ mb_substr($user->name, 0, 1) }}
                                @endif
                            </div>
                        </div>
                        <h2 class="student-profile-hero__name">{{ $user->name }}</h2>
                        <div class="student-profile-hero__contacts">
                            @if($user->email)
                                <span class="student-profile-hero__contact">
                                    <i class="bi bi-envelope-fill"></i>{{ $user->email }}
                                </span>
                            @endif
                            @if($user->phone)
                                <span class="student-profile-hero__contact">
                                    <i class="bi bi-telephone-fill"></i>{{ $user->phone }}
                                </span>
                            @endif
                        </div>
                        <button type="button" class="btn btn-primary btn-sm w-100" disabled>
                            <i class="bi bi-pencil-square me-1"></i>تعديل الملف الشخصي
                        </button>
                    </div>
                </div>

                <div class="student-profile-panel">
                    <div class="student-profile-panel__head">
                        <h3 class="student-profile-panel__title">
                            <i class="bi bi-bar-chart me-1 text-primary"></i>الإحصائيات
                        </h3>
                    </div>
                    <div class="student-profile-panel__body">
                        <div class="student-profile-stats">
                            <div class="student-profile-stat student-profile-stat--subjects">
                                <span class="student-profile-stat__value">{{ $generalStats['total_subjects'] }}</span>
                                <span class="student-profile-stat__label">المواد</span>
                            </div>
                            <div class="student-profile-stat student-profile-stat--enrollments">
                                <span class="student-profile-stat__value">{{ $generalStats['active_enrollments'] }}</span>
                                <span class="student-profile-stat__label">انضمامات نشطة</span>
                            </div>
                            <div class="student-profile-stat student-profile-stat--attempts">
                                <span class="student-profile-stat__value">{{ $quizStats['total_attempts'] }}</span>
                                <span class="student-profile-stat__label">محاولات الاختبارات</span>
                            </div>
                            <div class="student-profile-stat student-profile-stat--passed">
                                <span class="student-profile-stat__value">{{ $quizStats['passed_attempts'] }}</span>
                                <span class="student-profile-stat__label">اختبارات ناجحة</span>
                            </div>
                            @if($quizStats['average_score'] > 0)
                                <div class="student-profile-stat student-profile-stat--average" style="grid-column: span 2;">
                                    <span class="student-profile-stat__value">{{ number_format($quizStats['average_score'], 1) }}%</span>
                                    <span class="student-profile-stat__label">متوسط النقاط</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-8 col-lg-7">
                <div class="student-profile-panel">
                    <div class="student-profile-panel__head">
                        <h3 class="student-profile-panel__title">
                            <i class="bi bi-book me-1 text-primary"></i>المواد المسجلة
                        </h3>
                        @if($user->subjects->count() > 0)
                            <span class="student-profile-panel__count">{{ $user->subjects->count() }}</span>
                        @endif
                    </div>
                    <div class="student-profile-panel__body">
                        @if($user->subjects->count() > 0)
                            @foreach($user->subjects as $subject)
                                @php
                                    $enrollment = $user->enrollments->where('subject_id', $subject->id)->first();
                                    $status = $enrollment?->status;
                                    $statusLabel = match($status) {
                                        'active' => 'نشط',
                                        'suspended' => 'معلق',
                                        'completed' => 'مكتمل',
                                        default => 'غير محدد',
                                    };
                                    $statusClass = match($status) {
                                        'active' => 'active',
                                        'suspended' => 'suspended',
                                        default => 'other',
                                    };
                                @endphp
                                <a href="{{ route('student.subjects.show', $subject->id) }}"
                                   class="student-profile-subject-row"
                                   style="animation-delay: {{ $loop->index * 0.04 }}s;">
                                    <div class="student-profile-subject-row__icon">
                                        <i class="bi bi-journal-bookmark"></i>
                                    </div>
                                    <div class="student-profile-subject-row__main">
                                        <h4 class="student-profile-subject-row__title">{{ $subject->name }}</h4>
                                        <div class="student-profile-subject-row__meta">
                                            {{ $subject->schoolClass->name ?? '-' }}
                                            · {{ $subject->schoolClass->stage->name ?? '-' }}
                                        </div>
                                    </div>
                                    <span class="student-profile-subject-row__status student-profile-subject-row__status--{{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </a>
                            @endforeach
                        @else
                            <div class="student-profile-empty">
                                <div class="student-profile-empty__icon"><i class="bi bi-book"></i></div>
                                <p class="text-muted mb-0">لا توجد مواد مسجلة حالياً</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="student-profile-panel">
                    <div class="student-profile-panel__head">
                        <h3 class="student-profile-panel__title">
                            <i class="bi bi-clipboard-check me-1 text-info"></i>آخر محاولات الاختبارات
                        </h3>
                    </div>
                    <div class="student-profile-panel__body">
                        @if($quizStats['recent_attempts']->count() > 0)
                            @foreach($quizStats['recent_attempts'] as $attempt)
                                @php
                                    $scoreClass = $attempt->percentage === null
                                        ? 'pending'
                                        : ($attempt->passed ? 'passed' : 'failed');
                                    $statusLabel = match($attempt->status) {
                                        'completed' => 'مكتمل',
                                        'in_progress' => 'قيد التنفيذ',
                                        'graded' => 'مصحح',
                                        'timeout', 'timed_out' => 'انتهى الوقت',
                                        default => $attempt->status,
                                    };
                                @endphp
                                <div class="student-profile-attempt-row" style="animation-delay: {{ $loop->index * 0.04 }}s;">
                                    <div class="student-profile-attempt-row__score student-profile-attempt-row__score--{{ $scoreClass }}">
                                        @if($attempt->percentage !== null)
                                            {{ number_format($attempt->percentage, 0) }}%
                                        @else
                                            —
                                        @endif
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <h4 class="student-profile-attempt-row__title">{{ $attempt->quiz->title ?? '-' }}</h4>
                                        <div class="student-profile-attempt-row__meta">
                                            {{ $attempt->quiz->subject->name ?? '-' }}
                                            · {{ $statusLabel }}
                                            · {{ $attempt->started_at->format('Y-m-d H:i') }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        {{-- محاولات الاختبارات التفاعلية (تُحفظ في جدول منفصل) --}}
                        @foreach($quizStats['recent_interactive_attempts'] ?? [] as $attempt)
                            <div class="student-profile-attempt-row" style="animation-delay: {{ $loop->index * 0.04 }}s;">
                                <div class="student-profile-attempt-row__score student-profile-attempt-row__score--{{ $attempt->passed ? 'passed' : 'failed' }}">
                                    {{ number_format($attempt->percentage, 0) }}%
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <h4 class="student-profile-attempt-row__title">
                                        <i class="bi bi-joystick text-primary me-1"></i>{{ $attempt->experience->title ?? '-' }}
                                    </h4>
                                    <div class="student-profile-attempt-row__meta">
                                        {{ $attempt->experience->subject->name ?? '-' }}
                                        · تفاعلي
                                        · {{ optional($attempt->finished_at ?? $attempt->created_at)->format('Y-m-d H:i') }}
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @if($quizStats['recent_attempts']->count() === 0 && ($quizStats['recent_interactive_attempts'] ?? collect())->count() === 0)
                            <div class="student-profile-empty">
                                <div class="student-profile-empty__icon"><i class="bi bi-clipboard-x"></i></div>
                                <p class="text-muted mb-0">لا توجد محاولات اختبارات حتى الآن</p>
                            </div>
                        @endif
                    </div>
                </div>

                @if($user->loginLogs->count() > 0)
                    <div class="student-profile-panel mb-0">
                        <div class="student-profile-panel__head">
                            <h3 class="student-profile-panel__title">
                                <i class="bi bi-shield-lock me-1 text-success"></i>جلسات الدخول الأخيرة
                            </h3>
                        </div>
                        <div class="student-profile-panel__body">
                            @foreach($user->loginLogs->take(5) as $log)
                                <div class="student-profile-login-row" style="animation-delay: {{ $loop->index * 0.03 }}s;">
                                    <span class="student-profile-login-row__status student-profile-login-row__status--{{ $log->is_successful ? 'ok' : 'fail' }}">
                                        {{ $log->is_successful ? 'ناجح' : 'فاشل' }}
                                    </span>
                                    <span class="student-profile-login-row__item">
                                        <i class="bi bi-globe2"></i>
                                        <strong>{{ $log->ip_address }}</strong>
                                    </span>
                                    <span class="student-profile-login-row__item">
                                        <i class="bi bi-phone"></i>
                                        {{ $log->device_type ?? '-' }} · {{ $log->platform ?? '-' }}
                                    </span>
                                    <span class="student-profile-login-row__item">
                                        <i class="bi bi-browser-chrome"></i>
                                        {{ $log->browser ?? '-' }}
                                    </span>
                                    <span class="student-profile-login-row__item">
                                        <i class="bi bi-clock"></i>
                                        {{ $log->login_at->format('Y-m-d H:i') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
