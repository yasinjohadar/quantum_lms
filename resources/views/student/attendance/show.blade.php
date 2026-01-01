@extends('student.layouts.master')

@section('page-title')
    تفاصيل الحضور - {{ $liveSession->title }}
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">تفاصيل الحضور</h4>
                <p class="mb-0 text-muted">{{ $liveSession->title }}</p>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3">معلومات الجلسة</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>عنوان الجلسة:</strong> {{ $liveSession->title }}</p>
                                <p><strong>تاريخ الجلسة:</strong> {{ $liveSession->scheduled_at->format('Y-m-d') }}</p>
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
            </div>
        </div>
    </div>
</div>
<!-- End::app-content -->
@endsection



