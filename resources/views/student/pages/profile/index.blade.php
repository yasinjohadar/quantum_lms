@extends('student.layouts.master')

@section('page-title')
    البروفايل الشخصي
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">البروفايل الشخصي</h4>
                <p class="mb-0 text-muted">عرض شامل لجميع بياناتك وإحصائياتك</p>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- row -->
        <div class="row">
            <!-- معلومات الطالب الأساسية -->
            <div class="col-xl-4 col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="main-img-user profile-user mx-auto mb-3">
                                @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                                @else
                                    <div class="avatar avatar-xl bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 120px; height: 120px; font-size: 3rem;">
                                        {{ mb_substr($user->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <h4 class="mb-1">{{ $user->name }}</h4>
                            <p class="text-muted mb-3">طالب</p>
                            <div class="d-flex justify-content-center gap-2">
                                <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                                    {{ $user->is_active ? 'نشط' : 'غير نشط' }}
                                </span>
                                @if($user->email_verified_at)
                                    <span class="badge bg-info">البريد مؤكد</span>
                                @endif
                            </div>
                        </div>

                        <hr>

                        <!-- معلومات الاتصال -->
                        <div class="mb-4">
                            <h6 class="mb-3">معلومات الاتصال</h6>
                            <div class="mb-2">
                                <i class="bi bi-envelope me-2 text-primary"></i>
                                <strong>البريد الإلكتروني:</strong>
                                <div class="text-muted">{{ $user->email }}</div>
                            </div>
                            @if($user->phone)
                            <div class="mb-2">
                                <i class="bi bi-telephone me-2 text-primary"></i>
                                <strong>رقم الهاتف:</strong>
                                <div class="text-muted">{{ $user->phone }}</div>
                            </div>
                            @endif
                            @if($user->last_login_at)
                            <div class="mb-2">
                                <i class="bi bi-clock-history me-2 text-primary"></i>
                                <strong>آخر تسجيل دخول:</strong>
                                <div class="text-muted">{{ $user->last_login_at->format('Y-m-d H:i') }}</div>
                            </div>
                            @endif
                        </div>

                        <hr>

                        <!-- الإحصائيات السريعة -->
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="p-3 bg-light rounded">
                                    <h3 class="mb-0 text-primary">{{ $generalStats['total_subjects'] }}</h3>
                                    <small class="text-muted">المواد</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="p-3 bg-light rounded">
                                    <h3 class="mb-0 text-success">{{ $generalStats['total_groups'] }}</h3>
                                    <small class="text-muted">المجموعات</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="p-3 bg-light rounded">
                                    <h3 class="mb-0 text-info">{{ $quizStats['total_attempts'] }}</h3>
                                    <small class="text-muted">محاولات الاختبارات</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="p-3 bg-light rounded">
                                    <h3 class="mb-0 text-warning">{{ $quizStats['passed_attempts'] }}</h3>
                                    <small class="text-muted">اختبارات ناجحة</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- المحتوى الرئيسي -->
            <div class="col-xl-8 col-lg-12">
                <!-- Tabs -->
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#overview" role="tab">
                                    <i class="bi bi-house me-1"></i> نظرة عامة
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#subjects" role="tab">
                                    <i class="bi bi-book me-1"></i> المواد الدراسية
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#quizzes" role="tab">
                                    <i class="bi bi-clipboard-check me-1"></i> الاختبارات
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#groups" role="tab">
                                    <i class="bi bi-people me-1"></i> المجموعات
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#activity" role="tab">
                                    <i class="bi bi-activity me-1"></i> النشاطات
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- نظرة عامة -->
                            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                                <div class="row">
                                    <!-- إحصائيات الاختبارات -->
                                    <div class="col-md-6 mb-4">
                                        <div class="card border">
                                            <div class="card-body">
                                                <h6 class="card-title mb-3">إحصائيات الاختبارات</h6>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>إجمالي المحاولات:</span>
                                                    <strong>{{ $quizStats['total_attempts'] }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>المكتملة:</span>
                                                    <strong class="text-success">{{ $quizStats['completed_attempts'] }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>الناجحة:</span>
                                                    <strong class="text-primary">{{ $quizStats['passed_attempts'] }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span>متوسط النسبة:</span>
                                                    <strong class="text-info">{{ number_format($quizStats['average_score'], 1) }}%</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- إحصائيات عامة -->
                                    <div class="col-md-6 mb-4">
                                        <div class="card border">
                                            <div class="card-body">
                                                <h6 class="card-title mb-3">إحصائيات عامة</h6>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>المواد المسجلة:</span>
                                                    <strong>{{ $generalStats['total_subjects'] }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>الانضمامات النشطة:</span>
                                                    <strong class="text-success">{{ $generalStats['active_enrollments'] }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>المجموعات:</span>
                                                    <strong>{{ $generalStats['total_groups'] }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span>جلسات الدخول:</span>
                                                    <strong>{{ $generalStats['total_logins'] }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- آخر محاولات الاختبارات -->
                                    <div class="col-12">
                                        <h6 class="mb-3">آخر محاولات الاختبارات</h6>
                                        @if($quizStats['recent_attempts']->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>الاختبار</th>
                                                            <th>المادة</th>
                                                            <th>التاريخ</th>
                                                            <th>النسبة</th>
                                                            <th>الحالة</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($quizStats['recent_attempts'] as $attempt)
                                                        <tr>
                                                            <td>{{ $attempt->quiz->title }}</td>
                                                            <td>{{ $attempt->quiz->subject->name ?? '-' }}</td>
                                                            <td>{{ $attempt->started_at->format('Y-m-d') }}</td>
                                                            <td>
                                                                <span class="badge bg-{{ $attempt->passed ? 'success' : 'danger' }}">
                                                                    {{ number_format($attempt->percentage, 1) }}%
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-{{ $attempt->status_color }}">
                                                                    {{ $attempt->status_name }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="alert alert-info">لا توجد محاولات اختبارات حتى الآن</div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- المواد الدراسية -->
                            <div class="tab-pane fade" id="subjects" role="tabpanel">
                                @if($user->subjects->count() > 0)
                                    <div class="row">
                                        @foreach($user->subjects as $subject)
                                            @php
                                                $enrollment = $user->enrollments()->where('subject_id', $subject->id)->first();
                                            @endphp
                                            <div class="col-md-6 mb-3">
                                                <div class="card border">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <h6 class="mb-0">{{ $subject->name }}</h6>
                                                            <span class="badge bg-{{ $enrollment && $enrollment->status == 'active' ? 'success' : 'secondary' }}">
                                                                {{ $enrollment ? ($enrollment->status == 'active' ? 'نشط' : 'غير نشط') : '-' }}
                                                            </span>
                                                        </div>
                                                        @if($subject->class)
                                                            <p class="text-muted mb-2">
                                                                <i class="bi bi-building me-1"></i>
                                                                {{ $subject->class->name }}
                                                                @if($subject->class->stage)
                                                                    - {{ $subject->class->stage->name }}
                                                                @endif
                                                            </p>
                                                        @endif
                                                        @if($enrollment && $enrollment->enrolled_at)
                                                            <small class="text-muted">
                                                                <i class="bi bi-calendar me-1"></i>
                                                                تاريخ الانضمام: {{ $enrollment->enrolled_at->format('Y-m-d') }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-warning">لم يتم تسجيلك في أي مادة دراسية بعد</div>
                                @endif
                            </div>

                            <!-- الاختبارات -->
                            <div class="tab-pane fade" id="quizzes" role="tabpanel">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body text-center">
                                                <h3>{{ $quizStats['total_attempts'] }}</h3>
                                                <p class="mb-0">إجمالي المحاولات</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-success text-white">
                                            <div class="card-body text-center">
                                                <h3>{{ $quizStats['passed_attempts'] }}</h3>
                                                <p class="mb-0">ناجحة</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-danger text-white">
                                            <div class="card-body text-center">
                                                <h3>{{ $quizStats['completed_attempts'] - $quizStats['passed_attempts'] }}</h3>
                                                <p class="mb-0">فاشلة</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-info text-white">
                                            <div class="card-body text-center">
                                                <h3>{{ number_format($quizStats['average_score'], 1) }}%</h3>
                                                <p class="mb-0">متوسط النسبة</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if($quizStats['recent_attempts']->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>الاختبار</th>
                                                    <th>المادة</th>
                                                    <th>التاريخ</th>
                                                    <th>النتيجة</th>
                                                    <th>النسبة</th>
                                                    <th>الحالة</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($quizStats['recent_attempts'] as $index => $attempt)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $attempt->quiz->title }}</td>
                                                    <td>{{ $attempt->quiz->subject->name ?? '-' }}</td>
                                                    <td>{{ $attempt->started_at->format('Y-m-d H:i') }}</td>
                                                    <td>{{ $attempt->score }} / {{ $attempt->max_score }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $attempt->passed ? 'success' : 'danger' }}">
                                                            {{ number_format($attempt->percentage, 1) }}%
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $attempt->status_color }}">
                                                            {{ $attempt->status_name }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info">لا توجد محاولات اختبارات حتى الآن</div>
                                @endif
                            </div>

                            <!-- المجموعات -->
                            <div class="tab-pane fade" id="groups" role="tabpanel">
                                @if($user->groups->count() > 0)
                                    <div class="row">
                                        @foreach($user->groups as $group)
                                            <div class="col-md-6 mb-3">
                                                <div class="card border">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <h6 class="mb-0">
                                                                <span class="badge me-2" style="background-color: {{ $group->color ?? '#007bff' }};">
                                                                    &nbsp;
                                                                </span>
                                                                {{ $group->name }}
                                                            </h6>
                                                            <span class="badge bg-{{ $group->is_active ? 'success' : 'secondary' }}">
                                                                {{ $group->is_active ? 'نشط' : 'غير نشط' }}
                                                            </span>
                                                        </div>
                                                        @if($group->description)
                                                            <p class="text-muted mb-2">{{ $group->description }}</p>
                                                        @endif
                                                        @if($group->pivot->added_at)
                                                            <small class="text-muted">
                                                                <i class="bi bi-calendar me-1"></i>
                                                                تاريخ الانضمام: {{ \Carbon\Carbon::parse($group->pivot->added_at)->format('Y-m-d') }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-warning">لم يتم إضافتك إلى أي مجموعة بعد</div>
                                @endif
                            </div>

                            <!-- النشاطات -->
                            <div class="tab-pane fade" id="activity" role="tabpanel">
                                <div class="row">
                                    <!-- آخر جلسات الدخول -->
                                    <div class="col-md-6 mb-4">
                                        <h6 class="mb-3">آخر جلسات الدخول</h6>
                                        @if($user->userSessions->count() > 0)
                                            <div class="list-group">
                                                @foreach($user->userSessions as $session)
                                                    <div class="list-group-item">
                                                        <div class="d-flex justify-content-between">
                                                            <div>
                                                                <strong>{{ $session->device_type ?? 'غير محدد' }}</strong>
                                                                <br>
                                                                <small class="text-muted">
                                                                    {{ $session->browser ?? 'غير محدد' }}
                                                                    @if($session->platform)
                                                                        - {{ $session->platform }}
                                                                    @endif
                                                                </small>
                                                            </div>
                                                            <div class="text-end">
                                                                <small class="text-muted">
                                                                    {{ $session->started_at->format('Y-m-d H:i') }}
                                                                </small>
                                                                <br>
                                                                @if($session->is_active)
                                                                    <span class="badge bg-success">نشط</span>
                                                                @else
                                                                    <span class="badge bg-secondary">منتهي</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="alert alert-info">لا توجد جلسات مسجلة</div>
                                        @endif
                                    </div>

                                    <!-- آخر تسجيلات الدخول -->
                                    <div class="col-md-6 mb-4">
                                        <h6 class="mb-3">آخر تسجيلات الدخول</h6>
                                        @if($user->loginLogs->count() > 0)
                                            <div class="list-group">
                                                @foreach($user->loginLogs as $log)
                                                    <div class="list-group-item">
                                                        <div class="d-flex justify-content-between">
                                                            <div>
                                                                <strong>
                                                                    <i class="bi bi-{{ $log->is_successful ? 'check-circle text-success' : 'x-circle text-danger' }} me-1"></i>
                                                                    {{ $log->is_successful ? 'نجح' : 'فشل' }}
                                                                </strong>
                                                                <br>
                                                                <small class="text-muted">
                                                                    {{ $log->device_type ?? 'غير محدد' }} - {{ $log->browser ?? 'غير محدد' }}
                                                                </small>
                                                            </div>
                                                            <div class="text-end">
                                                                <small class="text-muted">
                                                                    {{ $log->login_at->format('Y-m-d H:i') }}
                                                                </small>
                                                                <br>
                                                                @if($log->session_duration_seconds)
                                                                    <small class="text-muted">
                                                                        {{ gmdate('H:i:s', $log->session_duration_seconds) }}
                                                                    </small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="alert alert-info">لا توجد تسجيلات دخول</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- row closed -->
    </div>
    <!-- Container closed -->
</div>
<!-- main-content closed -->
@stop

@section('script')
<script>
    // تفعيل التبويبات
    document.addEventListener('DOMContentLoaded', function() {
        var triggerTabList = [].slice.call(document.querySelectorAll('a[data-bs-toggle="tab"]'));
        triggerTabList.forEach(function (triggerEl) {
            var tabTrigger = new bootstrap.Tab(triggerEl);
            triggerEl.addEventListener('click', function (event) {
                event.preventDefault();
                tabTrigger.show();
            });
        });
    });
</script>
@stop

