@extends('student.layouts.master')

@section('page-title')
    تفاصيل الجلسة الحية - {{ $liveSession->title }}
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">تفاصيل الجلسة الحية</h4>
                <p class="mb-0 text-muted">{{ $liveSession->title }}</p>
            </div>
            <div>
                <a href="{{ route('student.live-sessions.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3">معلومات الجلسة</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>العنوان:</strong> {{ $liveSession->title }}</p>
                                <p><strong>المادة/الدرس:</strong>
                                    @if($liveSession->sessionable_type === \App\Models\Subject::class)
                                        <span class="badge bg-info">{{ $liveSession->sessionable->name ?? 'N/A' }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $liveSession->sessionable->title ?? 'N/A' }}</span>
                                    @endif
                                </p>
                                <p><strong>تاريخ ووقت الجلسة:</strong> {{ $liveSession->scheduled_at->format('Y-m-d H:i') }}</p>
                                <p><strong>المدة:</strong> {{ $liveSession->duration_minutes }} دقيقة</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>الحالة:</strong>
                                    @if($liveSession->status === 'scheduled')
                                        <span class="badge bg-primary">مجدولة</span>
                                    @elseif($liveSession->status === 'live')
                                        <span class="badge bg-success">جارية</span>
                                    @elseif($liveSession->status === 'completed')
                                        <span class="badge bg-secondary">مكتملة</span>
                                    @else
                                        <span class="badge bg-danger">ملغاة</span>
                                    @endif
                                </p>
                                @if($liveSession->description)
                                <p><strong>الوصف:</strong></p>
                                <p>{{ $liveSession->description }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if($attendanceLog)
                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="mb-3">سجل الحضور</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>وقت الانضمام:</strong> {{ $attendanceLog->joined_at->format('Y-m-d H:i:s') }}</p>
                                <p><strong>وقت المغادرة:</strong> {{ $attendanceLog->left_at ? $attendanceLog->left_at->format('Y-m-d H:i:s') : 'لا يزال نشطاً' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>المدة:</strong> {{ $attendanceLog->formatted_duration ?? 'N/A' }}</p>
                                <p><strong>الحالة:</strong> 
                                    <span class="badge bg-{{ $attendanceLog->isActive() ? 'success' : 'primary' }}">
                                        {{ $attendanceLog->isActive() ? 'نشط' : 'مكتمل' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3">الإجراءات</h5>
                        @if($canJoinNow && $liveSession->zoomMeeting)
                            <a href="{{ route('student.live-sessions.zoom.join', $liveSession->id) }}" class="btn btn-success btn-lg w-100 mb-2">
                                <i class="fas fa-video me-2"></i> الانضمام إلى الجلسة
                            </a>
                        @elseif($liveSession->status === 'cancelled')
                            <div class="alert alert-danger">
                                هذه الجلسة ملغاة
                            </div>
                        @elseif(!$liveSession->zoomMeeting)
                            <div class="alert alert-warning">
                                لم يتم إنشاء Zoom Meeting بعد
                            </div>
                        @elseif(!$liveSession->isWithinTimeWindow())
                            <div class="alert alert-info">
                                الجلسة غير متاحة للانضمام حالياً
                                <br>
                                <small>
                                    متاحة من: {{ $liveSession->getTimeWindowStart()->format('Y-m-d H:i') }}
                                    <br>
                                    إلى: {{ $liveSession->getTimeWindowEnd()->format('Y-m-d H:i') }}
                                </small>
                            </div>
                        @endif

                        <a href="{{ route('student.attendance.show', $liveSession->id) }}" class="btn btn-info w-100">
                            <i class="fas fa-calendar-check me-2"></i> عرض سجل الحضور
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End::app-content -->
@endsection


