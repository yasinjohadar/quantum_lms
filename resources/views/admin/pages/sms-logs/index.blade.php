@extends('admin.layouts.master')

@section('page-title')
    سجل SMS
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">سجل SMS</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">سجل SMS</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- فلترة -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.sms-logs.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">بحث</label>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="البحث في الرقم أو الرسالة...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">الحالة</label>
                        <select class="form-select" name="status">
                            <option value="">الكل</option>
                            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>مرسل</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>فشل</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">النوع</label>
                        <select class="form-select" name="type">
                            <option value="">الكل</option>
                            <option value="otp" {{ request('type') == 'otp' ? 'selected' : '' }}>OTP</option>
                            <option value="notification" {{ request('type') == 'notification' ? 'selected' : '' }}>إشعار</option>
                            <option value="custom" {{ request('type') == 'custom' ? 'selected' : '' }}>مخصص</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header">
                <h5 class="card-title mb-0">قائمة SMS</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped text-nowrap">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>إلى</th>
                                <th>الرسالة</th>
                                <th>النوع</th>
                                <th>الحالة</th>
                                <th>المزود</th>
                                <th>تاريخ الإرسال</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>{{ $log->to }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($log->message, 50) }}</td>
                                    <td>
                                        @if($log->type === 'otp')
                                            <span class="badge bg-info">OTP</span>
                                        @elseif($log->type === 'notification')
                                            <span class="badge bg-primary">إشعار</span>
                                        @else
                                            <span class="badge bg-secondary">مخصص</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->status === 'sent')
                                            <span class="badge bg-success">مرسل</span>
                                        @else
                                            <span class="badge bg-danger">فشل</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->provider ?? '-' }}</td>
                                    <td>{{ $log->sent_at ? $log->sent_at->format('Y-m-d H:i:s') : $log->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td>
                                        <a href="{{ route('admin.sms-logs.show', $log) }}" class="btn btn-sm btn-info" title="عرض">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">لا توجد سجلات</td>
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




