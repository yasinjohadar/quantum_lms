
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
                </div>
                <!-- End Page Header -->

                <!-- الكاردات الملونة: للأدمن فقط -->
                @if(auth()->check() && auth()->user()->hasRole('admin'))
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
                @endif
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
                                    @can('user-list')
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
                                    @endcan

                                    <!-- المواد -->
                                    @can('subject-list')
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
                                    @endcan

                                    <!-- الاختبارات -->
                                    @can('quiz-list')
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
                                    @endcan

                                    <!-- الانضمامات -->
                                    @can('enrollment-list')
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
                                    @endcan

                                    <!-- التقارير -->
                                    @can('report-view')
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
                                    @endcan

                                    <!-- المكتبة -->
                                    @can('library-list')
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
                                    @endcan

                                    <!-- جلسات المستخدمين -->
                                    @can('user-login-logs')
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
                                    @endcan

                                    <!-- النسخ الاحتياطية -->
                                    @can('settings-manage')
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
                                    @endcan

                                    <!-- نماذج AI -->
                                    @can('question-create')
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
                                    @endcan

                                    <!-- التقويم -->
                                    @can('calendar-list')
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
                                    @endcan

                                    <!-- سجلات الدخول -->
                                    @can('user-login-logs')
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
                                    @endcan

                                    <!-- الإعدادات -->
                                    @can('settings-manage')
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
