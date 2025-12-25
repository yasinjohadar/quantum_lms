@extends('student.layouts.master')

@section('page-title')
    الملف الشخصي
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">الملف الشخصي</h5>
            </div>
        </div>

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

        <div class="row">
            <!-- معلومات المستخدم الأساسية -->
            <div class="col-xl-4 col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <div class="profile-img mb-3">
                            @if($user->photo)
                                <img src="{{ asset('storage/' . $user->photo) }}" alt="صورة المستخدم" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center" style="width: 150px; height: 150px; font-size: 48px; color: white;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <h4 class="mb-1">{{ $user->name }}</h4>
                        <p class="text-muted mb-3">{{ $user->email }}</p>
                        
                        @if($user->phone)
                            <p class="text-muted mb-3">
                                <i class="fas fa-phone me-2"></i>{{ $user->phone }}
                            </p>
                        @endif

                        <div class="d-flex justify-content-center gap-2">
                            <!-- زر التعديل معطل مؤقتاً -->
                            <button type="button" class="btn btn-primary btn-sm" disabled>
                                <i class="fas fa-edit me-1"></i> تعديل الملف الشخصي
                            </button>
                        </div>
                    </div>
                </div>

                <!-- الإحصائيات -->
                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0">الإحصائيات</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <h4 class="mb-0 text-primary">{{ $generalStats['total_subjects'] }}</h4>
                                <small class="text-muted">المواد</small>
                            </div>
                            <div class="col-6 mb-3">
                                <h4 class="mb-0 text-success">{{ $generalStats['active_enrollments'] }}</h4>
                                <small class="text-muted">الانضمامات النشطة</small>
                            </div>
                            <div class="col-6 mb-3">
                                <h4 class="mb-0 text-info">{{ $quizStats['total_attempts'] }}</h4>
                                <small class="text-muted">محاولات الاختبارات</small>
                            </div>
                            <div class="col-6 mb-3">
                                <h4 class="mb-0 text-warning">{{ $quizStats['passed_attempts'] }}</h4>
                                <small class="text-muted">اختبارات نجحت</small>
                            </div>
                            @if($quizStats['average_score'] > 0)
                            <div class="col-12">
                                <h4 class="mb-0 text-danger">{{ number_format($quizStats['average_score'], 1) }}%</h4>
                                <small class="text-muted">متوسط النقاط</small>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- المحتوى الرئيسي -->
            <div class="col-xl-8 col-lg-12">
                <!-- المواد المسجلة -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0">المواد المسجلة</h6>
                    </div>
                    <div class="card-body">
                        @if($user->subjects->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>المادة</th>
                                            <th>الصف</th>
                                            <th>المرحلة</th>
                                            <th>الحالة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->subjects as $subject)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('student.subjects.show', $subject->id) }}" class="text-decoration-none">
                                                        {{ $subject->name }}
                                                    </a>
                                                </td>
                                                <td>{{ $subject->schoolClass->name ?? '-' }}</td>
                                                <td>{{ $subject->schoolClass->stage->name ?? '-' }}</td>
                                                <td>
                                                    @php
                                                        $enrollment = $user->enrollments->where('subject_id', $subject->id)->first();
                                                    @endphp
                                                    @if($enrollment)
                                                        <span class="badge bg-{{ $enrollment->status === 'active' ? 'success' : ($enrollment->status === 'suspended' ? 'warning' : 'secondary') }}">
                                                            {{ $enrollment->status === 'active' ? 'نشط' : ($enrollment->status === 'suspended' ? 'معلق' : 'مكتمل') }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">غير محدد</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle me-2"></i>
                                لا توجد مواد مسجلة حالياً.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- آخر محاولات الاختبارات -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0">آخر محاولات الاختبارات</h6>
                    </div>
                    <div class="card-body">
                        @if($quizStats['recent_attempts']->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>الاختبار</th>
                                            <th>المادة</th>
                                            <th>النقاط</th>
                                            <th>الحالة</th>
                                            <th>التاريخ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($quizStats['recent_attempts'] as $attempt)
                                            <tr>
                                                <td>{{ $attempt->quiz->title ?? '-' }}</td>
                                                <td>{{ $attempt->quiz->subject->name ?? '-' }}</td>
                                                <td>
                                                    @if($attempt->percentage !== null)
                                                        <span class="badge bg-{{ $attempt->passed ? 'success' : 'danger' }}">
                                                            {{ number_format($attempt->percentage, 1) }}%
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $attempt->status === 'completed' ? 'success' : ($attempt->status === 'in_progress' ? 'warning' : 'secondary') }}">
                                                        {{ $attempt->status === 'completed' ? 'مكتمل' : ($attempt->status === 'in_progress' ? 'قيد التنفيذ' : 'منتهي') }}
                                                    </span>
                                                </td>
                                                <td>{{ $attempt->started_at->format('Y-m-d H:i') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle me-2"></i>
                                لا توجد محاولات اختبارات حتى الآن.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- معلومات الدخول الأخيرة -->
                @if($user->loginLogs->count() > 0)
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0">جلسات الدخول الأخيرة</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>IP</th>
                                        <th>الجهاز</th>
                                        <th>المتصفح</th>
                                        <th>الحالة</th>
                                        <th>التاريخ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->loginLogs->take(5) as $log)
                                        <tr>
                                            <td>{{ $log->ip_address }}</td>
                                            <td>{{ $log->device_type ?? '-' }} - {{ $log->platform ?? '-' }}</td>
                                            <td>{{ $log->browser ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $log->is_successful ? 'success' : 'danger' }}">
                                                    {{ $log->is_successful ? 'ناجح' : 'فاشل' }}
                                                </span>
                                            </td>
                                            <td>{{ $log->login_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

