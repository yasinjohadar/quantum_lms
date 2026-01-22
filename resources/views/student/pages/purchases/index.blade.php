@extends('student.layouts.master')

@section('page-title')
    مشترياتي
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">مشترياتي</h4>
                <p class="mb-0 text-muted">جميع مشترياتك من الصفوف والمواد</p>
            </div>
        </div>
        <!-- End Page Header -->

        @if($purchases->count() > 0)
            <div class="row">
                @foreach($purchases as $purchase)
                    <div class="col-xl-6 mb-4">
                        <div class="card custom-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="mb-1">
                                            <i class="bi bi-{{ $purchase->purchase_type === 'class' ? 'building' : 'book' }} me-2 text-primary"></i>
                                            {{ $purchase->purchasable->name ?? 'غير محدد' }}
                                        </h5>
                                        <p class="text-muted mb-0 small">
                                            {{ $purchase->purchase_type === 'class' ? 'صف كامل' : 'مادة' }}
                                        </p>
                                    </div>
                                    <span class="badge bg-{{ $purchase->status === 'completed' ? 'success' : ($purchase->status === 'pending' ? 'warning' : 'danger') }}">
                                        @if($purchase->status === 'completed')
                                            مكتمل
                                        @elseif($purchase->status === 'pending')
                                            قيد الانتظار
                                        @elseif($purchase->status === 'cancelled')
                                            ملغي
                                        @else
                                            مسترد
                                        @endif
                                    </span>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">المبلغ:</span>
                                        <strong>{{ number_format($purchase->price, 2) }} ر.س</strong>
                                    </div>
                                    @if($purchase->purchased_at)
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">تاريخ الشراء:</span>
                                            <span>{{ $purchase->purchased_at->format('Y-m-d') }}</span>
                                        </div>
                                    @endif
                                    @if($purchase->payment)
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">طريقة الدفع:</span>
                                            <span>
                                                @if($purchase->payment->payment_method === 'wallet')
                                                    محفظة إلكترونية
                                                @elseif($purchase->payment->payment_method === 'iban')
                                                    تحويل بنكي
                                                @elseif($purchase->payment->payment_method === 'custom')
                                                    {{ $purchase->payment->customPaymentMethod->name ?? 'مخصص' }}
                                                @else
                                                    {{ $purchase->payment->payment_method }}
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                @if($purchase->status === 'pending' && $purchase->payment && $purchase->payment->status === 'pending')
                                    <div class="alert alert-warning mb-3">
                                        <i class="bi bi-hourglass-split me-2"></i>
                                        <small>في انتظار مراجعة الدفع من قبل الإدارة</small>
                                    </div>
                                @endif

                                @if($purchase->status === 'completed')
                                    <a href="{{ $purchase->purchase_type === 'class' ? route('student.enrollments.class.show', $purchase->purchasable_id) : route('student.subjects.show', $purchase->purchasable_id) }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-eye me-1"></i>
                                        عرض المحتوى
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $purchases->links() }}
            </div>
        @else
            <div class="card custom-card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-cart-x text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 mb-2">لا توجد مشتريات</h5>
                    <p class="text-muted">لم تقم بشراء أي صفوف أو مواد بعد</p>
                    <a href="{{ route('student.enrollments.index') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        تصفح الصفوف والمواد
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
<!-- End::app-content -->
@endsection
