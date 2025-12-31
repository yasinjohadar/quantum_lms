@extends('admin.layouts.master')

@section('page-title')
    سجلات الدخول: {{ $user->name }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">سجلات الدخول: {{ $user->name }}</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.login-logs.index') }}">سجلات الدخول</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('admin.login-logs.index') }}" class="btn btn-secondary btn-sm">
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
            <div class="card-header">
                <h5 class="card-title mb-0">معلومات المستخدم</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <strong>الاسم:</strong> {{ $user->name }}
                    </div>
                    <div class="col-md-4">
                        <strong>البريد الإلكتروني:</strong> {{ $user->email }}
                    </div>
                    <div class="col-md-4">
                        <strong>عدد السجلات:</strong> {{ $logs->total() }}
                    </div>
                </div>
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
                                <th>IP Address</th>
                                <th>البلد</th>
                                <th>المدينة</th>
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
                                        <code>{{ $log->ip_address }}</code>
                                    </td>
                                    <td>{{ $log->country ?? '-' }}</td>
                                    <td>{{ $log->city ?? '-' }}</td>
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
                                    <td colspan="9" class="text-center py-4">لا توجد سجلات لهذا المستخدم</td>
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

