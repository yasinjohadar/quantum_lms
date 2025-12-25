
@extends('student.layouts.master')

@section('page-title')
لوحة التحكم
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div>
                    <h4 class="mb-0">مرحباً بك! 👋</h4>
                    <p class="mb-0 text-muted">{{ now()->translatedFormat('l، d F Y') }}</p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <!-- إجمالي المحاولات -->
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3 mb-xl-0">
                    <div class="card overflow-hidden sales-card bg-danger-gradient h-100">
                        <div class="px-3 pt-3 pb-2 pt-0">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="mb-2 fs-12 text-fixed-white">إجمالي المحاولات</h6>
                                    <h3 class="mb-1 text-fixed-white">{{ number_format($totalQuizAttempts ?? 0) }}</h3>
                                    <p class="mb-0 fs-11 text-fixed-white op-7">جميع محاولات الاختبارات</p>
                                </div>
                                <div class="ms-auto">
                                    <i class="fe fe-file-text fs-32 text-fixed-white op-5"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- اختبارات ناجحة -->
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3 mb-xl-0">
                    <div class="card overflow-hidden sales-card bg-warning-gradient h-100">
                        <div class="px-3 pt-3 pb-2 pt-0">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="mb-2 fs-12 text-fixed-white">اختبارات ناجحة</h6>
                                    <h3 class="mb-1 text-fixed-white">{{ number_format($passedQuizAttempts ?? 0) }}</h3>
                                    <p class="mb-0 fs-11 text-fixed-white op-7">اختبارات تم النجاح فيها</p>
                                </div>
                                <div class="ms-auto">
                                    <i class="fe fe-award fs-32 text-fixed-white op-5"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- متوسط درجات الاختبارات -->
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3 mb-xl-0">
                    <div class="card overflow-hidden sales-card bg-info-gradient h-100">
                        <div class="px-3 pt-3 pb-2 pt-0">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="mb-2 fs-12 text-fixed-white">متوسط درجات الاختبارات</h6>
                                    <h3 class="mb-1 text-fixed-white">{{ number_format($avgQuizScore ?? 0, 1) }}%</h3>
                                    <p class="mb-0 fs-11 text-fixed-white op-7">متوسط أدائك في الاختبارات</p>
                                </div>
                                <div class="ms-auto">
                                    <i class="fe fe-graduation-cap fs-32 text-fixed-white op-5"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- إجمالي الكورسات المسجلة -->
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3 mb-xl-0">
                    <div class="card overflow-hidden sales-card bg-primary-gradient h-100">
                        <div class="px-3 pt-3 pb-2 pt-0">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="mb-2 fs-12 text-fixed-white">إجمالي الكورسات المسجلة</h6>
                                    <h3 class="mb-1 text-fixed-white">{{ number_format($totalEnrollments ?? 0) }}</h3>
                                    <p class="mb-0 fs-11 text-fixed-white op-7">المواد الدراسية المسجلة</p>
                                </div>
                                <div class="ms-auto">
                                    <i class="fe fe-book-open fs-32 text-fixed-white op-5"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="row mb-4">
                <div class="col-12 mb-3">
                    <h5 class="mb-0">روابط سريعة</h5>
                    <p class="text-muted fs-12 mb-0">الوصول السريع لأقسام النظام</p>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
                    <a href="{{ route('student.subjects') }}" class="card text-center h-100 text-decoration-none">
                        <div class="card-body">
                            <div class="fs-32 mb-2 text-primary">
                                <i class="fe fe-book"></i>
                            </div>
                            <h6 class="mb-1">كورساتي</h6>
                            <p class="text-muted fs-12 mb-0">عرض جميع المواد والدروس</p>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
                    <a href="{{ route('student.quizzes.index') }}" class="card text-center h-100 text-decoration-none">
                        <div class="card-body">
                            <div class="fs-32 mb-2 text-success">
                                <i class="fe fe-edit"></i>
                            </div>
                            <h6 class="mb-1">إحصائيات الاختبارات</h6>
                            <p class="text-muted fs-12 mb-0">عرض نتائج وتقارير الاختبارات</p>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
                    <a href="{{ route('student.progress.index') }}" class="card text-center h-100 text-decoration-none">
                        <div class="card-body">
                            <div class="fs-32 mb-2 text-info">
                                <i class="fe fe-trending-up"></i>
                            </div>
                            <h6 class="mb-1">تقدمي في الكورسات</h6>
                            <p class="text-muted fs-12 mb-0">متابعة تقدمك في المواد</p>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
                    <a href="{{ route('student.gamification.dashboard') }}" class="card text-center h-100 text-decoration-none">
                        <div class="card-body">
                            <div class="fs-32 mb-2 text-warning">
                                <i class="fe fe-award"></i>
                            </div>
                            <h6 class="mb-1">لوحة التلعيب</h6>
                            <p class="text-muted fs-12 mb-0">النقاط والإنجازات والشارات</p>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
                    <a href="{{ route('student.gamification.badges') }}" class="card text-center h-100 text-decoration-none">
                        <div class="card-body">
                            <div class="fs-32 mb-2 text-danger">
                                <i class="fe fe-star"></i>
                            </div>
                            <h6 class="mb-1">شاراتي</h6>
                            <p class="text-muted fs-12 mb-0">عرض جميع الشارات المكتسبة</p>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
                    <a href="{{ route('student.gamification.leaderboard') }}" class="card text-center h-100 text-decoration-none">
                        <div class="card-body">
                            <div class="fs-32 mb-2 text-purple">
                                <i class="fe fe-bar-chart"></i>
                            </div>
                            <h6 class="mb-1">لوحة المتصدرين</h6>
                            <p class="text-muted fs-12 mb-0">ترتيبك بين الطلاب</p>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
                    <a href="{{ route('student.assignments.index') }}" class="card text-center h-100 text-decoration-none">
                        <div class="card-body">
                            <div class="fs-32 mb-2 text-primary">
                                <i class="fe fe-clipboard"></i>
                            </div>
                            <h6 class="mb-1">الواجبات</h6>
                            <p class="text-muted fs-12 mb-0">إدارة وتقديم الواجبات</p>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
                    <a href="{{ route('student.library.index') }}" class="card text-center h-100 text-decoration-none">
                        <div class="card-body">
                            <div class="fs-32 mb-2 text-info">
                                <i class="fe fe-book-open"></i>
                            </div>
                            <h6 class="mb-1">المكتبة الرقمية</h6>
                            <p class="text-muted fs-12 mb-0">الكتب والموارد التعليمية</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12 mb-3">
                    <h5 class="mb-0">
                        <i class="fe fe-zap me-2 text-warning"></i>
                        إجراءات سريعة
                    </h5>
                    <p class="text-muted fs-12 mb-0">الوصول السريع لإجراءات نظام التحفيز</p>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
                    <a href="{{ route('student.gamification.challenges') }}" class="card text-center h-100 text-decoration-none">
                        <div class="card-body">
                            <div class="fs-32 mb-2 text-danger">
                                <i class="fe fe-zap"></i>
                            </div>
                            <h6 class="mb-1">التحديات</h6>
                            <p class="text-muted fs-12 mb-0">عرض التحديات النشطة</p>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
                    <a href="{{ route('student.gamification.rewards') }}" class="card text-center h-100 text-decoration-none">
                        <div class="card-body">
                            <div class="fs-32 mb-2 text-primary">
                                <i class="fe fe-gift"></i>
                            </div>
                            <h6 class="mb-1">المكافآت</h6>
                            <p class="text-muted fs-12 mb-0">استبدل نقاطك بمكافآت</p>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
                    <a href="{{ route('student.gamification.certificates') }}" class="card text-center h-100 text-decoration-none">
                        <div class="card-body">
                            <div class="fs-32 mb-2 text-info">
                                <i class="fe fe-file-text"></i>
                            </div>
                            <h6 class="mb-1">الشهادات</h6>
                            <p class="text-muted fs-12 mb-0">عرض وتحميل الشهادات</p>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
                    <a href="{{ route('student.tasks.index') }}" class="card text-center h-100 text-decoration-none">
                        <div class="card-body">
                            <div class="fs-32 mb-2 text-success">
                                <i class="fe fe-check-square"></i>
                            </div>
                            <h6 class="mb-1">المهام</h6>
                            <p class="text-muted fs-12 mb-0">عرض ومتابعة المهام</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Main Content Row -->
            <div class="row">
                <!-- Courses in Progress -->
                <div class="col-xl-7 col-lg-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="card-title mb-0">
                                    <i class="fe fe-book me-2"></i>
                                    الكورسات قيد التقدم
                                </h4>
                                <p class="fs-12 text-muted mb-0">موادك الدراسية والتقدم فيها</p>
                            </div>
                            <a href="{{ route('student.subjects') }}" class="btn btn-sm btn-primary">
                                عرض الكل
                            </a>
                        </div>
                        <div class="card-body">
                            @php
                                $subjectsCollection = collect($topSubjects ?? []);
                            @endphp
                            @if($subjectsCollection->isEmpty())
                                <div class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="fe fe-book-open fs-48 text-muted op-5"></i>
                                    </div>
                                    <h5 class="mb-2">لا توجد كورسات قيد التقدم</h5>
                                    <p class="text-muted mb-3">ابدأ رحلتك التعليمية الآن؟</p>
                                    <a href="{{ route('student.subjects') }}" class="btn btn-primary">
                                        <i class="fe fe-search me-1"></i>
                                        تصفح الكورسات
                                    </a>
                                </div>
                            @else
                                @foreach($subjectsCollection as $item)
                                    @php
                                        $subject = $item['subject'] ?? null;
                                        $p = $item['progress'] ?? [];
                                    @endphp
                                    @if($subject)
                                        <div class="mb-4 pb-4 border-bottom">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">
                                                        <a href="{{ route('student.subjects.show', $subject->id) }}" class="text-dark">
                                                            {{ $subject->name }}
                                                        </a>
                                                    </h6>
                                                    <small class="text-muted">
                                                        {{ $subject->schoolClass->name ?? '' }}
                                                        @if(optional($subject->schoolClass)->stage)
                                                            - {{ $subject->schoolClass->stage->name }}
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
                                                     style="width: {{ $p['overall_percentage'] ?? 0 }}%;"
                                                     aria-valuenow="{{ $p['overall_percentage'] ?? 0 }}"
                                                     aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="d-flex justify-content-between fs-11 text-muted">
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

                <!-- Right Sidebar -->
                <div class="col-xl-5 col-lg-12">
                    <!-- Important Notifications -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h4 class="card-title mb-0">
                                <i class="fe fe-bell me-2"></i>
                                تنبيهات مهمة
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="text-center py-4">
                                <div class="mb-2">
                                    <i class="fe fe-check-circle fs-32 text-success"></i>
                                </div>
                                <p class="text-muted mb-0">لا توجد تنبيهات جديدة</p>
                                <a href="{{ route('student.notifications.index') }}" class="btn btn-sm btn-outline-primary mt-3">
                                    عرض جميع الإشعارات
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Latest Badges -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h4 class="card-title mb-0">
                                <i class="fe fe-star me-2"></i>
                                آخر الشارات
                            </h4>
                        </div>
                        <div class="card-body">
                            @if(($latestBadges ?? collect())->isEmpty())
                                <div class="text-center py-4">
                                    <div class="mb-2">
                                        <i class="fe fe-award fs-32 text-muted op-5"></i>
                                    </div>
                                    <p class="text-muted mb-0">لم تحصل على شارات بعد</p>
                                    <a href="{{ route('student.gamification.badges') }}" class="btn btn-sm btn-outline-primary mt-3">
                                        عرض الشارات
                                    </a>
                                </div>
                            @else
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($latestBadges as $userBadge)
                                        @if($userBadge->badge)
                                            <div class="badge-item text-center p-2 border rounded">
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

                    <!-- Quick Statistics -->
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">
                                <i class="fe fe-bar-chart me-2"></i>
                                إحصائيات سريعة
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fs-13 text-muted">الواجبات المسلمة</span>
                                    <span class="fw-bold">{{ count($upcomingAssignments ?? []) }}/{{ count($upcomingAssignments ?? []) }}</span>
                                </div>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-success" style="width: 100%;" role="progressbar"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fs-13 text-muted">متوسط التقدم</span>
                                    <span class="fw-bold">{{ number_format($overallAverage ?? 0, 1) }}%</span>
                                </div>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-primary" style="width: {{ $overallAverage ?? 0 }}%;" role="progressbar"></div>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fs-13 text-muted">إجمالي النقاط</span>
                                    <span class="fw-bold text-success">{{ number_format($totalPoints ?? 0) }}</span>
                                </div>
                            </div>
                            <div class="mt-3 pt-3 border-top">
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
    </div>
@stop

@push('styles')
<style>
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .badge-item {
        transition: transform 0.2s;
        min-width: 80px;
    }
    .badge-item:hover {
        transform: scale(1.05);
    }
    .text-purple {
        color: #6f42c1;
    }
</style>
@endpush
