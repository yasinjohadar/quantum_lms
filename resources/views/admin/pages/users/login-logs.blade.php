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
                            <li class="breadcrumb-item active" aria-current="page">سجلات الدخول</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i> العودة
                    </a>
                </div>
            </div>

            <!-- معلومات المستخدم -->
            <div class="row mb-4">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" 
                                         class="rounded-circle" style="width: 60px; height: 60px; object-fit: cover;">
                                @else
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 60px; height: 60px;">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                @endif
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">{{ $user->name }}</h5>
                                    <p class="text-muted mb-0">{{ $user->email }}</p>
                                    @if($user->phone)
                                        <small class="text-muted">{{ $user->phone }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- إحصائيات -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-1">إجمالي المحاولات</p>
                                    <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                                </div>
                                <div class="avatar avatar-md bg-primary-transparent">
                                    <i class="bi bi-list-check fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-1">ناجحة</p>
                                    <h4 class="mb-0 text-success">{{ number_format($stats['successful']) }}</h4>
                                </div>
                                <div class="avatar avatar-md bg-success-transparent">
                                    <i class="bi bi-check-circle fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-1">فاشلة</p>
                                    <h4 class="mb-0 text-danger">{{ number_format($stats['failed']) }}</h4>
                                </div>
                                <div class="avatar avatar-md bg-danger-transparent">
                                    <i class="bi bi-x-circle fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-1">إجمالي مدة الجلسات</p>
                                    <h4 class="mb-0 text-info">
                                        @if($stats['total_duration'])
                                            @php
                                                $hours = floor($stats['total_duration'] / 3600);
                                                $minutes = floor(($stats['total_duration'] % 3600) / 60);
                                            @endphp
                                            @if($hours > 0)
                                                {{ $hours }}س {{ $minutes }}د
                                            @else
                                                {{ $minutes }}د
                                            @endif
                                        @else
                                            0
                                        @endif
                                    </h4>
                                </div>
                                <div class="avatar avatar-md bg-info-transparent">
                                    <i class="bi bi-clock-history fs-20"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- قائمة السجلات -->
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
                                        <th style="min-width: 120px;">عنوان IP</th>
                                        <th style="min-width: 150px;">الجهاز/المتصفح</th>
                                        <th style="min-width: 120px;">الموقع</th>
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
                                                <code>{{ $log->ip_address }}</code>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <div><strong>{{ $log->device_type ?? '-' }}</strong></div>
                                                    <div class="text-muted">{{ $log->browser ?? '-' }} {{ $log->browser_version ?? '' }}</div>
                                                    <div class="text-muted">{{ $log->platform ?? '-' }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($log->country || $log->city)
                                                    <div class="small">
                                                        {{ $log->city ?? '' }}{{ $log->city && $log->country ? ', ' : '' }}{{ $log->country ?? '' }}
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($log->is_successful)
                                                    <span class="badge bg-success-transparent text-success">
                                                        <i class="bi bi-check-circle me-1"></i> ناجحة
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger-transparent text-danger">
                                                        <i class="bi bi-x-circle me-1"></i> فاشلة
                                                    </span>
                                                    @if($log->failure_reason)
                                                        <br><small class="text-muted">{{ $log->failure_reason }}</small>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $log->login_at->format('Y-m-d') }}</div>
                                                <small class="text-muted">{{ $log->login_at->format('H:i:s') }}</small>
                                                <div class="text-muted small">{{ $log->login_at->diffForHumans() }}</div>
                                            </td>
                                            <td>
                                                @if($log->logout_at)
                                                    <div class="fw-semibold">{{ $log->logout_at->format('Y-m-d') }}</div>
                                                    <small class="text-muted">{{ $log->logout_at->format('H:i:s') }}</small>
                                                    <div class="text-muted small">{{ $log->logout_at->diffForHumans() }}</div>
                                                @else
                                                    <span class="badge bg-info-transparent text-info">الجلسة لا تزال نشطة</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($log->session_duration)
                                                    <div class="fw-semibold">{{ $log->session_duration }}</div>
                                                    <small class="text-muted">
                                                        @php
                                                            $hours = floor($log->session_duration_seconds / 3600);
                                                            $minutes = floor(($log->session_duration_seconds % 3600) / 60);
                                                        @endphp
                                                        @if($hours > 0)
                                                            {{ $hours }} ساعة {{ $minutes }} دقيقة
                                                        @else
                                                            {{ $minutes }} دقيقة
                                                        @endif
                                                    </small>
                                                @elseif($log->is_active)
                                                    <span class="text-muted">قيد التشغيل...</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.login-logs.show', $log->id) }}"
                                                   class="btn btn-sm btn-info text-white"
                                                   title="عرض التفاصيل">
                                                    <i class="fas fa-eye"></i> عرض
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-5">
                                                <i class="bi bi-inbox display-4 text-muted d-block mb-3"></i>
                                                <p class="text-muted">لا توجد سجلات دخول لهذا المستخدم</p>
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

