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
                                    <p class="text-muted mb-1">اليوم</p>
                                    <h4 class="mb-0 text-info">{{ number_format($stats['today']) }}</h4>
                                </div>
                                <div class="avatar avatar-md bg-info-transparent">
                                    <i class="bi bi-calendar-day fs-20"></i>
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
                                       value="{{ request('ip_address') }}" style="min-width: 150px;">

                                <select name="is_successful" class="form-select form-select-sm" style="min-width: 150px;">
                                    <option value="">كل الحالات</option>
                                    <option value="1" {{ request('is_successful') === '1' ? 'selected' : '' }}>ناجحة</option>
                                    <option value="0" {{ request('is_successful') === '0' ? 'selected' : '' }}>فاشلة</option>
                                </select>

                                <input type="date" name="date_from" class="form-control form-control-sm"
                                       value="{{ request('date_from') }}" style="min-width: 150px;"
                                       placeholder="من تاريخ">

                                <input type="date" name="date_to" class="form-control form-control-sm"
                                       value="{{ request('date_to') }}" style="min-width: 150px;"
                                       placeholder="إلى تاريخ">

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
                                        <th style="min-width: 150px;">المستخدم</th>
                                        <th style="min-width: 120px;">عنوان IP</th>
                                        <th style="min-width: 150px;">الجهاز/المتصفح</th>
                                        <th style="min-width: 120px;">الموقع</th>
                                        <th style="min-width: 100px;">الحالة</th>
                                        <th style="min-width: 150px;">تاريخ الدخول</th>
                                        <th style="min-width: 150px;">تاريخ الخروج</th>
                                        <th style="min-width: 120px;">مدة الجلسة</th>
                                        <th style="min-width: 200px;">العمليات</th>
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
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.login-logs.ip', $log->ip_address) }}" class="text-decoration-none">
                                                    {{ $log->ip_address }}
                                                </a>
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
                                                @elseif($log->is_active)
                                                    <span class="text-muted">-</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1 flex-wrap justify-content-center">
                                                    <a href="{{ route('admin.login-logs.show', $log->id) }}"
                                                       class="btn btn-sm btn-info text-white"
                                                       title="عرض التفاصيل">
                                                        <i class="fas fa-eye"></i> عرض
                                                    </a>
                                                    @if($log->user)
                                                        <a href="{{ route('admin.login-logs.user', $log->user_id) }}"
                                                           class="btn btn-sm btn-primary text-white"
                                                           title="سجلات المستخدم">
                                                            <i class="fas fa-user"></i>
                                                        </a>
                                                    @endif
                                                    <button type="button"
                                                            class="btn btn-sm btn-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteLogModal{{ $log->id }}"
                                                            title="حذف السجل">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>

                                                <!-- Modal for Delete Confirmation -->
                                                <div class="modal fade" id="deleteLogModal{{ $log->id }}" tabindex="-1" aria-labelledby="deleteLogModalLabel{{ $log->id }}" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-body text-center p-4">
                                                                <i class="bi bi-trash-fill text-danger display-1 mb-3"></i>
                                                                <h4 class="mb-3">تأكيد حذف السجل</h4>
                                                                <p class="mb-3">هل أنت متأكد من حذف هذا السجل؟</p>
                                                                <div class="alert alert-warning mb-4">
                                                                    <i class="bi bi-info-circle me-2"></i>
                                                                    <small>هذه العملية لا يمكن التراجع عنها.</small>
                                                                </div>
                                                                <div class="d-flex justify-content-center gap-2">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                        <i class="bi bi-x-circle me-1"></i> إلغاء
                                                                    </button>
                                                                    <form action="{{ route('admin.login-logs.destroy', $log->id) }}" method="POST" class="d-inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-danger">
                                                                            <i class="bi bi-trash me-1"></i> حذف
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
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
                                    {{ $logs->appends(request()->query())->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- حذف السجلات القديمة -->
            <div class="row mt-4">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-trash me-2"></i> حذف السجلات القديمة
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.login-logs.clear-old') }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف جميع السجلات الأقدم من التاريخ المحدد؟')">
                                @csrf
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label">حذف السجلات الأقدم من (بالأيام)</label>
                                        <input type="number" name="days" class="form-control" min="1" max="365" value="30" required>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-danger">
                                            <i class="bi bi-trash me-1"></i> حذف السجلات القديمة
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop


