@extends('student.layouts.master')

@section('page-title')
لوحة التحكم
@stop

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@600;700;800&display=swap" rel="stylesheet">
    @include('student.partials.dashboard-widget-styles')
    <style>
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

        .dashboard-welcome {
            margin: 1rem 0 1.25rem;
            min-width: 0;
            max-width: 100%;
        }

        .dashboard-welcome__title {
            margin: 0 0 0.35rem;
            font-family: "Alexandria", "Segoe UI", Tahoma, "Noto Sans Arabic", sans-serif;
            font-weight: 800;
            font-size: clamp(1.15rem, 2.8vw + 0.7rem, 1.75rem);
            line-height: 1.45;
            letter-spacing: -0.01em;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .dashboard-welcome__name {
            color: #0d9488;
        }

        [data-theme-mode="dark"] .dashboard-welcome__name,
        [data-bs-theme="dark"] .dashboard-welcome__name {
            color: #2dd4bf;
        }

        .dashboard-welcome__meta {
            margin: 0;
            color: var(--text-muted, #64748b);
            font-size: 0.88rem;
            font-weight: 600;
            line-height: 1.55;
        }

        @media (max-width: 575.98px) {
            .dashboard-welcome {
                margin: 0.75rem 0 1rem;
            }

            .dashboard-welcome__title {
                font-size: 1.2rem;
            }

            .dashboard-welcome__meta {
                font-size: 0.8rem;
            }
        }

        /* على الجوال: الاختصارات السريعة أولاً (بترتيب معكوس)، ثم الودجات الملونة تحتها */
        @media (max-width: 991.98px) {
            .main-content.app-content > .container-fluid {
                display: flex;
                flex-direction: column;
            }

            .main-content.app-content > .container-fluid > .dashboard-welcome { order: 0; }
            .main-content.app-content > .container-fluid > .dashboard-shortcuts-row { order: 1; }
            .main-content.app-content > .container-fluid > .dashboard-stats-row { order: 2; }
            .main-content.app-content > .container-fluid > .dashboard-main-row { order: 3; }

            .dashboard-shortcuts-grid {
                flex-direction: row-reverse;
            }
        }

        /* ملاحظة: لا نستخدم flex-direction: column-reverse دون 576px — كانت تُلغي
           التفاف Bootstrap (col-6 يصبح بلا تأثير عملياً مع flex-direction: column)
           فتصير كل بطاقة اختصار وحدها في صفّها بدل بطاقتين جنباً إلى جنب.
           row-reverse من القاعدة أعلاه يكفي وحده لعكس الترتيب مع إبقاء بطاقتين
           في كل صف كما يقتضي col-6. */
    </style>
@endpush

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="dashboard-welcome">
                <h1 class="dashboard-welcome__title">
                    مرحباً <span class="dashboard-welcome__name">{{ Auth::user()->name }}</span>، أهلاً بعودتك!
                </h1>
                <p class="dashboard-welcome__meta">{{ now()->translatedFormat('l، d F Y') }} — أنت مسجل كطالب</p>
            </div>

            @php
                $subjectsCollection = collect($topSubjects ?? []);
                $subjectsTotal = $subjectsCount ?? $subjectsCollection->count();
            @endphp

            <div class="row g-3 mb-4 dashboard-stats-row">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="dsc-link" style="--card-delay: 0s">
                        <div class="dsc-card dsc-card--gold">
                            <div class="dsc-shine"></div>
                            <div class="dsc-mesh"></div>
                            <div class="dsc-bubble dsc-bubble-1"></div>
                            <div class="dsc-bubble dsc-bubble-2"></div>
                            <div class="dsc-bubble dsc-bubble-3"></div>
                            <div class="dsc-glow"></div>
                            <div class="dsc-body">
                                <div class="dsc-content">
                                    <span class="dsc-label">إجمالي النقاط</span>
                                    <span class="dsc-value" data-count="{{ (int) ($totalPoints ?? 0) }}">0</span>
                                    <span class="dsc-subtext">
                                        @if($currentLevel ?? null)
                                            المستوى {{ $currentLevel->name }}
                                        @else
                                            استمر لتكسب المزيد
                                        @endif
                                    </span>
                                </div>
                                <div class="dsc-icon-wrap">
                                    <span class="dsc-icon-ring"></span>
                                    <span class="dsc-icon-circle"><i class="fas fa-star"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="dsc-link" style="--card-delay: 0.1s">
                        <div class="dsc-card dsc-card--green">
                            <div class="dsc-shine"></div>
                            <div class="dsc-mesh"></div>
                            <div class="dsc-bubble dsc-bubble-1"></div>
                            <div class="dsc-bubble dsc-bubble-2"></div>
                            <div class="dsc-bubble dsc-bubble-3"></div>
                            <div class="dsc-glow"></div>
                            <div class="dsc-body">
                                <div class="dsc-content">
                                    <span class="dsc-label">متوسط التقدم</span>
                                    <span class="dsc-value" data-count="{{ (int) round($overallAverage ?? 0) }}" data-suffix="%">0</span>
                                    <span class="dsc-subtext">في جميع موادك</span>
                                </div>
                                <div class="dsc-icon-wrap">
                                    <span class="dsc-icon-ring"></span>
                                    <span class="dsc-icon-circle"><i class="fas fa-chart-line"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="dsc-link" style="--card-delay: 0.2s">
                        <div class="dsc-card dsc-card--purple">
                            <div class="dsc-shine"></div>
                            <div class="dsc-mesh"></div>
                            <div class="dsc-bubble dsc-bubble-1"></div>
                            <div class="dsc-bubble dsc-bubble-2"></div>
                            <div class="dsc-bubble dsc-bubble-3"></div>
                            <div class="dsc-glow"></div>
                            <div class="dsc-body">
                                <div class="dsc-content">
                                    <span class="dsc-label">الشارات</span>
                                    <span class="dsc-value" data-count="{{ (int) ($badgesCount ?? 0) }}">0</span>
                                    <span class="dsc-subtext">{{ $achievementsCount ?? 0 }} إنجاز مكتمل</span>
                                </div>
                                <div class="dsc-icon-wrap">
                                    <span class="dsc-icon-ring"></span>
                                    <span class="dsc-icon-circle"><i class="fas fa-award"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="dsc-link" style="--card-delay: 0.3s">
                        <div class="dsc-card dsc-card--blue">
                            <div class="dsc-shine"></div>
                            <div class="dsc-mesh"></div>
                            <div class="dsc-bubble dsc-bubble-1"></div>
                            <div class="dsc-bubble dsc-bubble-2"></div>
                            <div class="dsc-bubble dsc-bubble-3"></div>
                            <div class="dsc-glow"></div>
                            <div class="dsc-body">
                                <div class="dsc-content">
                                    <span class="dsc-label">المواد النشطة</span>
                                    <span class="dsc-value" data-count="{{ (int) $subjectsTotal }}">0</span>
                                    <span class="dsc-subtext">كورسات قيد المتابعة</span>
                                </div>
                                <div class="dsc-icon-wrap">
                                    <span class="dsc-icon-ring"></span>
                                    <span class="dsc-icon-circle"><i class="fas fa-book-open"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4 dashboard-shortcuts-row">
                <div class="col-12">
                    <div class="card dashboard-panel">
                        <div class="card-header pb-2">
                            <h4 class="card-title mb-0">
                                <i class="fe fe-zap me-2"></i> اختصارات سريعة
                            </h4>
                            <p class="fs-12 text-muted mb-0">الوصول السريع لأهم أقسام منصتك</p>
                        </div>
                        <div class="card-body">
                            @php
                                $studentShortcuts = [
                                    ['href' => route('student.classes'), 'icon' => 'fas fa-layer-group', 'title' => 'صفوفي', 'desc' => 'الصفوف والمواد', 'theme' => 'blue'],
                                    ['href' => route('student.quizzes.results'), 'icon' => 'fas fa-clipboard-check', 'title' => 'نتائج الاختبارات', 'desc' => 'نتائج وتقارير الاختبارات', 'theme' => 'green'],
                                    ['href' => route('student.progress.index'), 'icon' => 'fas fa-chart-line', 'title' => 'تقدمي', 'desc' => 'متابعة التقدم في المواد', 'theme' => 'cyan'],
                                    ['href' => route('student.gamification.badges'), 'icon' => 'fas fa-medal', 'title' => 'شاراتي', 'desc' => 'جميع الشارات المكتسبة', 'theme' => 'gold'],
                                    ['href' => route('student.gamification.leaderboard'), 'icon' => 'fas fa-ranking-star', 'title' => 'لوحة المتصدرين', 'desc' => 'ترتيبك بين الطلاب', 'theme' => 'purple'],
                                ];
                            @endphp
                            <div class="row g-3 shortcuts-grid dashboard-shortcuts-grid">
                                @foreach ($studentShortcuts as $index => $shortcut)
                                    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 col-6">
                                        <a href="{{ $shortcut['href'] }}"
                                           class="shortcut-card shortcut-theme-{{ $shortcut['theme'] }}"
                                           style="--shortcut-delay: {{ $index * 0.05 }}s">
                                            <span class="shortcut-shine"></span>
                                            <span class="shortcut-accent"></span>
                                            <span class="shortcut-icon-wrap">
                                                <span class="shortcut-icon-ring"></span>
                                                <span class="shortcut-icon">
                                                    <i class="{{ $shortcut['icon'] }}"></i>
                                                </span>
                                            </span>
                                            <span class="shortcut-title">{{ $shortcut['title'] }}</span>
                                            <span class="shortcut-desc">{{ $shortcut['desc'] }}</span>
                                            <span class="shortcut-arrow"><i class="fas fa-chevron-left"></i></span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row dashboard-main-row">
                <div class="col-xl-6 col-lg-12 mb-4">
                    <div class="card dashboard-panel h-100">
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
                </div>

                <div class="col-xl-6 col-lg-12 mb-4">
                    <div class="card dashboard-panel h-100">
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

@push('scripts')
<script>
(function () {
    // عدّاد تصاعدي لبطاقات لوحة التحكم الملوّنة — نفس حركة Hr-System (easeOutExpo، 1400ms)
    function easeOutExpo(t) {
        return t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
    }

    function animateCount(el) {
        const target = parseInt(el.getAttribute('data-count'), 10) || 0;
        const suffix = el.getAttribute('data-suffix') || '';
        const duration = 1400;
        const start = performance.now();

        function tick(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = easeOutExpo(progress);
            const current = Math.round(target * eased);
            el.textContent = current.toLocaleString('en-US') + suffix;

            if (progress < 1) {
                requestAnimationFrame(tick);
            } else {
                el.textContent = target.toLocaleString('en-US') + suffix;
                el.classList.add('dsc-value-done');
            }
        }

        requestAnimationFrame(tick);
    }

    document.querySelectorAll('.dsc-value[data-count]').forEach(animateCount);
})();
</script>
<script>
(function () {
    // تأثير نقر متموّج على بطاقات الاختصارات السريعة — نفس حركة Hr-System
    document.querySelectorAll('.shortcut-card').forEach(function (card) {
        card.addEventListener('click', function (e) {
            const rect = card.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const ripple = document.createElement('span');
            ripple.className = 'shortcut-ripple';
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
            ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
            card.appendChild(ripple);
            ripple.addEventListener('animationend', function () { ripple.remove(); });
        });
    });
})();
</script>
@endpush
