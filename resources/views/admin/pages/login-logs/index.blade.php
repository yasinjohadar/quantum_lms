@extends('admin.layouts.master')

@section('page-title')
    سجلات الدخول
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">سجلات الدخول</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">سجلات الدخول</li>
                        </ol>
                    </nav>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <!-- إحصائيات -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-muted mb-1">إجمالي السجلات</h6>
                                    <h3 class="mb-0">{{ number_format($stats['total']) }}</h3>
                                </div>
                                <div class="avatar avatar-lg bg-primary-transparent rounded-circle">
                                    <i class="bi bi-list-ul fs-2 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-muted mb-1">ناجحة</h6>
                                    <h3 class="mb-0 text-success">{{ number_format($stats['successful']) }}</h3>
                                </div>
                                <div class="avatar avatar-lg bg-success-transparent rounded-circle">
                                    <i class="bi bi-check-circle fs-2 text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-muted mb-1">فاشلة</h6>
                                    <h3 class="mb-0 text-danger">{{ number_format($stats['failed']) }}</h3>
                                </div>
                                <div class="avatar avatar-lg bg-danger-transparent rounded-circle">
                                    <i class="bi bi-x-circle fs-2 text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-muted mb-1">اليوم</h6>
                                    <h3 class="mb-0 text-info">{{ number_format($stats['today']) }}</h3>
                                </div>
                                <div class="avatar avatar-lg bg-info-transparent rounded-circle">
                                    <i class="bi bi-calendar-day fs-2 text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <h5 class="mb-0 fw-bold">قائمة سجلات الدخول</h5>

                            <form method="GET" action="{{ route('admin.login-logs.index') }}"
                                  class="d-flex flex-wrap gap-2 align-items-center">
                                <input type="text" name="search" class="form-control form-control-sm"
                                       placeholder="بحث بالاسم، البريد، أو IP"
                                       value="{{ request('search') }}" style="min-width: 220px;">

                                <select name="user_id" class="form-select form-select-sm" style="min-width: 160px;">
                                    <option value="">كل المستخدمين</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <input type="text" name="ip_address" class="form-control form-control-sm"
                                       placeholder="عنوان IP"
                                       value="{{ request('ip_address') }}" style="min-width: 140px;">

                                <select name="is_successful" class="form-select form-select-sm" style="min-width: 150px;">
                                    <option value="">كل الحالات</option>
                                    <option value="1" {{ request('is_successful') === '1' ? 'selected' : '' }}>ناجحة</option>
                                    <option value="0" {{ request('is_successful') === '0' ? 'selected' : '' }}>فاشلة</option>
                                </select>

                                <div class="d-flex gap-2" style="min-width: 240px;">
                                    <input type="date" name="date_from" class="form-control form-control-sm"
                                           value="{{ request('date_from') }}" placeholder="من">
                                    <input type="date" name="date_to" class="form-control form-control-sm"
                                           value="{{ request('date_to') }}" placeholder="إلى">
                                </div>

                                <button type="submit" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-search me-1"></i> بحث
                                </button>
                                <a href="{{ route('admin.login-logs.index') }}" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-x-circle me-1"></i> مسح
                                </a>
                            </form>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle table-hover table-bordered mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th style="min-width: 180px;">المستخدم</th>
                                        <th style="min-width: 130px;">عنوان IP</th>
                                        <th style="min-width: 150px;">الجهاز</th>
                                        <th style="min-width: 150px;">المتصفح</th>
                                        <th style="min-width: 120px;">المكان</th>
                                        <th style="min-width: 100px;">الحالة</th>
                                        <th style="min-width: 150px;">تاريخ الدخول</th>
                                        <th style="min-width: 120px;">مدة الجلسة</th>
                                        <th style="min-width: 150px;">العمليات</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($logs as $log)
                                        <tr>
                                            <td>{{ $log->id }}</td>
                                            <td>
                                                @if($log->user)
                                                    <div class="d-flex align-items-center gap-2">
                                                        @if($log->user->photo)
                                                            <img src="{{ asset('storage/' . $log->user->photo) }}" 
                                                                 alt="{{ $log->user->name }}" 
                                                                 class="rounded-circle" 
                                                                 style="width: 35px; height: 35px; object-fit: cover;">
                                                        @else
                                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                                 style="width: 35px; height: 35px;">
                                                                {{ substr($log->user->name, 0, 1) }}
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <div class="fw-semibold">{{ $log->user->name }}</div>
                                                            <small class="text-muted">{{ $log->user->email }}</small>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">مستخدم محذوف</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.login-logs.ip', $log->ip_address) }}" 
                                                   class="text-primary text-decoration-none">
                                                    {{ $log->ip_address }}
                                                </a>
                                            </td>
                                            <td>
                                                <div>
                                                    <i class="bi bi-{{ $log->device_type === 'mobile' ? 'phone' : ($log->device_type === 'tablet' ? 'tablet' : 'laptop') }} me-1"></i>
                                                    {{ $log->device_type ?? 'غير معروف' }}
                                                </div>
                                                @if($log->platform)
                                                    <small class="text-muted">{{ $log->platform }} {{ $log->platform_version }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($log->browser)
                                                    <div>{{ $log->browser }}</div>
                                                    @if($log->browser_version)
                                                        <small class="text-muted">v{{ $log->browser_version }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($log->country || $log->city)
                                                    <div>
                                                        @if($log->country)
                                                            <i class="bi bi-geo-alt me-1"></i>{{ $log->country }}
                                                        @endif
                                                        @if($log->city)
                                                            <br><small class="text-muted">{{ $log->city }}</small>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($log->is_successful)
                                                    <span class="badge bg-success-transparent text-success">
                                                        <i class="bi bi-check-circle me-1"></i> ناجح
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger-transparent text-danger">
                                                        <i class="bi bi-x-circle me-1"></i> فاشل
                                                    </span>
                                                    @if($log->failure_reason)
                                                        <br><small class="text-muted">{{ $log->failure_reason }}</small>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                <div>{{ $log->login_at->format('Y-m-d') }}</div>
                                                <small class="text-muted">{{ $log->login_at->format('H:i:s') }}</small>
                                            </td>
                                            <td>
                                                @if($log->session_duration_seconds)
                                                    {{ $log->session_duration }}
                                                @elseif($log->logout_at === null && $log->is_successful)
                                                    <span class="badge bg-info-transparent text-info">نشط</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('admin.login-logs.show', $log->id) }}" 
                                                       class="btn btn-sm btn-info" 
                                                       data-bs-toggle="tooltip" 
                                                       title="عرض التفاصيل">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    @if($log->user)
                                                        <a href="{{ route('admin.login-logs.user', $log->user->id) }}" 
                                                           class="btn btn-sm btn-secondary" 
                                                           data-bs-toggle="tooltip" 
                                                           title="سجلات المستخدم">
                                                            <i class="bi bi-person-lines-fill"></i>
                                                        </a>
                                                    @endif
                                                    <form action="{{ route('admin.login-logs.destroy', $log->id) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل؟')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                                data-bs-toggle="tooltip" 
                                                                title="حذف">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center py-5">
                                                <i class="bi bi-inbox display-4 text-muted d-block mb-3"></i>
                                                <p class="text-muted">لا توجد سجلات دخول</p>
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

@section('js')
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@stop

