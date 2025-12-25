@extends('admin.layouts.master')

@section('page-title')
    تفاصيل سجل الدخول
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تفاصيل سجل الدخول</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.login-logs.index') }}">سجلات الدخول</a></li>
                            <li class="breadcrumb-item active" aria-current="page">تفاصيل السجل</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.login-logs.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i> العودة
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">معلومات سجل الدخول</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">معلومات المستخدم</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <th style="width: 150px;">المستخدم:</th>
                                            <td>
                                                @if($log->user)
                                                    <div class="fw-semibold">{{ $log->user->name }}</div>
                                                    <small class="text-muted">{{ $log->user->email }}</small>
                                                @else
                                                    <span class="text-muted">غير مسجل</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>الحالة:</th>
                                            <td>
                                                @if($log->is_successful)
                                                    <span class="badge bg-success-transparent text-success">
                                                        <i class="bi bi-check-circle me-1"></i> ناجحة
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger-transparent text-danger">
                                                        <i class="bi bi-x-circle me-1"></i> فاشلة
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if($log->failure_reason)
                                        <tr>
                                            <th>سبب الفشل:</th>
                                            <td><span class="text-danger">{{ $log->failure_reason }}</span></td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>

                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">معلومات الشبكة</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <th style="width: 150px;">عنوان IP:</th>
                                            <td>
                                                <a href="{{ route('admin.login-logs.ip', $log->ip_address) }}" class="text-decoration-none">
                                                    {{ $log->ip_address }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>البلد:</th>
                                            <td>{{ $log->country ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>المدينة:</th>
                                            <td>{{ $log->city ?? '-' }}</td>
                                        </tr>
                                    </table>
                                </div>

                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">معلومات الجهاز</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <th style="width: 150px;">نوع الجهاز:</th>
                                            <td>{{ $log->device_type ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>المتصفح:</th>
                                            <td>{{ $log->browser ?? '-' }} {{ $log->browser_version ?? '' }}</td>
                                        </tr>
                                        <tr>
                                            <th>النظام:</th>
                                            <td>{{ $log->platform ?? '-' }} {{ $log->platform_version ?? '' }}</td>
                                        </tr>
                                        <tr>
                                            <th>User Agent:</th>
                                            <td><small class="text-muted">{{ \Illuminate\Support\Str::limit($log->user_agent, 100) }}</small></td>
                                        </tr>
                                    </table>
                                </div>

                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">معلومات الجلسة</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <th style="width: 150px;">تاريخ الدخول:</th>
                                            <td>
                                                <div>{{ $log->login_at->format('Y-m-d H:i:s') }}</div>
                                                <small class="text-muted">{{ $log->login_at->diffForHumans() }}</small>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>تاريخ الخروج:</th>
                                            <td>
                                                @if($log->logout_at)
                                                    <div>{{ $log->logout_at->format('Y-m-d H:i:s') }}</div>
                                                    <small class="text-muted">{{ $log->logout_at->diffForHumans() }}</small>
                                                @else
                                                    <span class="badge bg-info-transparent text-info">الجلسة لا تزال نشطة</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>مدة الجلسة:</th>
                                            <td>
                                                @if($log->session_duration)
                                                    {{ $log->session_duration }}
                                                @elseif($log->is_active)
                                                    <span class="text-muted">قيد التشغيل...</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Session ID:</th>
                                            <td><small class="text-muted">{{ $log->session_id ?? '-' }}</small></td>
                                        </tr>
                                    </table>
                                </div>

                                @if($log->meta)
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">معلومات إضافية</h6>
                                    <div class="bg-light p-3 rounded">
                                        <pre class="mb-0">{{ json_encode($log->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop
