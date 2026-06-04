@extends('student.layouts.master')

@section('page-title')
لوحة التحكم
@stop

@push('styles')
    @include('admin.pages.dashboard.partials.widget-styles')
    <style>
        .dashboard-panel {
            border-radius: 14px;
            border: 1px solid var(--default-border);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            transition: box-shadow 0.25s ease;
        }
        .dashboard-panel:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
        }
        [data-theme-mode="dark"] .dashboard-panel {
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.28);
        }
        .dashboard-panel .card-header {
            border-bottom: 1px solid var(--default-border);
            background: transparent;
        }
        .dashboard-panel .progress {
            border-radius: 6px;
        }
        .dashboard-badge-item {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            min-width: 88px;
            border-radius: 10px;
            border: 1px solid var(--default-border);
            background: var(--custom-card-bg, var(--default-background));
        }
        .dashboard-badge-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .dashboard-course-row:last-child {
            border-bottom: none !important;
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }
    </style>
@endpush

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div>
                    <h4 class="mb-0">مرحباً {{ Auth::user()->name }}، أهلاً بعودتك!</h4>
                    <p class="mb-0 text-muted">{{ now()->translatedFormat('l، d F Y') }} — أنت مسجل كطالب</p>
                </div>
            </div>

            @php
                $subjectsCollection = collect($topSubjects ?? []);
                $subjectsTotal = $subjectsCount ?? $subjectsCollection->count();
            @endphp

            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3 mb-xl-0">
                    <div class="dashboard-stat-card dashboard-stat-card--enrollments h-100">
                        <div class="dashboard-stat-card__body">
                            <div class="dashboard-stat-card__content">
                                <div class="dashboard-stat-card__label">إجمالي النقاط</div>
                                <div class="dashboard-stat-card__value">{{ number_format($totalPoints ?? 0) }}</div>
                                <p class="dashboard-stat-card__meta">
                                    @if($currentLevel ?? null)
                                        المستوى {{ $currentLevel->name }}
                                    @else
                                        استمر في التعلم لكسب المزيد
                                    @endif
                                </p>
                            </div>
                            <div class="dashboard-stat-card__icon">
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3 mb-xl-0">
                    <div class="dashboard-stat-card dashboard-stat-card--subjects h-100">
                        <div class="dashboard-stat-card__body">
                            <div class="dashboard-stat-card__content">
                                <div class="dashboard-stat-card__label">متوسط التقدم</div>
                                <div class="dashboard-stat-card__value">{{ number_format($overallAverage ?? 0, 1) }}%</div>
                                <p class="dashboard-stat-card__meta">في جميع موادك</p>
                            </div>
                            <div class="dashboard-stat-card__icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3 mb-xl-0">
                    <div class="dashboard-stat-card dashboard-stat-card--quizzes h-100">
                        <div class="dashboard-stat-card__body">
                            <div class="dashboard-stat-card__content">
                                <div class="dashboard-stat-card__label">الشارات</div>
                                <div class="dashboard-stat-card__value">{{ number_format($badgesCount ?? 0) }}</div>
                                <p class="dashboard-stat-card__meta">{{ $achievementsCount ?? 0 }} إنجاز مكتمل</p>
                            </div>
                            <div class="dashboard-stat-card__icon">
                                <i class="fas fa-award"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3 mb-xl-0">
                    <div class="dashboard-stat-card dashboard-stat-card--students h-100">
                        <div class="dashboard-stat-card__body">
                            <div class="dashboard-stat-card__content">
                                <div class="dashboard-stat-card__label">المواد النشطة</div>
                                <div class="dashboard-stat-card__value">{{ number_format($subjectsTotal) }}</div>
                                <p class="dashboard-stat-card__meta">كورسات قيد المتابعة</p>
                            </div>
                            <div class="dashboard-stat-card__icon">
                                <i class="fas fa-book-open"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card dashboard-panel">
                        <div class="card-header pb-2">
                            <h4 class="card-title mb-0">
                                <i class="fe fe-zap me-2"></i> اختصارات سريعة
                            </h4>
                            <p class="fs-12 text-muted mb-0">الوصول السريع لأهم أقسام منصتك</p>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <x-dashboard-shortcut
                                    href="{{ route('student.classes') }}"
                                    icon="fas fa-layer-group"
                                    title="صفوفي"
                                    subtitle="الصفوف والمواد"
                                    accent="primary"
                                    col-class="col-xl-2 col-lg-4 col-md-4 col-sm-6"
                                />
                                <x-dashboard-shortcut
                                    href="{{ route('student.quizzes.index') }}"
                                    icon="fas fa-clipboard-check"
                                    title="الاختبارات"
                                    subtitle="نتائج وتقارير الاختبارات"
                                    accent="success"
                                    col-class="col-xl-2 col-lg-4 col-md-4 col-sm-6"
                                />
                                <x-dashboard-shortcut
                                    href="{{ route('student.progress.index') }}"
                                    icon="fas fa-chart-line"
                                    title="تقدمي"
                                    subtitle="متابعة التقدم في المواد"
                                    accent="info"
                                    col-class="col-xl-2 col-lg-4 col-md-4 col-sm-6"
                                />
                                <x-dashboard-shortcut
                                    href="{{ route('student.gamification.badges') }}"
                                    icon="fas fa-medal"
                                    title="شاراتي"
                                    subtitle="جميع الشارات المكتسبة"
                                    accent="warning"
                                    col-class="col-xl-2 col-lg-4 col-md-4 col-sm-6"
                                />
                                <x-dashboard-shortcut
                                    href="{{ route('student.gamification.leaderboard') }}"
                                    icon="fas fa-ranking-star"
                                    title="لوحة المتصدرين"
                                    subtitle="ترتيبك بين الطلاب"
                                    accent="purple"
                                    col-class="col-xl-2 col-lg-4 col-md-4 col-sm-6"
                                />
                                <x-dashboard-shortcut
                                    href="{{ route('student.library.index') }}"
                                    icon="fas fa-book"
                                    title="المكتبة الرقمية"
                                    subtitle="كتب وموارد تعليمية"
                                    accent="teal"
                                    col-class="col-xl-2 col-lg-4 col-md-4 col-sm-6"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-7 col-lg-12 mb-4">
                    <div class="card dashboard-panel h-100">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h4 class="card-title mb-0">
                                    <i class="fe fe-book me-2"></i>
                                    الكورسات قيد التقدم
                                </h4>
                                <p class="fs-12 text-muted mb-0">موادك الدراسية والتقدم فيها</p>
                            </div>
                            <a href="{{ route('student.classes') }}" class="btn btn-sm btn-primary">
                                عرض الكل
                            </a>
                        </div>
                        <div class="card-body">
                            @if($subjectsCollection->isEmpty())
                                <div class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="fe fe-book-open fs-48 text-muted op-5"></i>
                                    </div>
                                    <h5 class="mb-2">لا توجد كورسات قيد التقدم</h5>
                                    <p class="text-muted mb-3">ابدأ رحلتك التعليمية الآن</p>
                                    <a href="{{ route('student.classes') }}" class="btn btn-primary">
                                        <i class="fe fe-search me-1"></i>
                                        تصفح الصفوف والمواد
                                    </a>
                                </div>
                            @else
                                @foreach($subjectsCollection as $item)
                                    @php
                                        $subject = $item['subject'] ?? null;
                                        $p = $item['progress'] ?? [];
                                    @endphp
                                    @if($subject)
                                        <div class="mb-4 pb-4 border-bottom dashboard-course-row">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">
                                                        <a href="{{ route('student.subjects.show', $subject->id) }}" class="text-dark text-decoration-none">
                                                            {{ $subject->name }}
                                                        </a>
                                                    </h6>
                                                    <small class="text-muted">
                                                        {{ $subject->schoolClass->name ?? '' }}
                                                        @if(optional($subject->schoolClass)->stage)
                                                            — {{ $subject->schoolClass->stage->name }}
                                                        @endif
                                                    </small>
                                                </div>
                                                <div class="text-end ms-3">
                                                    <span class="badge bg-primary-transparent text-primary fs-12">
                                                        {{ $p['overall_percentage'] ?? 0 }}%
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="progress progress-xs mb-2">
                                                <div class="progress-bar bg-primary" role="progressbar"
                                                     style="width: {{ min(100, $p['overall_percentage'] ?? 0) }}%;"
                                                     aria-valuenow="{{ $p['overall_percentage'] ?? 0 }}"
                                                     aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="d-flex justify-content-between fs-11 text-muted flex-wrap gap-1">
                                                <span><i class="fe fe-file-text me-1"></i> الدروس: {{ $p['lessons_completed'] ?? 0 }}/{{ $p['lessons_total'] ?? 0 }}</span>
                                                <span><i class="fe fe-edit me-1"></i> الاختبارات: {{ $p['quizzes_completed'] ?? 0 }}/{{ $p['quizzes_total'] ?? 0 }}</span>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-xl-5 col-lg-12">
                    <div class="card dashboard-panel mb-3">
                        <div class="card-header">
                            <h4 class="card-title mb-0">
                                <i class="fe fe-award me-2"></i>
                                آخر الشارات
                            </h4>
                        </div>
                        <div class="card-body">
                            @if(($latestBadges ?? collect())->isEmpty())
                                <div class="text-center py-4">
                                    <i class="fe fe-award fs-32 text-muted op-5 mb-2 d-block"></i>
                                    <p class="text-muted mb-0">لم تحصل على شارات بعد</p>
                                    <a href="{{ route('student.gamification.badges') }}" class="btn btn-sm btn-outline-primary mt-3">
                                        عرض الشارات
                                    </a>
                                </div>
                            @else
                                <div class="d-flex flex-wrap gap-2 justify-content-center">
                                    @foreach($latestBadges as $userBadge)
                                        @if($userBadge->badge)
                                            <div class="dashboard-badge-item text-center p-2">
                                                <div class="mb-1">
                                                    @if($userBadge->badge->icon)
                                                        <i class="{{ $userBadge->badge->icon }} fs-24 text-warning"></i>
                                                    @else
                                                        <i class="fe fe-award fs-24 text-warning"></i>
                                                    @endif
                                                </div>
                                                <div class="fs-11 fw-semibold">{{ $userBadge->badge->name }}</div>
                                                <div class="fs-10 text-muted">{{ $userBadge->earned_at->diffForHumans() }}</div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="mt-3 text-center">
                                    <a href="{{ route('student.gamification.badges') }}" class="btn btn-sm btn-outline-primary">
                                        عرض جميع الشارات
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card dashboard-panel">
                        <div class="card-header">
                            <h4 class="card-title mb-0">
                                <i class="fe fe-bar-chart-2 me-2"></i>
                                إحصائيات سريعة
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fs-13 text-muted">متوسط التقدم</span>
                                    <span class="fw-bold">{{ number_format($overallAverage ?? 0, 1) }}%</span>
                                </div>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-success" style="width: {{ min(100, $overallAverage ?? 0) }}%;" role="progressbar"></div>
                                </div>
                            </div>
                            @if(isset($levelProgress) && is_array($levelProgress))
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fs-13 text-muted">تقدم المستوى</span>
                                        <span class="fw-bold">{{ number_format($levelProgress['progress_percentage'] ?? 0, 0) }}%</span>
                                    </div>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-warning" style="width: {{ min(100, $levelProgress['progress_percentage'] ?? 0) }}%;" role="progressbar"></div>
                                    </div>
                                </div>
                            @endif
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fs-13 text-muted">إجمالي النقاط</span>
                                <span class="fw-bold text-success">{{ number_format($totalPoints ?? 0) }}</span>
                            </div>
                            <a href="{{ route('student.reports.index') }}" class="btn btn-sm btn-primary w-100">
                                <i class="fe fe-file-text me-1"></i>
                                عرض التقارير التفصيلية
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
