@extends('admin.layouts.master')

@section('page-title')
    المدفوعات — الانضمامات
@stop

@push('styles')
    @include('admin.pages.payments.partials.payments-index-styles')
@endpush

@section('content')
<div class="main-content app-content payments-index-page">
    <div class="container-fluid">

        @php
            $needsReviewCount = \App\Models\Payment::needsReview()->count();
        @endphp

        <div class="payments-index-hero my-4">
            <div class="payments-index-hero__icon">
                <i class="bi bi-credit-card-fill"></i>
            </div>
            <div class="payments-index-hero__content">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2 small">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        @can('enrollment-list')
                            <li class="breadcrumb-item"><a href="{{ route('admin.enrollments.index') }}">الانضمامات</a></li>
                        @endcan
                        <li class="breadcrumb-item active" aria-current="page">المدفوعات</li>
                    </ol>
                </nav>
                <h4 class="payments-index-hero__title">المدفوعات</h4>
                <p class="payments-index-hero__subtitle">متابعة مدفوعات الانضمامات ومراجعتها</p>
            </div>
            <div class="payments-index-stat-mini">
                <span class="payments-index-stat-mini__value">{{ number_format($payments->total()) }}</span>
                <span class="payments-index-stat-mini__label">دفعة مطابقة</span>
            </div>
            <div class="payments-index-hero__actions">
                @if($needsReviewCount > 0)
                    <a href="{{ route('admin.payments.index', ['needs_review' => 1]) }}" class="btn btn-sm btn-warning position-relative">
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
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="payments-index-card">
            <div class="payments-index-card__header">
                <div class="d-flex align-items-center gap-2">
                    <span class="payments-index-card__header-icon"><i class="bi bi-funnel"></i></span>
                    تصفية وبحث
                </div>
            </div>
            <div class="payments-index-card__body">
                <form method="GET" action="{{ route('admin.payments.index') }}" id="paymentsFilterForm" class="payments-index-filters">
                    <div class="row g-3">
                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label">الحالة</label>
                            <select name="status" class="form-select" id="statusFilter">
                                <option value="">الكل</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>فاشل</option>
                                <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>مسترد</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-2">
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
                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label">نوع الشراء</label>
                            <select name="purchase_type" class="form-select" id="purchaseTypeFilter">
                                <option value="">الكل</option>
                                <option value="class" {{ request('purchase_type') == 'class' ? 'selected' : '' }}>صف</option>
                                <option value="subject" {{ request('purchase_type') == 'subject' ? 'selected' : '' }}>مادة</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label">إلى تاريخ</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label">البحث</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0" id="searchQuery"
                                       value="{{ request('search') }}" placeholder="اسم الطالب أو البريد">
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 align-items-end mt-1">
                        <div class="col-12 col-lg-4">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" name="needs_review" value="1" id="needsReviewFilter" {{ request()->boolean('needs_review') ? 'checked' : '' }}>
                                <label class="form-check-label small" for="needsReviewFilter">عرض المدفوعات التي تحتاج مراجعة فقط</label>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="form-label" for="purchaseStatusFilter">حالة الشراء</label>
                            <select name="purchase_status" class="form-select" id="purchaseStatusFilter">
                                <option value="">الكل</option>
                                <option value="pending" {{ request('purchase_status') === 'pending' ? 'selected' : '' }}>شراء معلّق</option>
                                <option value="completed" {{ request('purchase_status') === 'completed' ? 'selected' : '' }}>شراء مكتمل</option>
                                <option value="cancelled" {{ request('purchase_status') === 'cancelled' ? 'selected' : '' }}>ملغى نهائي</option>
                                <option value="refunded" {{ request('purchase_status') === 'refunded' ? 'selected' : '' }}>شراء مسترد</option>
                            </select>
                        </div>
                        <div class="col-12 col-lg-5 d-flex flex-wrap gap-2 justify-content-lg-end">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-search me-1"></i> تطبيق الفلاتر
                            </button>
                            <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-lg me-1"></i> مسح
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="payments-index-card">
            <div class="payments-index-card__header">
                <div class="d-flex align-items-center gap-2">
                    <span class="payments-index-card__header-icon"><i class="bi bi-table"></i></span>
                    قائمة المدفوعات
                </div>
                @if($payments->hasPages())
                    <span class="badge bg-primary-transparent text-primary">
                        صفحة {{ $payments->currentPage() }} من {{ $payments->lastPage() }}
                    </span>
                @endif
            </div>
            <div class="payments-index-card__body p-0">
                @if($payments->count() > 0)
                    <div class="payments-index-table-wrap mx-3 mt-3 mb-0">
                        <div class="table-responsive">
                            <table class="table payments-index-table align-middle mb-0">
                                <thead>
                                <tr>
                                    <th scope="col" style="width: 48px;">#</th>
                                    <th scope="col">الطالب</th>
                                    <th scope="col">العنصر</th>
                                    <th scope="col">المبلغ</th>
                                    <th scope="col" class="payments-col-method">طريقة الدفع</th>
                                    <th scope="col" class="payments-col-purchase-status">حالة الشراء</th>
                                    <th scope="col">حالة الدفع</th>
                                    <th scope="col" class="payments-col-date">التاريخ</th>
                                    <th scope="col" style="min-width: 100px;">الإجراءات</th>
                                </tr>
                                </thead>
                                <tbody id="paymentsTableBody">
                                @foreach($payments as $payment)
                                    @php
                                        $user = $payment->purchase->user ?? null;
                                        $initial = $user ? mb_strtoupper(mb_substr(trim($user->name), 0, 1)) : '—';
                                        $pur = $payment->purchase;
                                        $purchaseCancelled = $pur && $pur->status === 'cancelled';
                                    @endphp
                                    <tr>
                                        <th scope="row" class="text-muted small">{{ $payment->id }}</th>
                                        <td>
                                            @if($user)
                                                <div class="ui-user-cell">
                                                    <span class="ui-user-avatar">{{ $initial }}</span>
                                                    <div class="min-width-0">
                                                        <div class="ui-user-name text-truncate">{{ $user->name }}</div>
                                                        <small class="text-muted text-truncate d-block">{{ $user->email }}</small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">غير محدد</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="ui-payment-item">{{ $payment->purchase->purchasable->name ?? 'غير محدد' }}</div>
                                            <div class="ui-payment-item-meta payments-col-item-type">
                                                {{ $payment->purchase->purchase_type === 'class' ? 'صف' : 'مادة' }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="ui-payment-amount">{{ number_format($payment->amount, 2) }} ر.س</span>
                                        </td>
                                        <td class="payments-col-method">
                                            @if($payment->payment_method === 'wallet')
                                                <span class="ui-method-pill ui-method-pill--wallet"><i class="bi bi-wallet2"></i> محفظة</span>
                                            @elseif($payment->payment_method === 'iban')
                                                <span class="ui-method-pill ui-method-pill--iban"><i class="bi bi-bank"></i> IBAN</span>
                                            @elseif($payment->payment_method === 'custom')
                                                <span class="ui-method-pill ui-method-pill--custom"><i class="bi bi-sliders"></i> {{ $payment->customPaymentMethod->name ?? 'مخصص' }}</span>
                                            @else
                                                <span class="ui-method-pill ui-method-pill--other">{{ $payment->payment_method }}</span>
                                            @endif
                                        </td>
                                        <td class="payments-col-purchase-status">
                                            @if(!$pur)
                                                <span class="text-muted">—</span>
                                            @elseif($pur->status === 'cancelled')
                                                <span class="ui-status-pill ui-status-pill--dark">
                                                    ملغى
                                                    @if($pur->cancelled_by === 'student') (الطالب) @elseif($pur->cancelled_by === 'admin') (الإدارة) @endif
                                                </span>
                                            @elseif($pur->status === 'pending')
                                                <span class="ui-status-pill ui-status-pill--warning">قيد المراجعة</span>
                                            @elseif($pur->status === 'completed')
                                                <span class="ui-status-pill ui-status-pill--success">مكتمل</span>
                                            @elseif($pur->status === 'refunded')
                                                <span class="ui-status-pill ui-status-pill--info">مسترد</span>
                                            @else
                                                <span class="ui-status-pill ui-status-pill--secondary">{{ $pur->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($payment->status === 'completed')
                                                <span class="ui-status-pill ui-status-pill--success">مكتمل</span>
                                            @elseif($payment->status === 'pending')
                                                <span class="ui-status-pill ui-status-pill--warning">قيد الانتظار</span>
                                            @elseif($payment->status === 'failed')
                                                <span class="ui-status-pill ui-status-pill--danger">فاشل</span>
                                            @else
                                                <span class="ui-status-pill ui-status-pill--info">مسترد</span>
                                            @endif
                                        </td>
                                        <td class="payments-col-date">
                                            <div class="ui-date-cell">
                                                {{ $payment->created_at->format('Y-m-d') }}
                                                <small>{{ $payment->created_at->format('H:i') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="row-action-bar">
                                                <a href="{{ route('admin.payments.show', $payment->id) }}"
                                                   class="row-action-btn row-action-btn--info" title="عرض">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @if(!$purchaseCancelled && $payment->status === 'pending' && in_array($payment->payment_method, ['iban', 'custom']))
                                                    <form action="{{ route('admin.payments.approve', $payment->id) }}" method="POST" class="row-action-form"
                                                          onsubmit="return confirm('هل أنت متأكد من الموافقة على هذا الدفع؟');">
                                                        @csrf
                                                        <button type="submit" class="row-action-btn row-action-btn--success" title="موافقة">
                                                            <i class="bi bi-check-lg"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="row-action-btn row-action-btn--danger"
                                                            onclick="rejectPayment({{ $payment->id }})" title="رفض">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="payments-index-pagination" id="paginationContainer">
                        {{ $payments->withQueryString()->links() }}
                    </div>
                @else
                    <div class="payments-index-empty py-5">
                        <i class="bi bi-inbox"></i>
                        <p class="mb-0 fw-semibold">لا توجد مدفوعات</p>
                        <p class="small mb-0 mt-1">لم يتم العثور على أي مدفوعات مطابقة للفلاتر</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
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

    if (rejectModalEl) {
        rejectModalEl.addEventListener('hidden.bs.modal', function () {
            if (rejectForm) {
                rejectForm.reset();
                document.getElementById('rejectPaymentId').value = '';
            }
        });
    }
})();

function rejectPayment(id) {
    document.getElementById('rejectPaymentId').value = id;
    var rejectForm = document.getElementById('rejectForm');
    if (rejectForm) {
        rejectForm.reset();
        document.getElementById('rejectPaymentId').value = id;
    }
    bootstrap.Modal.getOrCreateInstance(document.getElementById('rejectModal')).show();
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
