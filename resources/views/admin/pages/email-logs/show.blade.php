@extends('admin.layouts.master')

@section('page-title')
    تفاصيل الإيميل
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تفاصيل الإيميل</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.email-logs.index') }}">سجل الإيميلات</a></li>
                        <li class="breadcrumb-item active" aria-current="page">تفاصيل</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('admin.email-logs.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0">
                    <div class="card-header">
                        <h5 class="card-title mb-0">معلومات الإيميل</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>إلى:</strong> {{ $emailLog->to }}
                            </div>
                            <div class="col-md-6">
                                <strong>الحالة:</strong>
                                @if($emailLog->status === 'sent')
                                    <span class="badge bg-success">مرسل</span>
                                @else
                                    <span class="badge bg-danger">فشل</span>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <strong>الموضوع:</strong>
                            <p>{{ $emailLog->subject }}</p>
                        </div>

                        <div class="mb-3">
                            <strong>المحتوى:</strong>
                            <div class="border p-3 bg-light" style="white-space: pre-wrap;">{{ $emailLog->body }}</div>
                        </div>

                        @if($emailLog->error_message)
                            <div class="mb-3">
                                <strong>رسالة الخطأ:</strong>
                                <div class="alert alert-danger">{{ $emailLog->error_message }}</div>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <strong>تاريخ الإرسال:</strong> {{ $emailLog->sent_at ? $emailLog->sent_at->format('Y-m-d H:i:s') : 'غير محدد' }}
                            </div>
                            <div class="col-md-6">
                                <strong>تاريخ الإنشاء:</strong> {{ $emailLog->created_at->format('Y-m-d H:i:s') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop



