@extends('admin.layouts.master')

@section('page-title')
    سجلات الدخول - {{ $ip }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">سجلات الدخول - {{ $ip }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.login-logs.index') }}">سجلات الدخول</a></li>
                            <li class="breadcrumb-item active" aria-current="page">سجلات IP</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.login-logs.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i> العودة
                    </a>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <h5 class="mb-1">عنوان IP: <code>{{ $ip }}</code></h5>
                            <p class="text-muted mb-0">إجمالي السجلات: {{ $logs->total() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">قائمة سجلات الدخول</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle table-hover table-bordered mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th style="min-width: 150px;">المستخدم</th>
                                        <th style="min-width: 150px;">الجهاز/المتصفح</th>
                                        <th style="min-width: 100px;">الحالة</th>
                                        <th style="min-width: 150px;">تاريخ الدخول</th>
                                        <th style="min-width: 150px;">تاريخ الخروج</th>
                                        <th style="min-width: 120px;">مدة الجلسة</th>
                                        <th style="min-width: 150px;">العمليات</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($logs as $log)
                                        <tr>
                                            <td>{{ $loop->iteration + ($logs->currentPage() - 1) * $logs->perPage() }}</td>
                                            <td>
                                                @if($log->user)
                                                    <div class="fw-semibold">{{ $log->user->name }}</div>
                                                    <small class="text-muted">{{ $log->user->email }}</small>
                                                @else
                                                    <span class="text-muted">غير مسجل</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <div><strong>{{ $log->device_type ?? '-' }}</strong></div>
                                                    <div class="text-muted">{{ $log->browser ?? '-' }} {{ $log->browser_version ?? '' }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($log->is_successful)
                                                    <span class="badge bg-success-transparent text-success">ناجحة</span>
                                                @else
                                                    <span class="badge bg-danger-transparent text-danger">فاشلة</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>{{ $log->login_at->format('Y-m-d') }}</div>
                                                <small class="text-muted">{{ $log->login_at->format('H:i:s') }}</small>
                                            </td>
                                            <td>
                                                @if($log->logout_at)
                                                    <div>{{ $log->logout_at->format('Y-m-d') }}</div>
                                                    <small class="text-muted">{{ $log->logout_at->format('H:i:s') }}</small>
                                                @else
                                                    <span class="badge bg-info-transparent text-info">نشطة</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($log->session_duration)
                                                    {{ $log->session_duration }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.login-logs.show', $log->id) }}"
                                                   class="btn btn-sm btn-info text-white">
                                                    <i class="fas fa-eye"></i> عرض
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <i class="bi bi-inbox display-4 text-muted d-block mb-3"></i>
                                                <p class="text-muted">لا توجد سجلات دخول لهذا العنوان</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($logs->hasPages())
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $logs->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop


