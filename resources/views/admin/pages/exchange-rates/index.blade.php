@extends('admin.layouts.master')

@section('page-title')
    أسعار الصرف
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">أسعار الصرف</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">أسعار الصرف</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('admin.exchange-rates.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i>إضافة سعر صرف جديد
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card custom-card">
            <div class="card-body">
                @if($exchangeRates->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>من عملة</th>
                                    <th>إلى عملة</th>
                                    <th>سعر الصرف</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($exchangeRates as $rate)
                                    <tr>
                                        <td>{{ $rate->id }}</td>
                                        <td><strong>{{ $rate->fromCurrency->code }}</strong> ({{ $rate->fromCurrency->name }})</td>
                                        <td><strong>{{ $rate->toCurrency->code }}</strong> ({{ $rate->toCurrency->name }})</td>
                                        <td><strong>{{ number_format($rate->rate, 6) }}</strong></td>
                                        <td>
                                            @if($rate->is_active)
                                                <span class="badge bg-success">نشط</span>
                                            @else
                                                <span class="badge bg-danger">غير نشط</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('admin.exchange-rates.edit', $rate->id) }}" class="btn btn-sm btn-primary" title="تعديل">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('admin.exchange-rates.destroy', $rate->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف سعر الصرف هذا؟');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-4">
                        {{ $exchangeRates->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                        <h5 class="mt-3 mb-2">لا توجد أسعار صرف</h5>
                        <p class="text-muted">ابدأ بإضافة سعر صرف جديد</p>
                        <a href="{{ route('admin.exchange-rates.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>إضافة سعر صرف جديد
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
