@extends('admin.layouts.master')

@section('page-title')
    تفاصيل الدفع
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تفاصيل الدفع #{{ $payment->id }}</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        @can('enrollment-list')
                        <li class="breadcrumb-item"><a href="{{ route('admin.enrollments.index') }}">الانضمامات</a></li>
                        @endcan
                        <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">المدفوعات</a></li>
                        <li class="breadcrumb-item active">تفاصيل الدفع</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-right me-1"></i>رجوع
                </a>
                @php
                    $purchaseForActions = $payment->purchase;
                    $purchaseCancelledForActions = $purchaseForActions && $purchaseForActions->status === 'cancelled';
                @endphp
                @if(!$purchaseCancelledForActions && $payment->status === 'pending' && in_array($payment->payment_method, ['iban', 'custom']))
                    <form action="{{ route('admin.payments.approve', $payment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الموافقة على هذا الدفع؟');">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bi bi-check-circle me-1"></i>موافقة
                        </button>
                    </form>
                    <button type="button" class="btn btn-danger btn-sm" onclick="rejectPayment()">
                        <i class="bi bi-x-circle me-1"></i>رفض
                    </button>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <!-- معلومات الدفع -->
            <div class="col-xl-8">
                <div class="card custom-card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">معلومات الدفع</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>معرف الدفع:</strong> #{{ $payment->id }}
                            </div>
                            <div class="col-md-6">
                                <strong>الحالة:</strong>
                                @if($payment->status === 'completed')
                                    <span class="badge bg-success">مكتمل</span>
                                @elseif($payment->status === 'pending')
                                    <span class="badge bg-warning">قيد الانتظار</span>
                                @elseif($payment->status === 'failed')
                                    <span class="badge bg-danger">فاشل</span>
                                @else
                                    <span class="badge bg-info">مسترد</span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>المبلغ:</strong> {{ number_format($payment->amount, 2) }} ر.س
                            </div>
                            <div class="col-md-6">
                                <strong>العملة:</strong> {{ $payment->currency }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>طريقة الدفع:</strong>
                                @if($payment->payment_method === 'wallet')
                                    <span class="badge bg-info">محفظة إلكترونية</span>
                                @elseif($payment->payment_method === 'iban')
                                    <span class="badge bg-primary">IBAN</span>
                                @elseif($payment->payment_method === 'custom')
                                    <span class="badge bg-warning">{{ $payment->customPaymentMethod->name ?? 'مخصص' }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ $payment->payment_method }}</span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <strong>معرف المعاملة:</strong> {{ $payment->transaction_id ?? 'غير متوفر' }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>تاريخ الإنشاء:</strong> {{ $payment->created_at->format('Y-m-d H:i:s') }}
                            </div>
                            @if($payment->reviewed_at)
                                <div class="col-md-6">
                                    <strong>تاريخ المراجعة:</strong> {{ $payment->reviewed_at->format('Y-m-d H:i:s') }}
                                </div>
                            @endif
                        </div>
                        @if($payment->review_notes)
                            <div class="mb-3">
                                <strong>ملاحظات المراجعة:</strong>
                                <p class="text-muted">{{ $payment->review_notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- معلومات الشراء -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">معلومات الشراء</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>العنصر:</strong> {{ $payment->purchase->purchasable->name ?? 'غير محدد' }}
                            </div>
                            <div class="col-md-6">
                                <strong>النوع:</strong> {{ $payment->purchase->purchase_type === 'class' ? 'صف كامل' : 'مادة' }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>السعر:</strong> {{ number_format($payment->purchase->price, 2) }} ر.س
                            </div>
                            <div class="col-md-6">
                                <strong>حالة الشراء:</strong>
                                @php $pPurchase = $payment->purchase; @endphp
                                @if($pPurchase->status === 'completed')
                                    <span class="badge bg-success">مكتمل</span>
                                @elseif($pPurchase->status === 'pending')
                                    <span class="badge bg-warning">قيد المراجعة</span>
                                @elseif($pPurchase->status === 'cancelled')
                                    @if($pPurchase->cancelled_by === 'student')
                                        <span class="badge bg-dark">ملغى نهائي (الطالب)</span>
                                    @elseif($pPurchase->cancelled_by === 'admin')
                                        <span class="badge bg-secondary">ملغى نهائي (الإدارة)</span>
                                    @else
                                        <span class="badge bg-secondary">ملغى نهائي</span>
                                    @endif
                                    @if($pPurchase->cancelled_at)
                                        <small class="text-muted d-block mt-1">تاريخ الإلغاء: {{ $pPurchase->cancelled_at->format('Y-m-d H:i') }}</small>
                                    @endif
                                @elseif($pPurchase->status === 'refunded')
                                    <span class="badge bg-info">مسترد</span>
                                @else
                                    <span class="badge bg-light text-dark">{{ $pPurchase->status }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- الوصل -->
                @if($payment->receipt_file)
                    <div class="card custom-card">
                        <div class="card-header">
                            <h6 class="mb-0">الوصل</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <a href="{{ route('admin.payments.download-receipt', $payment->id) }}" class="btn btn-primary" target="_blank">
                                    <i class="bi bi-download me-2"></i>تحميل الوصل
                                </a>
                            </div>
                            @if(str_ends_with($payment->receipt_file, '.pdf'))
                                <iframe src="{{ media_public_url($payment->receipt_file) }}" width="100%" height="600px"></iframe>
                            @else
                                <img src="{{ media_public_url($payment->receipt_file) }}" class="img-fluid" alt="الوصل">
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- معلومات الطالب -->
            <div class="col-xl-4">
                <div class="card custom-card mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">معلومات الطالب</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>الاسم:</strong><br>
                            {{ $payment->purchase->user->name ?? 'غير محدد' }}
                        </div>
                        <div class="mb-3">
                            <strong>البريد الإلكتروني:</strong><br>
                            {{ $payment->purchase->user->email ?? 'غير محدد' }}
                        </div>
                        <div class="mb-3">
                            <strong>رقم الهاتف:</strong><br>
                            {{ $payment->purchase->user->phone ?? 'غير متوفر' }}
                        </div>
                    </div>
                </div>

                @if($payment->reviewedBy)
                    <div class="card custom-card">
                        <div class="card-header">
                            <h6 class="mb-0">معلومات المراجعة</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>راجع بواسطة:</strong><br>
                                {{ $payment->reviewedBy->name }}
                            </div>
                            <div class="mb-3">
                                <strong>تاريخ المراجعة:</strong><br>
                                {{ $payment->reviewed_at->format('Y-m-d H:i:s') }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal رفض الدفع -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalTitle">رفض الدفع</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form id="rejectForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rejectNotes" class="form-label">سبب الرفض <span class="text-muted fw-normal">(اختياري)</span></label>
                        <textarea class="form-control" id="rejectNotes" name="notes" rows="4" placeholder="يمكنك ترك الحقل فارغاً"></textarea>
                    </div>
                </div>
            </form>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" form="rejectForm" class="btn btn-danger">رفض</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var rejectModalEl = document.getElementById('rejectModal');
    var rejectForm = document.getElementById('rejectForm');
    if (rejectModalEl && rejectForm) {
        rejectModalEl.addEventListener('hidden.bs.modal', function () {
            rejectForm.reset();
        });
    }
})();

function rejectPayment() {
    var rejectForm = document.getElementById('rejectForm');
    if (rejectForm) {
        rejectForm.reset();
    }
    var modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}

document.getElementById('rejectForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    var paymentId = {{ $payment->id }};

    fetch('/admin/payments/' + encodeURIComponent(paymentId) + '/reject', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(function (response) {
        return response.json().then(function (data) {
            return { ok: response.ok, data: data };
        });
    })
    .then(function (result) {
        if (result.ok && result.data && result.data.success) {
            window.location.reload();
        } else {
            var msg = (result.data && result.data.message) ? result.data.message : 'حدث خطأ أثناء رفض الدفع';
            if (result.data && result.data.errors) {
                var first = Object.values(result.data.errors)[0];
                if (Array.isArray(first) && first[0]) {
                    msg = first[0];
                }
            }
            alert(msg);
        }
    })
    .catch(function (error) {
        console.error('Error:', error);
        alert('حدث خطأ أثناء رفض الدفع');
    });
});
</script>
@endpush
@endsection
