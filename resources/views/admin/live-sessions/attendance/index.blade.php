@extends('admin.layouts.master')

@section('page-title')
    قائمة الحضور - {{ $liveSession->title }}
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">قائمة الحضور</h4>
                <p class="mb-0 text-muted">{{ $liveSession->title }}</p>
            </div>
            <div>
                <a href="{{ route('admin.live-sessions.attendance.export', [$liveSession->id, 'excel']) }}" class="btn btn-success">تصدير Excel</a>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">إجمالي المسجلين</h6>
                        <h3 class="mb-0">{{ $stats['total_enrolled'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">الحاضرين</h6>
                        <h3 class="mb-0 text-success">{{ $stats['attended_count'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">الغائبين</h6>
                        <h3 class="mb-0 text-danger">{{ $stats['absent_count'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">نسبة الحضور</h6>
                        <h3 class="mb-0">{{ number_format($stats['attendance_percentage'], 1) }}%</h3>
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
                                        <th>اسم الطالب</th>
                                        <th>البريد الإلكتروني</th>
                                        <th>وقت الانضمام</th>
                                        <th>وقت المغادرة</th>
                                        <th>المدة</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($attendance as $log)
                                        <tr>
                                            <td>{{ $log->user->name }}</td>
                                            <td>{{ $log->user->email }}</td>
                                            <td>{{ $log->joined_at->format('Y-m-d H:i:s') }}</td>
                                            <td>{{ $log->left_at ? $log->left_at->format('Y-m-d H:i:s') : 'لا يزال نشطاً' }}</td>
                                            <td>{{ $log->formatted_duration ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $log->isActive() ? 'success' : 'primary' }}">
                                                    {{ $log->isActive() ? 'نشط' : 'مكتمل' }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.live-sessions.attendance.show', [$liveSession->id, $log->user->id]) }}" class="btn btn-sm btn-info">التفاصيل</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">لا توجد سجلات حضور</td>
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




