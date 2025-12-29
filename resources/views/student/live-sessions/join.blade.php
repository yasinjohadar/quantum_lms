@extends('student.layouts.master')

@section('page-title')
    الانضمام إلى الجلسة الحية - {{ $liveSession->title }}
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">الانضمام إلى الجلسة الحية</h4>
                <p class="mb-0 text-muted">{{ $liveSession->title }}</p>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div id="zoom-container" class="zoom-container" style="min-height: 600px;">
                            <div class="text-center py-5" id="loading-message">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">جاري التحميل...</span>
                                </div>
                                <p class="mt-3">جاري تحميل الجلسة...</p>
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

@push('scripts')
<script src="https://source.zoom.us/zoom.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let zoomClient;
    let meetingSDK;
    let meetingNumber = '{{ $zoomMeeting->zoom_meeting_id }}';
    let userName = '{{ $user->name }}';
    let userEmail = '{{ $user->email }}';
    let sdkKey = '{{ config("zoom.sdk_key") }}';

    // Get join token and signature
    fetch('{{ route("student.live-sessions.zoom.join-token", $liveSession->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            document.getElementById('loading-message').innerHTML = 
                '<div class="alert alert-danger">' + (data.message || 'فشل في الحصول على بيانات الانضمام') + '</div>';
            return;
        }

        const joinData = data.data;
        meetingNumber = joinData.meetingNumber;
        userName = joinData.userName;
        userEmail = joinData.userEmail;

        // Initialize Zoom Meeting SDK
        ZoomMtg.preLoadWasm();
        ZoomMtg.prepareWebSDK();

        ZoomMtg.init({
            leaveOnPageUnload: true,
            patchJsMedia: true,
            success: function() {
                ZoomMtg.join({
                    signature: joinData.signature,
                    meetingNumber: meetingNumber,
                    userName: userName,
                    userEmail: userEmail,
                    passWord: joinData.passcode || '',
                    sdkKey: joinData.sdkKey,
                    success: function(res) {
                        console.log('Joined successfully');
                        document.getElementById('loading-message').style.display = 'none';
                        
                        // Notify backend of join
                        fetch('{{ route("student.live-sessions.zoom.on-join", $liveSession->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            credentials: 'same-origin'
                        });
                    },
                    error: function(res) {
                        console.error('Join error:', res);
                        document.getElementById('loading-message').innerHTML = 
                            '<div class="alert alert-danger">فشل في الانضمام إلى الجلسة: ' + (res.reason || 'خطأ غير معروف') + '</div>';
                    }
                });
            },
            error: function(res) {
                console.error('Init error:', res);
                document.getElementById('loading-message').innerHTML = 
                    '<div class="alert alert-danger">فشل في تهيئة Zoom SDK</div>';
            }
        });

        // Handle leave event
        window.addEventListener('beforeunload', function() {
            fetch('{{ route("student.live-sessions.zoom.on-leave", $liveSession->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'same-origin'
            });
        });
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('loading-message').innerHTML = 
            '<div class="alert alert-danger">حدث خطأ أثناء تحميل الجلسة</div>';
    });
});
</script>
@endpush

