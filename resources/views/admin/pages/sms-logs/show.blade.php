@extends('admin.layouts.master')

@section('page-title')
    تفاصيل SMS
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تفاصيل SMS</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.sms-logs.index') }}">سجل SMS</a></li>
                        <li class="breadcrumb-item active" aria-current="page">تفاصيل</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0">
                    <div class="card-header">
                        <h5 class="card-title mb-0">تفاصيل الرسالة</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">رقم الهاتف:</dt>
                            <dd class="col-sm-9">{{ $smsLog->to }}</dd>

                            <dt class="col-sm-3">الرسالة:</dt>
                            <dd class="col-sm-9">{{ $smsLog->message }}</dd>

                            <dt class="col-sm-3">النوع:</dt>
                            <dd class="col-sm-9">
                                @if($smsLog->type === 'otp')
                                    <span class="badge bg-info">OTP</span>
                                @elseif($smsLog->type === 'notification')
                                    <span class="badge bg-primary">إشعار</span>
                                @else
                                    <span class="badge bg-secondary">مخصص</span>
                                @endif
                            </dd>

                            <dt class="col-sm-3">الحالة:</dt>
                            <dd class="col-sm-9">
                                @if($smsLog->status === 'sent')
                                    <span class="badge bg-success">مرسل</span>
                                @else
                                    <span class="badge bg-danger">فشل</span>
                                @endif
                            </dd>

                            <dt class="col-sm-3">المزود:</dt>
                            <dd class="col-sm-9">{{ $smsLog->provider ?? '-' }}</dd>

                            @if($smsLog->error_message)
                            <dt class="col-sm-3">رسالة الخطأ:</dt>
                            <dd class="col-sm-9">
                                <div class="alert alert-danger mb-0">
                                    {{ $smsLog->error_message }}
                                </div>
                            </dd>
                            @endif

                            <dt class="col-sm-3">تاريخ الإرسال:</dt>
                            <dd class="col-sm-9">{{ $smsLog->sent_at ? $smsLog->sent_at->format('Y-m-d H:i:s') : '-' }}</dd>

                            <dt class="col-sm-3">تاريخ الإنشاء:</dt>
                            <dd class="col-sm-9">{{ $smsLog->created_at->format('Y-m-d H:i:s') }}</dd>
                        </dl>

                        <div class="mt-3">
                            <a href="{{ route('admin.sms-logs.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right me-1"></i> رجوع
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

