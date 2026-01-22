@extends('admin.layouts.master')

@section('page-title')
    تفاصيل وسيلة الدفع المخصصة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تفاصيل وسيلة الدفع المخصصة</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.custom-payment-methods.index') }}">وسائل الدفع المخصصة</a></li>
                        <li class="breadcrumb-item active">تفاصيل</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.custom-payment-methods.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-right me-1"></i>رجوع
                </a>
                <a href="{{ route('admin.custom-payment-methods.edit', $method->id) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-pencil me-1"></i>تعديل
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8">
                <div class="card custom-card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">معلومات وسيلة الدفع</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>الاسم:</strong> {{ $method->name }}
                            </div>
                            <div class="col-md-6">
                                <strong>النوع:</strong>
                                @if($method->type === 'iban')
                                    <span class="badge bg-primary">IBAN</span>
                                @elseif($method->type === 'code')
                                    <span class="badge bg-info">كود</span>
                                @else
                                    <span class="badge bg-secondary">أخرى</span>
                                @endif
                            </div>
                        </div>

                        @if($method->account_info)
                            <div class="mb-3">
                                <strong>معلومات الحساب:</strong>
                                <div class="mt-2">
                                    @foreach($method->account_info as $key => $value)
                                        <p class="mb-1"><strong>{{ $key }}:</strong> {{ $value }}</p>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($method->code_prefix)
                            <div class="mb-3">
                                <strong>بادئة الكود:</strong> {{ $method->code_prefix }}
                            </div>
                        @endif

                        @if($method->instructions)
                            <div class="mb-3">
                                <strong>تعليمات الدفع:</strong>
                                <p class="text-muted">{{ $method->instructions }}</p>
                            </div>
                        @endif

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>يتطلب وصل:</strong>
                                @if($method->requires_receipt)
                                    <span class="badge bg-warning">نعم</span>
                                @else
                                    <span class="badge bg-success">لا</span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <strong>الحالة:</strong>
                                @if($method->is_active)
                                    <span class="badge bg-success">نشط</span>
                                @else
                                    <span class="badge bg-danger">غير نشط</span>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <strong>الترتيب:</strong> {{ $method->order }}
                        </div>

                        <div class="mb-3">
                            <strong>تاريخ الإنشاء:</strong> {{ $method->created_at->format('Y-m-d H:i:s') }}
                        </div>
                        <div>
                            <strong>آخر تحديث:</strong> {{ $method->updated_at->format('Y-m-d H:i:s') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
