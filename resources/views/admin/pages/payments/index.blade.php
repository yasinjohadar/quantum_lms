@extends('admin.layouts.master')

@section('page-title')
    المدفوعات
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
                        <div class="col-md-3">
                            <label class="form-label">الحالة</label>
                            <select name="status" class="form-select" id="statusFilter">
                                <option value="">الكل</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>فاشل</option>
                                <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>مسترد</option>
                            </select>
                        </div>
                        <div class="col-md-3">
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
                        <div class="col-md-3">
                            <label class="form-label">البحث</label>
                            <input type="text" name="search" class="form-control" id="searchQuery" value="{{ request('search') }}" placeholder="اسم الطالب أو البريد الإلكتروني">
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i>بحث
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
                                    <th>الحالة</th>
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
                                            <div class="btn-group">
                                                <a href="{{ route('admin.payments.show', $payment->id) }}" class="btn btn-sm btn-info" title="عرض">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @if($payment->status === 'pending' && in_array($payment->payment_method, ['iban', 'custom']))
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
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">رفض الدفع</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="rejectPaymentId" name="payment_id">
                    <div class="mb-3">
                        <label for="rejectNotes" class="form-label">سبب الرفض <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectNotes" name="notes" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">رفض</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function rejectPayment(id) {
    document.getElementById('rejectPaymentId').value = id;
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}

document.getElementById('rejectForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const paymentId = formData.get('payment_id');
    
    fetch(`/admin/payments/${paymentId}/reject`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success || response.ok) {
            window.location.reload();
        } else {
            alert(data.message || 'حدث خطأ أثناء رفض الدفع');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ أثناء رفض الدفع');
    });
});
</script>
@endpush
@endsection
