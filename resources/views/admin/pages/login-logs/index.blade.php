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
                        <h4>{{ $stats['today'] ?? 0 }}</h4>
                        <p class="mb-0">اليوم</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- فلترة -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.login-logs.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">بحث</label>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="البحث في السجلات...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">المستخدم</label>
                        <select class="form-select" name="user_id">
                            <option value="">الكل</option>
                            @foreach($users ?? [] as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">IP Address</label>
                        <input type="text" class="form-control" name="ip_address" value="{{ request('ip_address') }}" placeholder="192.168.1.1">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">الحالة</label>
                        <select class="form-select" name="is_successful">
                            <option value="">الكل</option>
                            <option value="1" {{ request('is_successful') == '1' ? 'selected' : '' }}>نجحت</option>
                            <option value="0" {{ request('is_successful') == '0' ? 'selected' : '' }}>فشلت</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block w-100">
                            <i class="fas fa-search"></i> بحث
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- جدول السجلات -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>المستخدم</th>
                                <th>IP Address</th>
                                <th>البلد</th>
                                <th>المتصفح</th>
                                <th>الحالة</th>
                                <th>سبب الفشل</th>
                                <th>تاريخ الدخول</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>
                                        @if($log->user)
                                            <div class="fw-semibold">{{ $log->user->name }}</div>
                                            <small class="text-muted">{{ $log->user->email }}</small>
                                        @else
                                            <span class="text-muted">غير مسجل</span>
                                        @endif
                                    </td>
                                    <td>
                                        <code>{{ $log->ip_address }}</code>
                                        @if($log->city)
                                            <br><small class="text-muted">{{ $log->city }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $log->country ?? '-' }}</td>
                                    <td>
                                        {{ $log->browser ?? '-' }}
                                        @if($log->platform)
                                            <br><small class="text-muted">{{ $log->platform }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->is_successful)
                                            <span class="badge bg-success">نجح</span>
                                        @else
                                            <span class="badge bg-danger">فشل</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->failure_reason ?? '-' }}</td>
                                    <td>{{ $log->login_at->format('Y-m-d H:i:s') }}</td>
                                    <td>
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a href="{{ route('admin.login-logs.show', $log->id) }}" class="btn btn-sm btn-info" title="عرض التفاصيل">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($log->ip_address)
                                                <a href="{{ route('admin.login-logs.ip', $log->ip_address) }}" class="btn btn-sm btn-outline-primary" title="سجلات IP">
                                                    <i class="fas fa-network-wired"></i>
                                                </a>
                                            @endif
                                            <form action="{{ route('admin.login-logs.destroy', $log->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">لا توجد سجلات دخول</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@stop


