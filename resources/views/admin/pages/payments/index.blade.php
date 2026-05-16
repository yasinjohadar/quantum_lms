@extends('admin.layouts.master')

@section('page-title')
    المدفوعات — الانضمامات
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">المدفوعات</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        @can('enrollment-list')
                        <li class="breadcrumb-item"><a href="{{ route('admin.enrollments.index') }}">الانضمامات</a></li>
                        @endcan
                        <li class="breadcrumb-item active" aria-current="page">المدفوعات</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                @php
                    $needsReviewCount = \App\Models\Payment::needsReview()->count();
                @endphp
                @if($needsReviewCount > 0)
                    <a href="{{ route('admin.payments.index', ['needs_review' => 1]) }}" class="btn btn-warning btn-sm position-relative">
                        <i class="bi bi-clock me-1"></i> تحتاج مراجعة
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ $needsReviewCount }}
                        </span>
                    </a>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Filters -->
        <div class="card custom-card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.payments.index') }}" id="paymentsFilterForm">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">الحالة</label>
                            <select name="status" class="form-select" id="statusFilter">
                                <option value="">الكل</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>فاشل</option>
                                <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>مسترد</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">طريقة الدفع</label>
                            <select name="payment_method" class="form-select" id="paymentMethodFilter">
                                <option value="">الكل</option>
                                <option value="wallet" {{ request('payment_method') == 'wallet' ? 'selected' : '' }}>محفظة إلكترونية</option>
                                <option value="iban" {{ request('payment_method') == 'iban' ? 'selected' : '' }}>IBAN</option>
                                <option value="custom" {{ request('payment_method') == 'custom' ? 'selected' : '' }}>مخصص</option>
                                <option value="stripe" {{ request('payment_method') == 'stripe' ? 'selected' : '' }}>Stripe</option>
                                <option value="paypal" {{ request('payment_method') == 'paypal' ? 'selected' : '' }}>PayPal</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">نوع الشراء</label>
                            <select name="purchase_type" class="form-select" id="purchaseTypeFilter">
                                <option value="">الكل</option>
                                <option value="class" {{ request('purchase_type') == 'class' ? 'selected' : '' }}>صف</option>
                                <option value="subject" {{ request('purchase_type') == 'subject' ? 'selected' : '' }}>مادة</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">إلى تاريخ</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">البحث</label>
                            <input type="text" name="search" class="form-control" id="searchQuery" value="{{ request('search') }}" placeholder="اسم الطالب أو البريد">
                        </div>
                    </div>
                    <div class="row g-3 align-items-end mt-1">
                        <div class="col-md-3">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" name="needs_review" value="1" id="needsReviewFilter" {{ request()->boolean('needs_review') ? 'checked' : '' }}>
                                <label class="form-check-label" for="needsReviewFilter">عرض المدفوعات التي تحتاج مراجعة فقط</label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1" for="purchaseStatusFilter">حالة الشراء</label>
                            <select name="purchase_status" class="form-select" id="purchaseStatusFilter">
                                <option value="">الكل</option>
                                <option value="pending" {{ request('purchase_status') === 'pending' ? 'selected' : '' }}>شراء معلّق</option>
                                <option value="completed" {{ request('purchase_status') === 'completed' ? 'selected' : '' }}>شراء مكتمل</option>
                                <option value="cancelled" {{ request('purchase_status') === 'cancelled' ? 'selected' : '' }}>ملغى نهائي</option>
                                <option value="refunded" {{ request('purchase_status') === 'refunded' ? 'selected' : '' }}>شراء مسترد</option>
                            </select>
                        </div>
                        <div class="col-md-7 d-flex align-items-end justify-content-md-end gap-2 flex-wrap">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i>تطبيق الفلاتر
                            </button>
                            <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-clockwise me-1"></i>إعادة تعيين
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Payments Table -->
        <div class="card custom-card">
            <div class="card-body">
                @if($payments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الطالب</th>
                                    <th>العنصر</th>
                                    <th>المبلغ</th>
                                    <th>طريقة الدفع</th>
                                    <th>حالة الشراء</th>
                                    <th>حالة الدفع</th>
                                    <th>التاريخ</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="paymentsTableBody">
                                @foreach($payments as $payment)
                                    <tr>
                                        <td>{{ $payment->id }}</td>
                                        <td>
                                            <div>
                                                <strong>{{ $payment->purchase->user->name ?? 'غير محدد' }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $payment->purchase->user->email ?? '' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            {{ $payment->purchase->purchasable->name ?? 'غير محدد' }}
                                            <br>
                                            <small class="text-muted">{{ $payment->purchase->purchase_type === 'class' ? 'صف' : 'مادة' }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ number_format($payment->amount, 2) }} ر.س</strong>
                                        </td>
                                        <td>
                                            @if($payment->payment_method === 'wallet')
                                                <span class="badge bg-info">محفظة إلكترونية</span>
                                            @elseif($payment->payment_method === 'iban')
                                                <span class="badge bg-primary">IBAN</span>
                                            @elseif($payment->payment_method === 'custom')
                                                <span class="badge bg-warning">{{ $payment->customPaymentMethod->name ?? 'مخصص' }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $payment->payment_method }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php $pur = $payment->purchase; @endphp
                                            @if(!$pur)
                                                <span class="text-muted">—</span>
                                            @elseif($pur->status === 'cancelled')
                                                @if($pur->cancelled_by === 'student')
                                                    <span class="badge bg-dark">ملغى نهائي (الطالب)</span>
                                                @elseif($pur->cancelled_by === 'admin')
                                                    <span class="badge bg-secondary">ملغى نهائي (الإدارة)</span>
                                                @else
                                                    <span class="badge bg-secondary">ملغى نهائي</span>
                                                @endif
                                            @elseif($pur->status === 'pending')
                                                <span class="badge bg-warning">قيد المراجعة</span>
                                            @elseif($pur->status === 'completed')
                                                <span class="badge bg-success">مكتمل</span>
                                            @elseif($pur->status === 'refunded')
                                                <span class="badge bg-info">مسترد</span>
                                            @else
                                                <span class="badge bg-light text-dark">{{ $pur->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($payment->status === 'completed')
                                                <span class="badge bg-success">مكتمل</span>
                                            @elseif($payment->status === 'pending')
                                                <span class="badge bg-warning">قيد الانتظار</span>
                                            @elseif($payment->status === 'failed')
                                                <span class="badge bg-danger">فاشل</span>
                                            @else
                                                <span class="badge bg-info">مسترد</span>
                                            @endif
                                        </td>
                                        <td>{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            @php $purchaseCancelled = $payment->purchase && $payment->purchase->status === 'cancelled'; @endphp
                                            <div class="btn-group">
                                                <a href="{{ route('admin.payments.show', $payment->id) }}" class="btn btn-sm btn-info" title="عرض">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @if(!$purchaseCancelled && $payment->status === 'pending' && in_array($payment->payment_method, ['iban', 'custom']))
                                                    <form action="{{ route('admin.payments.approve', $payment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الموافقة على هذا الدفع؟');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" title="موافقة">
                                                            <i class="bi bi-check-circle"></i>
                                                        </button>
                                                    </form>
                                                    <button class="btn btn-sm btn-danger" onclick="rejectPayment({{ $payment->id }})" title="رفض">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-4" id="paginationContainer">
                        {{ $payments->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                        <h5 class="mt-3 mb-2">لا توجد مدفوعات</h5>
                        <p class="text-muted">لم يتم العثور على أي مدفوعات</p>
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
                    <input type="hidden" id="rejectPaymentId" name="payment_id" value="">
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

    rejectModalEl.addEventListener('hidden.bs.modal', function () {
        if (rejectForm) {
            rejectForm.reset();
            document.getElementById('rejectPaymentId').value = '';
        }
    });
})();

function rejectPayment(id) {
    document.getElementById('rejectPaymentId').value = id;
    var rejectForm = document.getElementById('rejectForm');
    if (rejectForm) {
        rejectForm.reset();
        document.getElementById('rejectPaymentId').value = id;
    }
    var modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}

document.getElementById('rejectForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    var paymentId = formData.get('payment_id');
    if (!paymentId) {
        return;
    }

    fetch('/admin/payments/' + encodeURIComponent(paymentId) + '/reject', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(function(response) {
        return response.json().then(function(data) {
            return { ok: response.ok, data: data };
        });
    })
    .then(function(result) {
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
    .catch(function(error) {
        console.error('Error:', error);
        alert('حدث خطأ أثناء رفض الدفع');
    });
});
</script>
@endpush
@endsection
