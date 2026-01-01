@extends('admin.layouts.master')

@section('page-title')
    سجلات الدخول - {{ $user->name }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">سجلات الدخول - {{ $user->name }}</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">المستخدمون</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('users.show', $user) }}">ملف المستخدم</a></li>
                        <li class="breadcrumb-item active" aria-current="page">سجلات الدخول</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('users.show', $user) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <!-- معلومات المستخدم -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    @if($user->photo)
                        <img src="{{ asset('storage/' . $user->photo) }}" 
                             alt="{{ $user->name }}" 
                             class="rounded-circle me-3" 
                             style="width: 60px; height: 60px; object-fit: cover;">
                    @else
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 60px; height: 60px; font-size: 24px;">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <h5 class="mb-1">{{ $user->name }}</h5>
                        <p class="text-muted mb-0">{{ $user->email }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- إحصائيات -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h4>{{ $stats['total'] ?? 0 }}</h4>
                        <p class="mb-0">إجمالي السجلات</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h4 class="text-success">{{ $stats['successful'] ?? 0 }}</h4>
                        <p class="mb-0">نجحت</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h4 class="text-danger">{{ $stats['failed'] ?? 0 }}</h4>
                        <p class="mb-0">فشلت</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h4>
                            @if(isset($stats['total_duration']) && $stats['total_duration'])
                                {{ gmdate('H:i:s', $stats['total_duration']) }}
                            @else
                                00:00:00
                            @endif
                        </h4>
                        <p class="mb-0">إجمالي مدة الجلسات</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- جدول السجلات -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 fw-bold">سجلات الدخول</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle table-hover table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>تاريخ ووقت الدخول</th>
                                <th>IP Address</th>
                                <th>User Agent</th>
                                <th>الحالة</th>
                                <th>مدة الجلسة</th>
                                <th>تاريخ الخروج</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>
                                        <div>{{ $log->login_at->format('Y-m-d') }}</div>
                                        <small class="text-muted">{{ $log->login_at->format('H:i:s') }}</small>
                                    </td>
                                    <td>
                                        <code>{{ $log->ip_address }}</code>
                                    </td>
                                    <td>
                                        <small class="text-muted" title="{{ $log->user_agent }}">
                                            {{ strlen($log->user_agent) > 50 ? substr($log->user_agent, 0, 50) . '...' : $log->user_agent }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($log->is_successful)
                                            <span class="badge bg-success">نجح</span>
                                        @else
                                            <span class="badge bg-danger">فشل</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->session_duration_seconds)
                                            {{ gmdate('H:i:s', $log->session_duration_seconds) }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->logout_at)
                                            <div>{{ $log->logout_at->format('Y-m-d') }}</div>
                                            <small class="text-muted">{{ $log->logout_at->format('H:i:s') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3"></i>
                                            <p class="mb-0">لا توجد سجلات دخول</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($logs->hasPages())
                    <div class="mt-4">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

