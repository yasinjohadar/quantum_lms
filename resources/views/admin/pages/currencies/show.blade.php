@extends('admin.layouts.master')

@section('page-title')
    تفاصيل العملة: {{ $currency->name }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تفاصيل العملة: {{ $currency->name }}</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.currencies.index') }}">العملات</a></li>
                        <li class="breadcrumb-item active">تفاصيل</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('admin.currencies.edit', $currency->id) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-pencil me-1"></i>تعديل
                </a>
                <a href="{{ route('admin.currencies.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-right me-1"></i>رجوع
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8">
                <div class="card custom-card">
                    <div class="card-header">
                        <h6 class="mb-0">معلومات العملة</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th width="200">رمز العملة</th>
                                <td><strong>{{ $currency->code }}</strong></td>
                            </tr>
                            <tr>
                                <th>اسم العملة</th>
                                <td>{{ $currency->name }}</td>
                            </tr>
                            <tr>
                                <th>رمز العملة</th>
                                <td>{{ $currency->symbol }}</td>
                            </tr>
                            <tr>
                                <th>افتراضي</th>
                                <td>
                                    @if($currency->is_default)
                                        <span class="badge bg-success">نعم</span>
                                    @else
                                        <span class="badge bg-secondary">لا</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>الحالة</th>
                                <td>
                                    @if($currency->is_active)
                                        <span class="badge bg-success">نشط</span>
                                    @else
                                        <span class="badge bg-danger">غير نشط</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>الترتيب</th>
                                <td>{{ $currency->order }}</td>
                            </tr>
                            <tr>
                                <th>تاريخ الإنشاء</th>
                                <td>{{ $currency->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                            <tr>
                                <th>آخر تحديث</th>
                                <td>{{ $currency->updated_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
