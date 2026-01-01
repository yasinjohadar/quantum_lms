@extends('student.layouts.master')

@section('page-title')
    سجل الحضور
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">سجل الحضور</h4>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">إجمالي الجلسات</h6>
                        <h3 class="mb-0">{{ $stats['total_sessions_attended'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">إجمالي الوقت</h6>
                        <h3 class="mb-0">{{ $stats['total_time_formatted'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">متوسط الوقت</h6>
                        <h3 class="mb-0">{{ $stats['average_time_formatted'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>عنوان الجلسة</th>
                                        <th>تاريخ الجلسة</th>
                                        <th>وقت الانضمام</th>
                                        <th>المدة</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($attendance as $log)
                                        <tr>
                                            <td>{{ $log->liveSession->title }}</td>
                                            <td>{{ $log->liveSession->scheduled_at->format('Y-m-d') }}</td>
                                            <td>{{ $log->joined_at->format('H:i:s') }}</td>
                                            <td>{{ $log->formatted_duration ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $log->isActive() ? 'success' : 'primary' }}">
                                                    {{ $log->isActive() ? 'نشط' : 'مكتمل' }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('student.attendance.show', $log->liveSession->id) }}" class="btn btn-sm btn-info">التفاصيل</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">لا توجد سجلات حضور</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End::app-content -->
@endsection


