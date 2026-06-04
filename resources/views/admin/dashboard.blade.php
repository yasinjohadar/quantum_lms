
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
                        <h4 class="mb-0">مرحباً {{ $greetingUserName ?? (auth()->user()->name ?? 'مستخدم') }}، أهلاً بعودتك!</h4>
                        <p class="mb-0 text-muted">أنت مسجل الدخول كـ {{ $greetingPrimaryRoleLabel ?? (auth()->user()->primary_role_label ?? 'مستخدم') }}</p>
                    </div>
                </div>
                <!-- End Page Header -->

                <!-- الكاردات الملونة: للأدمن فقط -->
                @if(auth()->check() && auth()->user()->hasRole('admin'))
                <div class="row mb-4">
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3 mb-xl-0">
                        <div class="dashboard-stat-card dashboard-stat-card--students h-100">
                            <div class="dashboard-stat-card__body">
                                <div class="dashboard-stat-card__content">
                                    <div class="dashboard-stat-card__label">إجمالي الطلاب</div>
                                    <div class="dashboard-stat-card__value">{{ number_format($stats['total_students'] ?? 0) }}</div>
                                    <p class="dashboard-stat-card__meta">{{ $stats['active_students'] ?? 0 }} طالب نشط</p>
                                </div>
                                <div class="dashboard-stat-card__icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3 mb-xl-0">
                        <div class="dashboard-stat-card dashboard-stat-card--subjects h-100">
                            <div class="dashboard-stat-card__body">
                                <div class="dashboard-stat-card__content">
                                    <div class="dashboard-stat-card__label">إجمالي المواد</div>
                                    <div class="dashboard-stat-card__value">{{ number_format($stats['total_subjects'] ?? 0) }}</div>
                                    <p class="dashboard-stat-card__meta">{{ $stats['total_lessons'] ?? 0 }} درس</p>
                                </div>
                                <div class="dashboard-stat-card__icon">
                                    <i class="fas fa-book"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3 mb-xl-0">
                        <div class="dashboard-stat-card dashboard-stat-card--quizzes h-100">
                            <div class="dashboard-stat-card__body">
                                <div class="dashboard-stat-card__content">
                                    <div class="dashboard-stat-card__label">إجمالي الاختبارات</div>
                                    <div class="dashboard-stat-card__value">{{ number_format($stats['total_quizzes'] ?? 0) }}</div>
                                    <p class="dashboard-stat-card__meta">{{ $stats['total_questions'] ?? 0 }} سؤال</p>
                                </div>
                                <div class="dashboard-stat-card__icon">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3 mb-xl-0">
                        <div class="dashboard-stat-card dashboard-stat-card--enrollments h-100">
                            <div class="dashboard-stat-card__body">
                                <div class="dashboard-stat-card__content">
                                    <div class="dashboard-stat-card__label">إجمالي الانضمامات</div>
                                    <div class="dashboard-stat-card__value">{{ number_format($stats['total_enrollments'] ?? 0) }}</div>
                                    <p class="dashboard-stat-card__meta">
                                        {{ $stats['active_enrollments'] ?? 0 }} نشط
                                        @if(isset($stats['enrollments_change']) && $stats['enrollments_change'] != 0)
                                            <span class="ms-1">
                                                @if($stats['enrollments_change'] > 0)
                                                    <i class="fas fa-arrow-circle-up"></i> {{ abs($stats['enrollments_change']) }}%
                                                @else
                                                    <i class="fas fa-arrow-circle-down"></i> {{ abs($stats['enrollments_change']) }}%
                                                @endif
                                            </span>
                                        @endif
                                    </p>
                                </div>
                                <div class="dashboard-stat-card__icon">
                                    <i class="fas fa-user-check"></i>
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

                <!-- اختصارات سريعة -->
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
                                <div class="row g-3">
                                    @can('user-list')
                                    <x-dashboard-shortcut
                                        href="{{ route('users.index') }}"
                                        icon="fas fa-users"
                                        title="الطلاب"
                                        subtitle="إدارة الطلاب"
                                        accent="primary"
                                    />
                                    @endcan

                                    @can('subject-list')
                                    <x-dashboard-shortcut
                                        href="{{ route('admin.subjects.index') }}"
                                        icon="fas fa-book"
                                        title="المواد"
                                        subtitle="إدارة المواد"
                                        accent="success"
                                    />
                                    @endcan

                                    @can('quiz-list')
                                    <x-dashboard-shortcut
                                        href="{{ route('admin.quizzes.index') }}"
                                        icon="fas fa-clipboard-list"
                                        title="الاختبارات"
                                        subtitle="إدارة الاختبارات"
                                        accent="info"
                                    />
                                    @endcan

                                    @can('enrollment-list')
                                    <x-dashboard-shortcut
                                        href="{{ route('admin.enrollments.index') }}"
                                        icon="fas fa-user-check"
                                        title="الانضمامات"
                                        subtitle="إدارة الانضمامات"
                                        accent="warning"
                                    />
                                    @endcan

                                    @can('report-view')
                                    <x-dashboard-shortcut
                                        href="{{ route('admin.reports.index') }}"
                                        icon="fas fa-chart-line"
                                        title="التقارير"
                                        subtitle="التقارير والإحصائيات"
                                        accent="danger"
                                    />
                                    @endcan

                                    @can('library-list')
                                    <x-dashboard-shortcut
                                        href="{{ route('admin.library.items.index') }}"
                                        icon="fas fa-book-reader"
                                        title="المكتبة"
                                        subtitle="المكتبة الرقمية"
                                        accent="secondary"
                                    />
                                    @endcan

                                    @can('user-login-logs')
                                    <x-dashboard-shortcut
                                        href="{{ route('admin.user-sessions.index') }}"
                                        icon="fas fa-desktop"
                                        title="الجلسات"
                                        subtitle="جلسات المستخدمين"
                                        accent="purple"
                                    />
                                    @endcan

                                    @can('settings-manage')
                                    <x-dashboard-shortcut
                                        href="{{ route('admin.backups.index') }}"
                                        icon="fas fa-database"
                                        title="النسخ الاحتياطية"
                                        subtitle="إدارة النسخ"
                                        accent="teal"
                                    />
                                    @endcan

                                    @can('question-create')
                                    <x-dashboard-shortcut
                                        href="{{ route('admin.ai.models.index') }}"
                                        icon="fas fa-brain"
                                        title="نماذج AI"
                                        subtitle="إدارة النماذج"
                                        accent="primary"
                                    />
                                    @endcan

                                    @can('calendar-list')
                                    <x-dashboard-shortcut
                                        href="{{ route('admin.calendar.index') }}"
                                        icon="fas fa-calendar-alt"
                                        title="التقويم"
                                        subtitle="الجدول الزمني"
                                        accent="orange"
                                    />
                                    @endcan

                                    @can('user-login-logs')
                                    <x-dashboard-shortcut
                                        href="{{ route('admin.login-logs.index') }}"
                                        icon="fas fa-sign-in-alt"
                                        title="سجلات الدخول"
                                        subtitle="سجلات تسجيل الدخول"
                                        accent="indigo"
                                    />
                                    @endcan

                                    @can('settings-manage')
                                    <x-dashboard-shortcut
                                        href="{{ route('admin.settings.index') }}"
                                        icon="fas fa-cog"
                                        title="الإعدادات"
                                        subtitle="إعدادات النظام"
                                        accent="muted"
                                    />
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end اختصارات سريعة -->

            </div>
        </div>
        <!-- End::app-content -->
@stop
