
@extends('admin.layouts.master')

@section('page-title')
لوحة التحكم
@stop

@section('content')
  <!-- Start::app-content -->
        <div class="main-content app-content">
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                    <div>
                        <h4 class="mb-0">مرحباً، أهلاً بعودتك!</h4>
                        <p class="mb-0 text-muted">لوحة تحكم إدارة النظام التعليمي</p>
                    </div>
                    <div class="main-dashboard-header-right">
                        <div>
                            <label class="fs-13 text-muted">الطلاب النشطون اليوم</label>
                            <h5 class="mb-0 fw-semibold">{{ $stats['today_sessions'] ?? 0 }}</h5>
                        </div>
                        <div>
                            <label class="fs-13 text-muted">محاولات الاختبارات اليوم</label>
                            <h5 class="mb-0 fw-semibold">{{ $stats['today_quiz_attempts'] ?? 0 }}</h5>
                        </div>
                        <div>
                            <label class="fs-13 text-muted">تسجيلات الدخول اليوم</label>
                            <h5 class="mb-0 fw-semibold">{{ $stats['today_logins'] ?? 0 }}</h5>
                        </div>
                    </div>
                </div>
                <!-- End Page Header -->

                <!-- row -->
                <div class="row mb-4">
                    <!-- إجمالي الطلاب -->
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3 mb-xl-0">
                        <div class="card overflow-hidden sales-card bg-primary-gradient h-100">
                            <div class="px-3 pt-3 pb-2 pt-0">
                                <div>
                                    <h6 class="mb-3 fs-12 text-fixed-white">إجمالي الطلاب</h6>
                                </div>
                                <div class="pb-0 mt-0">
                                    <div class="d-flex">
                                        <div>
                                            <h4 class="fs-20 fw-bold mb-1 text-fixed-white">{{ number_format($stats['total_students'] ?? 0) }}</h4>
                                            <p class="mb-0 fs-12 text-fixed-white op-7">{{ $stats['active_students'] ?? 0 }} طالب نشط</p>
                                        </div>
                                        <span class="float-end my-auto ms-auto">
                                            <i class="fas fa-users text-fixed-white fs-24"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- إجمالي المواد -->
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3 mb-xl-0">
                        <div class="card overflow-hidden sales-card bg-success-gradient h-100">
                            <div class="px-3 pt-3 pb-2 pt-0">
                                <div>
                                    <h6 class="mb-3 fs-12 text-fixed-white">إجمالي المواد</h6>
                                </div>
                                <div class="pb-0 mt-0">
                                    <div class="d-flex">
                                        <div>
                                            <h4 class="fs-20 fw-bold mb-1 text-fixed-white">{{ number_format($stats['total_subjects'] ?? 0) }}</h4>
                                            <p class="mb-0 fs-12 text-fixed-white op-7">{{ $stats['total_lessons'] ?? 0 }} درس</p>
                                        </div>
                                        <span class="float-end my-auto ms-auto">
                                            <i class="fas fa-book text-fixed-white fs-24"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- إجمالي الاختبارات -->
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3 mb-xl-0">
                        <div class="card overflow-hidden sales-card bg-info-gradient h-100">
                            <div class="px-3 pt-3 pb-2 pt-0">
                                <div>
                                    <h6 class="mb-3 fs-12 text-fixed-white">إجمالي الاختبارات</h6>
                                </div>
                                <div class="pb-0 mt-0">
                                    <div class="d-flex">
                                        <div>
                                            <h4 class="fs-20 fw-bold mb-1 text-fixed-white">{{ number_format($stats['total_quizzes'] ?? 0) }}</h4>
                                            <p class="mb-0 fs-12 text-fixed-white op-7">{{ $stats['total_questions'] ?? 0 }} سؤال</p>
                                        </div>
                                        <span class="float-end my-auto ms-auto">
                                            <i class="fas fa-clipboard-list text-fixed-white fs-24"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- إجمالي الانضمامات -->
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3 mb-xl-0">
                        <div class="card overflow-hidden sales-card bg-warning-gradient h-100">
                            <div class="px-3 pt-3 pb-2 pt-0">
                                <div>
                                    <h6 class="mb-3 fs-12 text-fixed-white">إجمالي الانضمامات</h6>
                                </div>
                                <div class="pb-0 mt-0">
                                    <div class="d-flex">
                                        <div>
                                            <h4 class="fs-20 fw-bold mb-1 text-fixed-white">{{ number_format($stats['total_enrollments'] ?? 0) }}</h4>
                                            <p class="mb-0 fs-12 text-fixed-white op-7">
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
                                        <span class="float-end my-auto ms-auto">
                                            <i class="fas fa-user-check text-fixed-white fs-24"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- row closed -->

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
                                    <!-- الطلاب -->
                                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                        <a href="{{ route('users.index') }}" class="card border shadow-sm h-100 text-decoration-none quick-link-card">
                                            <div class="card-body text-center p-3">
                                                <div class="avatar avatar-md bg-primary-transparent mx-auto mb-2">
                                                    <i class="fas fa-users fs-20 text-primary"></i>
                                                </div>
                                                <h6 class="mb-0 fw-semibold">الطلاب</h6>
                                                <small class="text-muted">إدارة الطلاب</small>
                                            </div>
                                        </a>
                                    </div>

                                    <!-- المواد -->
                                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                        <a href="{{ route('admin.subjects.index') }}" class="card border shadow-sm h-100 text-decoration-none quick-link-card">
                                            <div class="card-body text-center p-3">
                                                <div class="avatar avatar-md bg-success-transparent mx-auto mb-2">
                                                    <i class="fas fa-book fs-20 text-success"></i>
                                                </div>
                                                <h6 class="mb-0 fw-semibold">المواد</h6>
                                                <small class="text-muted">إدارة المواد</small>
                                            </div>
                                        </a>
                                    </div>

                                    <!-- الاختبارات -->
                                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                        <a href="{{ route('admin.quizzes.index') }}" class="card border shadow-sm h-100 text-decoration-none quick-link-card">
                                            <div class="card-body text-center p-3">
                                                <div class="avatar avatar-md bg-info-transparent mx-auto mb-2">
                                                    <i class="fas fa-clipboard-list fs-20 text-info"></i>
                                                </div>
                                                <h6 class="mb-0 fw-semibold">الاختبارات</h6>
                                                <small class="text-muted">إدارة الاختبارات</small>
                                            </div>
                                        </a>
                                    </div>

                                    <!-- الانضمامات -->
                                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                        <a href="{{ route('admin.enrollments.index') }}" class="card border shadow-sm h-100 text-decoration-none quick-link-card">
                                            <div class="card-body text-center p-3">
                                                <div class="avatar avatar-md bg-warning-transparent mx-auto mb-2">
                                                    <i class="fas fa-user-check fs-20 text-warning"></i>
                                                </div>
                                                <h6 class="mb-0 fw-semibold">الانضمامات</h6>
                                                <small class="text-muted">إدارة الانضمامات</small>
                                            </div>
                                        </a>
                                    </div>

                                    <!-- التقارير -->
                                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                        <a href="{{ route('admin.reports.index') }}" class="card border shadow-sm h-100 text-decoration-none quick-link-card">
                                            <div class="card-body text-center p-3">
                                                <div class="avatar avatar-md bg-danger-transparent mx-auto mb-2">
                                                    <i class="fas fa-chart-line fs-20 text-danger"></i>
                                                </div>
                                                <h6 class="mb-0 fw-semibold">التقارير</h6>
                                                <small class="text-muted">التقارير والإحصائيات</small>
                                            </div>
                                        </a>
                                    </div>

                                    <!-- المكتبة -->
                                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                        <a href="{{ route('admin.library.items.index') }}" class="card border shadow-sm h-100 text-decoration-none quick-link-card">
                                            <div class="card-body text-center p-3">
                                                <div class="avatar avatar-md bg-secondary-transparent mx-auto mb-2">
                                                    <i class="fas fa-book-reader fs-20 text-secondary"></i>
                                                </div>
                                                <h6 class="mb-0 fw-semibold">المكتبة</h6>
                                                <small class="text-muted">المكتبة الرقمية</small>
                                            </div>
                                        </a>
                                    </div>

                                    <!-- جلسات المستخدمين -->
                                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                        <a href="{{ route('admin.user-sessions.index') }}" class="card border shadow-sm h-100 text-decoration-none quick-link-card">
                                            <div class="card-body text-center p-3">
                                                <div class="avatar avatar-md bg-purple-transparent mx-auto mb-2">
                                                    <i class="fas fa-desktop fs-20" style="color: #6f42c1;"></i>
                                                </div>
                                                <h6 class="mb-0 fw-semibold">الجلسات</h6>
                                                <small class="text-muted">جلسات المستخدمين</small>
                                            </div>
                                        </a>
                                    </div>

                                    <!-- النسخ الاحتياطية -->
                                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                        <a href="{{ route('admin.backups.index') }}" class="card border shadow-sm h-100 text-decoration-none quick-link-card">
                                            <div class="card-body text-center p-3">
                                                <div class="avatar avatar-md bg-teal-transparent mx-auto mb-2">
                                                    <i class="fas fa-database fs-20" style="color: #20c997;"></i>
                                                </div>
                                                <h6 class="mb-0 fw-semibold">النسخ الاحتياطية</h6>
                                                <small class="text-muted">إدارة النسخ</small>
                                            </div>
                                        </a>
                                    </div>

                                    <!-- نماذج AI -->
                                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                        <a href="{{ route('admin.ai.models.index') }}" class="card border shadow-sm h-100 text-decoration-none quick-link-card">
                                            <div class="card-body text-center p-3">
                                                <div class="avatar avatar-md bg-gradient-primary-transparent mx-auto mb-2">
                                                    <i class="fas fa-brain fs-20 text-primary"></i>
                                                </div>
                                                <h6 class="mb-0 fw-semibold">نماذج AI</h6>
                                                <small class="text-muted">إدارة النماذج</small>
                                            </div>
                                        </a>
                                    </div>

                                    <!-- التقويم -->
                                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                        <a href="{{ route('admin.calendar.index') }}" class="card border shadow-sm h-100 text-decoration-none quick-link-card">
                                            <div class="card-body text-center p-3">
                                                <div class="avatar avatar-md bg-orange-transparent mx-auto mb-2">
                                                    <i class="fas fa-calendar-alt fs-20" style="color: #fd7e14;"></i>
                                                </div>
                                                <h6 class="mb-0 fw-semibold">التقويم</h6>
                                                <small class="text-muted">الجدول الزمني</small>
                                            </div>
                                        </a>
                                    </div>

                                    <!-- سجلات الدخول -->
                                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                        <a href="{{ route('admin.login-logs.index') }}" class="card border shadow-sm h-100 text-decoration-none quick-link-card">
                                            <div class="card-body text-center p-3">
                                                <div class="avatar avatar-md bg-indigo-transparent mx-auto mb-2">
                                                    <i class="fas fa-sign-in-alt fs-20" style="color: #6610f2;"></i>
                                                </div>
                                                <h6 class="mb-0 fw-semibold">سجلات الدخول</h6>
                                                <small class="text-muted">سجلات تسجيل الدخول</small>
                                            </div>
                                        </a>
                                    </div>

                                    <!-- الإعدادات -->
                                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                        <a href="{{ route('admin.settings.index') }}" class="card border shadow-sm h-100 text-decoration-none quick-link-card">
                                            <div class="card-body text-center p-3">
                                                <div class="avatar avatar-md bg-gray-transparent mx-auto mb-2">
                                                    <i class="fas fa-cog fs-20 text-muted"></i>
                                                </div>
                                                <h6 class="mb-0 fw-semibold">الإعدادات</h6>
                                                <small class="text-muted">إعدادات النظام</small>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end اختصارات سريعة -->

                <!-- row opened -->
                <div class="row mb-4">
                    <div class="col-md-12 col-lg-8">
                        <!-- آخر التحديثات والأنشطة -->
                        <div class="card">
                            <div class="card-header pb-0">
                                <div class="d-flex justify-content-between">
                                    <h4 class="card-title mb-0">آخر التحديثات والأنشطة</h4>
                                    <a href="javascript:void(0);" class="btn btn-icon btn-sm btn-light bg-transparent rounded-pill" data-bs-toggle="dropdown">
                                        <i class="fe fe-more-horizontal"></i>
                                    </a>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('admin.enrollments.index') }}">عرض جميع الانضمامات</a>
                                        <a class="dropdown-item" href="{{ route('admin.quiz-attempts.needs-grading') }}">عرض جميع المحاولات</a>
                                        <a class="dropdown-item" href="{{ route('users.index') }}">عرض جميع المستخدمين</a>
                                    </div>
                                </div>
                                <p class="fs-12 text-muted mb-0">آخر الأنشطة والأحداث في النظام</p>
                            </div>
                            <div class="card-body">
                                @if(!empty($recent_activities) && count($recent_activities) > 0)
                                    <div class="product-timeline">
                                        <ul class="timeline-1 mb-0">
                                            @foreach($recent_activities as $activity)
                                                <li class="mt-0">
                                                    <i class="fe fe-{{ $activity['icon'] ?? 'activity' }} bg-{{ $activity['color'] ?? 'primary' }}-gradient text-fixed-white product-icon"></i>
                                                    <span class="fw-medium mb-4 fs-14">{{ $activity['title'] ?? 'نشاط' }}</span>
                                                    <a href="{{ $activity['url'] ?? '#' }}" class="float-end fs-11 text-muted">
                                                        {{ \Carbon\Carbon::parse($activity['time'])->diffForHumans() }}
                                                    </a>
                                                    <p class="mb-0 text-muted fs-12">{{ $activity['description'] ?? '' }}</p>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <p class="text-muted">لا توجد أنشطة حديثة</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 col-lg-4">
                        <!-- إحصائيات سريعة -->
                        <div class="card">
                            <div class="card-header pb-1">
                                <h3 class="card-title mb-2">إحصائيات سريعة</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <div class="d-flex justify-content-between align-items-center pb-2">
                                            <span class="fs-13 text-muted">محاولات الاختبارات</span>
                                            <span class="fw-bold">{{ number_format($stats['total_quiz_attempts'] ?? 0) }}</span>
                                        </div>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-primary-gradient" 
                                                 style="width: {{ $stats['total_quiz_attempts'] > 0 ? min(100, (($stats['completed_quiz_attempts'] ?? 0) / $stats['total_quiz_attempts']) * 100) : 0 }}%" 
                                                 role="progressbar"></div>
                                        </div>
                                        <small class="text-muted">{{ $stats['completed_quiz_attempts'] ?? 0 }} مكتملة</small>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <div class="d-flex justify-content-between align-items-center pb-2">
                                            <span class="fs-13 text-muted">متوسط الدرجات</span>
                                            <span class="fw-bold">{{ number_format($stats['avg_quiz_score'] ?? 0, 1) }}%</span>
                                        </div>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-success-gradient" 
                                                 style="width: {{ $stats['avg_quiz_score'] ?? 0 }}%" 
                                                 role="progressbar"></div>
                                        </div>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <div class="d-flex justify-content-between align-items-center pb-2">
                                            <span class="fs-13 text-muted">الانضمامات المعلقة</span>
                                            <span class="fw-bold text-warning">{{ number_format($stats['pending_enrollments'] ?? 0) }}</span>
                                        </div>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <div class="d-flex justify-content-between align-items-center pb-2">
                                            <span class="fs-13 text-muted">الجلسات النشطة</span>
                                            <span class="fw-bold text-success">{{ number_format($stats['active_sessions'] ?? 0) }}</span>
                                        </div>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <div class="d-flex justify-content-between align-items-center pb-2">
                                            <span class="fs-13 text-muted">المعلمون</span>
                                            <span class="fw-bold">{{ number_format($stats['total_teachers'] ?? 0) }}</span>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center pb-2">
                                            <span class="fs-13 text-muted">الصفوف الدراسية</span>
                                            <span class="fw-bold">{{ number_format($stats['total_classes'] ?? 0) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- row closed -->

                <!-- row opened -->
                <div class="row">
                    <div class="col-md-12 col-lg-6">
                        <!-- إحصائيات الانضمامات -->
                        <div class="card">
                            <div class="card-header pb-0">
                                <h4 class="card-title mb-0">إحصائيات الانضمامات</h4>
                                <p class="fs-12 text-muted mb-0">حالة الانضمامات في النظام</p>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-4 text-center">
                                        <h3 class="fw-bold text-success">{{ number_format($stats['active_enrollments'] ?? 0) }}</h3>
                                        <p class="mb-0 fs-12 text-muted">نشط</p>
                                    </div>
                                    <div class="col-4 text-center">
                                        <h3 class="fw-bold text-warning">{{ number_format($stats['pending_enrollments'] ?? 0) }}</h3>
                                        <p class="mb-0 fs-12 text-muted">معلق</p>
                                    </div>
                                    <div class="col-4 text-center">
                                        <h3 class="fw-bold text-primary">{{ number_format($stats['total_enrollments'] ?? 0) }}</h3>
                                        <p class="mb-0 fs-12 text-muted">إجمالي</p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('admin.enrollments.pending') }}" class="btn btn-sm btn-warning">
                                        <i class="fe fe-clock me-1"></i>
                                        عرض الانضمامات المعلقة ({{ $stats['pending_enrollments'] ?? 0 }})
                                    </a>
                                    <a href="{{ route('admin.enrollments.index') }}" class="btn btn-sm btn-primary ms-2">
                                        <i class="fe fe-list me-1"></i>
                                        عرض الكل
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 col-lg-6">
                        <!-- إحصائيات الاختبارات -->
                        <div class="card">
                            <div class="card-header pb-0">
                                <h4 class="card-title mb-0">إحصائيات الاختبارات</h4>
                                <p class="fs-12 text-muted mb-0">أداء الاختبارات والمحاولات</p>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <h5 class="fw-bold mb-0">{{ number_format($stats['total_quiz_attempts'] ?? 0) }}</h5>
                                                <p class="mb-0 fs-12 text-muted">إجمالي المحاولات</p>
                                            </div>
                                            <div class="ms-auto">
                                                <i class="fe fe-file-text fs-24 text-primary"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <h5 class="fw-bold mb-0">{{ number_format($stats['completed_quiz_attempts'] ?? 0) }}</h5>
                                                <p class="mb-0 fs-12 text-muted">مكتملة</p>
                                            </div>
                                            <div class="ms-auto">
                                                <i class="fe fe-check-circle fs-24 text-success"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <h5 class="fw-bold mb-0">{{ number_format($stats['today_quiz_attempts'] ?? 0) }}</h5>
                                                <p class="mb-0 fs-12 text-muted">اليوم</p>
                                            </div>
                                            <div class="ms-auto">
                                                <i class="fe fe-calendar fs-24 text-info"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <h5 class="fw-bold mb-0">{{ number_format($stats['avg_quiz_score'] ?? 0, 1) }}%</h5>
                                                <p class="mb-0 fs-12 text-muted">متوسط الدرجات</p>
                                            </div>
                                            <div class="ms-auto">
                                                <i class="fe fe-trending-up fs-24 text-warning"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('admin.quiz-attempts.needs-grading') }}" class="btn btn-sm btn-primary">
                                        <i class="fe fe-list me-1"></i>
                                        عرض جميع المحاولات
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- row closed -->

            </div>
        </div>
        <!-- End::app-content -->
@stop

@section('js')
<style>
    .quick-link-card {
        transition: all 0.3s ease;
        border-color: #e9ecef !important;
    }
    .quick-link-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        border-color: #007bff !important;
        text-decoration: none;
    }
    .quick-link-card:hover .avatar {
        transform: scale(1.1);
    }
    .quick-link-card .avatar {
        transition: transform 0.3s ease;
    }
</style>
@stop
