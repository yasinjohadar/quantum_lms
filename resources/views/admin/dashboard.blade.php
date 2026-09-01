
@extends('admin.layouts.master')

@section('page-title')
لوحة التحكم
@stop

@push('styles')
    @include('admin.pages.dashboard.partials.widget-styles')
@endpush

@section('content')
  <!-- Start::app-content -->
        <div class="main-content app-content">
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                    <div>
                        <h4 class="mb-0 dashboard-welcome-title">مرحباً {{ $greetingUserName ?? (auth()->user()->name ?? 'مستخدم') }}، أهلاً بعودتك!</h4>
                        <p class="mb-0 text-muted dashboard-welcome-sub">أنت مسجل الدخول كـ {{ $greetingPrimaryRoleLabel ?? (auth()->user()->primary_role_label ?? 'مستخدم') }}</p>
                    </div>
                </div>
                <!-- End Page Header -->

                <!-- الكاردات الملونة: للأدمن فقط -->
                @if(auth()->check() && auth()->user()->hasRole('admin'))
                @php
                    $enrollmentsChange = $stats['enrollments_change'] ?? 0;
                    $enrollmentsSubtext = ($stats['active_enrollments'] ?? 0).' نشط';
                    if ($enrollmentsChange != 0) {
                        $enrollmentsSubtext .= ' '.($enrollmentsChange > 0 ? '▲' : '▼').' '.abs($enrollmentsChange).'%';
                    }
                @endphp
                <div class="row g-3 mb-4">
                    <div class="col-xl col-lg-6 col-md-6 col-sm-12">
                        <div class="dsc-link" style="--card-delay: 0s">
                            <div class="dsc-card dsc-card--blue">
                                <div class="dsc-shine"></div>
                                <div class="dsc-mesh"></div>
                                <div class="dsc-bubble dsc-bubble-1"></div>
                                <div class="dsc-bubble dsc-bubble-2"></div>
                                <div class="dsc-bubble dsc-bubble-3"></div>
                                <div class="dsc-glow"></div>
                                <div class="dsc-body">
                                    <div class="dsc-content">
                                        <span class="dsc-label">إجمالي الطلاب</span>
                                        <span class="dsc-value" data-count="{{ (int) ($stats['total_students'] ?? 0) }}">0</span>
                                        <span class="dsc-subtext">{{ $stats['active_students'] ?? 0 }} طالب نشط</span>
                                    </div>
                                    <div class="dsc-icon-wrap">
                                        <span class="dsc-icon-ring"></span>
                                        <span class="dsc-icon-circle"><i class="fas fa-users"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl col-lg-6 col-md-6 col-sm-12">
                        <a href="{{ route('admin.classes.index') }}" class="dsc-link" style="--card-delay: 0.1s">
                            <div class="dsc-card dsc-card--purple">
                                <div class="dsc-shine"></div>
                                <div class="dsc-mesh"></div>
                                <div class="dsc-bubble dsc-bubble-1"></div>
                                <div class="dsc-bubble dsc-bubble-2"></div>
                                <div class="dsc-bubble dsc-bubble-3"></div>
                                <div class="dsc-glow"></div>
                                <div class="dsc-body">
                                    <div class="dsc-content">
                                        <span class="dsc-label">إجمالي الصفوف</span>
                                        <span class="dsc-value" data-count="{{ (int) ($stats['total_classes'] ?? 0) }}">0</span>
                                        <span class="dsc-subtext">{{ $stats['total_stages'] ?? 0 }} مرحلة</span>
                                    </div>
                                    <div class="dsc-icon-wrap">
                                        <span class="dsc-icon-ring"></span>
                                        <span class="dsc-icon-circle"><i class="fas fa-layer-group"></i></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl col-lg-6 col-md-6 col-sm-12">
                        <div class="dsc-link" style="--card-delay: 0.2s">
                            <div class="dsc-card dsc-card--green">
                                <div class="dsc-shine"></div>
                                <div class="dsc-mesh"></div>
                                <div class="dsc-bubble dsc-bubble-1"></div>
                                <div class="dsc-bubble dsc-bubble-2"></div>
                                <div class="dsc-bubble dsc-bubble-3"></div>
                                <div class="dsc-glow"></div>
                                <div class="dsc-body">
                                    <div class="dsc-content">
                                        <span class="dsc-label">إجمالي المواد</span>
                                        <span class="dsc-value" data-count="{{ (int) ($stats['total_subjects'] ?? 0) }}">0</span>
                                        <span class="dsc-subtext">{{ $stats['total_lessons'] ?? 0 }} درس</span>
                                    </div>
                                    <div class="dsc-icon-wrap">
                                        <span class="dsc-icon-ring"></span>
                                        <span class="dsc-icon-circle"><i class="fas fa-book"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl col-lg-6 col-md-6 col-sm-12">
                        <div class="dsc-link" style="--card-delay: 0.3s">
                            <div class="dsc-card dsc-card--orange">
                                <div class="dsc-shine"></div>
                                <div class="dsc-mesh"></div>
                                <div class="dsc-bubble dsc-bubble-1"></div>
                                <div class="dsc-bubble dsc-bubble-2"></div>
                                <div class="dsc-bubble dsc-bubble-3"></div>
                                <div class="dsc-glow"></div>
                                <div class="dsc-body">
                                    <div class="dsc-content">
                                        <span class="dsc-label">إجمالي الاختبارات</span>
                                        <span class="dsc-value" data-count="{{ (int) ($stats['total_quizzes'] ?? 0) }}">0</span>
                                        <span class="dsc-subtext">{{ $stats['total_questions'] ?? 0 }} سؤال</span>
                                    </div>
                                    <div class="dsc-icon-wrap">
                                        <span class="dsc-icon-ring"></span>
                                        <span class="dsc-icon-circle"><i class="fas fa-clipboard-list"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl col-lg-6 col-md-6 col-sm-12">
                        <div class="dsc-link" style="--card-delay: 0.4s">
                            <div class="dsc-card dsc-card--gold">
                                <div class="dsc-shine"></div>
                                <div class="dsc-mesh"></div>
                                <div class="dsc-bubble dsc-bubble-1"></div>
                                <div class="dsc-bubble dsc-bubble-2"></div>
                                <div class="dsc-bubble dsc-bubble-3"></div>
                                <div class="dsc-glow"></div>
                                <div class="dsc-body">
                                    <div class="dsc-content">
                                        <span class="dsc-label">إجمالي الانضمامات</span>
                                        <span class="dsc-value" data-count="{{ (int) ($stats['total_enrollments'] ?? 0) }}">0</span>
                                        <span class="dsc-subtext">{{ $enrollmentsSubtext }}</span>
                                    </div>
                                    <div class="dsc-icon-wrap">
                                        <span class="dsc-icon-ring"></span>
                                        <span class="dsc-icon-circle"><i class="fas fa-user-check"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if(auth()->check() && auth()->user()->usesTeacherAssignmentScope())
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header pb-2">
                                <h4 class="card-title mb-0">
                                    <i class="fe fe-book me-2"></i> إدارة صفوفي وموادي
                                </h4>
                                <p class="fs-12 text-muted mb-0">الصفوف والمواد المنسوبة لك — تظهر دائماً حتى لو كانت القوائم فارغة</p>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <x-dashboard-shortcut
                                        href="{{ route('admin.classes.index') }}"
                                        icon="fas fa-school"
                                        title="صفوفي المخصصة"
                                        subtitle="عرض الصفوف المرتبطة بك كمعلم"
                                        accent="primary"
                                        col-class="col-xl-4 col-lg-6 col-md-6 col-sm-12"
                                    >
                                        <span class="badge bg-light text-dark mt-2">{{ (int) ($teacherClassesCount ?? 0) }} صف</span>
                                        @if(($teacherClassesCount ?? 0) === 0)
                                            <small class="text-muted d-block mt-2">لا توجد صفوف مخصصة بعد — يمكنك فتح الصفحة للتأكد</small>
                                        @endif
                                    </x-dashboard-shortcut>
                                    <x-dashboard-shortcut
                                        href="{{ route('admin.subjects.index') }}"
                                        icon="fas fa-book-open"
                                        title="موادي المخصصة"
                                        subtitle="عرض المواد المتاحة لك (مباشرة أو عبر الصفوف)"
                                        accent="success"
                                        col-class="col-xl-4 col-lg-6 col-md-6 col-sm-12"
                                    >
                                        <span class="badge bg-light text-dark mt-2">{{ (int) ($teacherSubjectsCount ?? 0) }} مادة</span>
                                        @if(($teacherSubjectsCount ?? 0) === 0)
                                            <small class="text-muted d-block mt-2">لا توجد مواد متاحة بعد — يمكنك فتح الصفحة للتأكد</small>
                                        @endif
                                    </x-dashboard-shortcut>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if(auth()->check() && auth()->user()->usesSupervisorAssignmentScope())
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header pb-2">
                                <h4 class="card-title mb-0">
                                    <i class="fe fe-briefcase me-2"></i> إشرافي
                                </h4>
                                <p class="fs-12 text-muted mb-0">صفوفك وموادك المخصصة — تظهر دائماً حتى لو كانت القوائم فارغة</p>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <x-dashboard-shortcut
                                        href="{{ route('admin.my-classes') }}"
                                        icon="fas fa-school"
                                        title="صفوفي المخصصة"
                                        subtitle="عرض الصفوف المرتبطة بك كمشرف"
                                        accent="primary"
                                        col-class="col-xl-4 col-lg-6 col-md-6 col-sm-12"
                                    >
                                        <span class="badge bg-light text-dark mt-2">{{ (int) ($supervisorClassesCount ?? 0) }} صف</span>
                                        @if(($supervisorClassesCount ?? 0) === 0)
                                            <small class="text-muted d-block mt-2">لا توجد صفوف مخصصة بعد — يمكنك فتح الصفحة للتأكد</small>
                                        @endif
                                    </x-dashboard-shortcut>
                                    <x-dashboard-shortcut
                                        href="{{ route('admin.my-subjects') }}"
                                        icon="fas fa-book-open"
                                        title="موادي المخصصة"
                                        subtitle="عرض المواد المتاحة لك (مباشرة أو عبر الصفوف)"
                                        accent="success"
                                        col-class="col-xl-4 col-lg-6 col-md-6 col-sm-12"
                                    >
                                        <span class="badge bg-light text-dark mt-2">{{ (int) ($supervisorSubjectsCount ?? 0) }} مادة</span>
                                        @if(($supervisorSubjectsCount ?? 0) === 0)
                                            <small class="text-muted d-block mt-2">لا توجد مواد متاحة بعد — يمكنك فتح الصفحة للتأكد</small>
                                        @endif
                                    </x-dashboard-shortcut>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- اختصارات سريعة (بنفس ستايل/ألوان/حركات مشروع Hr-System) -->
                @php
                    $quickShortcuts = array_values(array_filter([
                        auth()->user()->can('user-list') ? ['href' => route('users.index'), 'icon' => 'fas fa-users', 'title' => 'الطلاب', 'desc' => 'إدارة الطلاب', 'theme' => 'blue'] : null,
                        auth()->user()->can('class-list') ? ['href' => route('admin.classes.index'), 'icon' => 'fas fa-layer-group', 'title' => 'الصفوف', 'desc' => 'إدارة الصفوف الدراسية', 'theme' => 'purple'] : null,
                        auth()->user()->can('subject-list') ? ['href' => route('admin.subjects.index'), 'icon' => 'fas fa-book', 'title' => 'المواد', 'desc' => 'إدارة المواد', 'theme' => 'green'] : null,
                        auth()->user()->can('quiz-list') ? ['href' => route('admin.quizzes.index'), 'icon' => 'fas fa-clipboard-list', 'title' => 'الاختبارات', 'desc' => 'إدارة الاختبارات', 'theme' => 'cyan'] : null,
                        auth()->user()->can('enrollment-list') ? ['href' => route('admin.enrollments.index'), 'icon' => 'fas fa-user-check', 'title' => 'الانضمامات', 'desc' => 'إدارة الانضمامات', 'theme' => 'gold'] : null,
                        auth()->user()->can('report-view') ? ['href' => route('admin.reports.index'), 'icon' => 'fas fa-chart-line', 'title' => 'التقارير', 'desc' => 'التقارير والإحصائيات', 'theme' => 'red'] : null,
                        auth()->user()->can('user-login-logs') ? ['href' => route('admin.user-sessions.index'), 'icon' => 'fas fa-desktop', 'title' => 'الجلسات', 'desc' => 'جلسات المستخدمين', 'theme' => 'indigo'] : null,
                        auth()->user()->can('settings-manage') ? ['href' => route('admin.backups.index'), 'icon' => 'fas fa-database', 'title' => 'النسخ الاحتياطية', 'desc' => 'إدارة النسخ', 'theme' => 'teal'] : null,
                        auth()->user()->can('question-create') ? ['href' => route('admin.ai.models.index'), 'icon' => 'fas fa-brain', 'title' => 'نماذج AI', 'desc' => 'إدارة النماذج', 'theme' => 'pink'] : null,
                        auth()->user()->can('user-login-logs') ? ['href' => route('admin.login-logs.index'), 'icon' => 'fas fa-sign-in-alt', 'title' => 'سجلات الدخول', 'desc' => 'سجلات تسجيل الدخول', 'theme' => 'brown'] : null,
                        auth()->user()->can('settings-manage') ? ['href' => route('admin.settings.index'), 'icon' => 'fas fa-cog', 'title' => 'الإعدادات', 'desc' => 'إعدادات النظام', 'theme' => 'orange'] : null,
                    ]));
                @endphp
                @if(count($quickShortcuts))
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header pb-2">
                                <h4 class="card-title mb-0">
                                    <i class="fe fe-zap me-2"></i> اختصارات سريعة
                                </h4>
                                <p class="fs-12 text-muted mb-0">الوصول السريع لأهم أجزاء النظام</p>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 shortcuts-grid">
                                    @foreach ($quickShortcuts as $index => $shortcut)
                                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
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
                @endif
                <!-- end اختصارات سريعة -->

            </div>
        </div>
        <!-- End::app-content -->
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
