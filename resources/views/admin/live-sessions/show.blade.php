@extends('admin.layouts.master')

@section('page-title')
    تفاصيل الجلسة الحية - {{ $liveSession->title }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">تفاصيل الجلسة الحية</h4>
                <p class="mb-0 text-muted">{{ $liveSession->title }}</p>
            </div>
            <div>
                <a href="{{ route('admin.live-sessions.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
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
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">معلومات الجلسة</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>العنوان:</strong> {{ $liveSession->title }}
                            </div>
                            <div class="col-md-6">
                                <strong>الحالة:</strong>
                                @if($liveSession->status === 'scheduled')
                                    <span class="badge bg-primary">مجدولة</span>
                                @elseif($liveSession->status === 'live')
                                    <span class="badge bg-success">جارية</span>
                                @elseif($liveSession->status === 'completed')
                                    <span class="badge bg-secondary">مكتملة</span>
                                @else
                                    <span class="badge bg-danger">ملغاة</span>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>المادة/الدرس:</strong>
                                @if($liveSession->sessionable_type === \App\Models\Subject::class)
                                    <span class="badge bg-info">{{ $liveSession->sessionable->name ?? 'N/A' }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ $liveSession->sessionable->title ?? 'N/A' }}</span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <strong>تاريخ ووقت الجلسة:</strong> {{ $liveSession->scheduled_at->format('Y-m-d H:i') }}
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>المدة:</strong> {{ $liveSession->duration_minutes }} دقيقة
                            </div>
                            <div class="col-md-6">
                                <strong>المنطقة الزمنية:</strong> {{ $liveSession->timezone }}
                            </div>
                        </div>

                        @if($liveSession->description)
                        <div class="mb-3">
                            <strong>الوصف:</strong>
                            <p>{{ $liveSession->description }}</p>
                        </div>
                        @endif

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>أنشئ بواسطة:</strong> {{ $liveSession->creator->name ?? 'N/A' }}
                            </div>
                            <div class="col-md-6">
                                <strong>تاريخ الإنشاء:</strong> {{ $liveSession->created_at->format('Y-m-d H:i') }}
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <a href="{{ route('admin.live-sessions.edit', $liveSession) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i> تعديل
                            </a>
                            <form action="{{ route('admin.live-sessions.destroy', $liveSession) }}" 
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('هل أنت متأكد من حذف هذه الجلسة؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash me-1"></i> حذف
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Zoom Meeting Section -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">إدارة Zoom Meeting</h5>
                    </div>
                    <div class="card-body">
                        @if($liveSession->zoomMeeting)
                            <div class="alert alert-info">
                                <strong>حالة الاجتماع:</strong> 
                                <span class="badge bg-{{ $liveSession->zoomMeeting->status === 'created' ? 'primary' : ($liveSession->zoomMeeting->status === 'started' ? 'success' : 'secondary') }}">
                                    {{ $liveSession->zoomMeeting->status }}
                                </span>
                            </div>

                            <div class="mb-3">
                                <strong>معرف الاجتماع:</strong> {{ $liveSession->zoomMeeting->zoom_meeting_id }}
                            </div>
                            <div class="mb-3">
                                <strong>الموضوع:</strong> {{ $liveSession->zoomMeeting->topic }}
                            </div>
                            <div class="mb-3">
                                <strong>وقت البدء:</strong> {{ $liveSession->zoomMeeting->start_time->format('Y-m-d H:i:s') }}
                            </div>
                            <div class="mb-3">
                                <strong>المدة:</strong> {{ $liveSession->zoomMeeting->duration }} دقيقة
                            </div>

                            <div class="d-flex gap-2">
                                <form action="{{ route('admin.live-sessions.zoom.sync', $liveSession->id) }}" method="GET" class="d-inline">
                                    <button type="submit" class="btn btn-primary">مزامنة الحالة</button>
                                </form>
                                <form action="{{ route('admin.live-sessions.zoom.cancel', $liveSession->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من إلغاء الاجتماع؟')">إلغاء الاجتماع</button>
                                </form>
                            </div>
                        @else
                            <p class="text-muted">لم يتم إنشاء Zoom Meeting بعد.</p>
                            <a href="{{ route('admin.live-sessions.zoom.manage', $liveSession->id) }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> إنشاء Zoom Meeting
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">إجراءات سريعة</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if($liveSession->zoomMeeting)
                                <a href="{{ route('admin.live-sessions.zoom.manage', $liveSession->id) }}" class="btn btn-info">
                                    <i class="fas fa-video me-1"></i> إدارة Zoom
                                </a>
                            @else
                                <a href="{{ route('admin.live-sessions.zoom.manage', $liveSession->id) }}" class="btn btn-primary">
                                    <i class="fas fa-video me-1"></i> إنشاء Zoom Meeting
                                </a>
                            @endif

                            <a href="{{ route('admin.live-sessions.attendance.index', $liveSession->id) }}" class="btn btn-success">
                                <i class="fas fa-users me-1"></i> عرض الحضور
                            </a>

                            <a href="{{ route('admin.live-sessions.edit', $liveSession) }}" class="btn btn-warning">
                                <i class="fas fa-edit me-1"></i> تعديل الجلسة
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection




