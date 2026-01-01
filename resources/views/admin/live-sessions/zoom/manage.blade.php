@extends('admin.layouts.master')

@section('page-title')
    إدارة جلسة Zoom - {{ $liveSession->title }}
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">إدارة جلسة Zoom</h4>
                <p class="mb-0 text-muted">{{ $liveSession->title }}</p>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">معلومات الجلسة</h5>
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
                                    @csrf
                                    <button type="submit" class="btn btn-primary">مزامنة الحالة</button>
                                </form>
                                <form action="{{ route('admin.live-sessions.zoom.cancel', $liveSession->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من إلغاء الاجتماع؟')">إلغاء الاجتماع</button>
                                </form>
                            </div>
                        @else
                            <form action="{{ route('admin.live-sessions.zoom.create', $liveSession->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">عنوان الجلسة</label>
                                    <input type="text" name="title" class="form-control" value="{{ $liveSession->title }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">الوصف</label>
                                    <textarea name="description" class="form-control" rows="3">{{ $liveSession->description }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">وقت الجلسة</label>
                                    <input type="datetime-local" name="scheduled_at" class="form-control" value="{{ $liveSession->scheduled_at->format('Y-m-d\TH:i') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">المدة (بالدقائق)</label>
                                    <input type="number" name="duration_minutes" class="form-control" value="{{ $liveSession->duration_minutes }}" min="1" max="480" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">المنطقة الزمنية</label>
                                    <input type="text" name="timezone" class="form-control" value="{{ $liveSession->timezone }}" required>
                                </div>
                                <button type="submit" class="btn btn-primary">إنشاء اجتماع Zoom</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End::app-content -->
@endsection


