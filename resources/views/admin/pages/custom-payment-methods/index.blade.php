@extends('admin.layouts.master')

@section('page-title')
    وسائل الدفع المخصصة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">وسائل الدفع المخصصة</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">وسائل الدفع المخصصة</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('admin.custom-payment-methods.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i>إضافة وسيلة دفع جديدة
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
                @if($methods->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الاسم</th>
                                    <th>النوع</th>
                                    <th>يتطلب وصل</th>
                                    <th>الحالة</th>
                                    <th>الترتيب</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($methods as $method)
                                    <tr>
                                        <td>{{ $method->id }}</td>
                                        <td>
                                            <strong>{{ $method->name }}</strong>
                                            @if($method->instructions)
                                                <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($method->instructions, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($method->type === 'iban')
                                                <span class="badge bg-primary">IBAN</span>
                                            @elseif($method->type === 'code')
                                                <span class="badge bg-info">كود</span>
                                            @else
                                                <span class="badge bg-secondary">أخرى</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($method->requires_receipt)
                                                <span class="badge bg-warning">نعم</span>
                                            @else
                                                <span class="badge bg-success">لا</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($method->is_active)
                                                <span class="badge bg-success">نشط</span>
                                            @else
                                                <span class="badge bg-danger">غير نشط</span>
                                            @endif
                                        </td>
                                        <td>{{ $method->order }}</td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('admin.custom-payment-methods.show', $method->id) }}" class="btn btn-sm btn-info" title="عرض">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.custom-payment-methods.edit', $method->id) }}" class="btn btn-sm btn-primary" title="تعديل">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('admin.custom-payment-methods.destroy', $method->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الوسيلة؟');">
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
                        {{ $methods->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                        <h5 class="mt-3 mb-2">لا توجد وسائل دفع مخصصة</h5>
                        <p class="text-muted">ابدأ بإضافة وسيلة دفع مخصصة جديدة</p>
                        <a href="{{ route('admin.custom-payment-methods.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>إضافة وسيلة دفع جديدة
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
