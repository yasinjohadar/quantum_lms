@extends('student.layouts.master')

@section('page-title')
    المحفظة الإلكترونية
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">المحفظة الإلكترونية</h4>
                <p class="mb-0 text-muted">إدارة رصيدك المالي</p>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <!-- رصيد المحفظة -->
            <div class="col-xl-4 mb-4">
                <div class="card custom-card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-wallet2 me-2"></i>
                            الرصيد الحالي
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        <h2 class="text-primary mb-3">{{ number_format($wallet->balance, 2) }} <small>ر.س</small></h2>
                        <p class="text-muted mb-4">رصيدك المتاح للاستخدام</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#depositModal">
                            <i class="bi bi-plus-circle me-2"></i>
                            شحن المحفظة
                        </button>
                    </div>
                </div>
            </div>

            <!-- آخر المعاملات -->
            <div class="col-xl-8">
                <div class="card custom-card">
                    <div class="card-header">
                        <h6 class="mb-0">آخر المعاملات</h6>
                    </div>
                    <div class="card-body">
                        @if($transactions->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>النوع</th>
                                            <th>المبلغ</th>
                                            <th>الوصف</th>
                                            <th>التاريخ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($transactions as $transaction)
                                            <tr>
                                                <td>
                                                    @if($transaction->type === 'deposit')
                                                        <span class="badge bg-success">إيداع</span>
                                                    @elseif($transaction->type === 'withdrawal')
                                                        <span class="badge bg-danger">سحب</span>
                                                    @elseif($transaction->type === 'purchase')
                                                        <span class="badge bg-info">شراء</span>
                                                    @else
                                                        <span class="badge bg-warning">استرداد</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <strong class="{{ $transaction->type === 'deposit' || $transaction->type === 'refund' ? 'text-success' : 'text-danger' }}">
                                                        {{ $transaction->type === 'deposit' || $transaction->type === 'refund' ? '+' : '-' }}{{ number_format($transaction->amount, 2) }} ر.س
                                                    </strong>
                                                </td>
                                                <td>{{ $transaction->description }}</td>
                                                <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center mt-3">
                                {{ $transactions->links() }}
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">لا توجد معاملات بعد</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End::app-content -->

<!-- Modal شحن المحفظة -->
<div class="modal fade" id="depositModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">شحن المحفظة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="depositForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="amount" class="form-label">المبلغ (ر.س)</label>
                        <input type="number" class="form-control" id="amount" name="amount" min="1" max="100000" required>
                        <small class="text-muted">الحد الأدنى: 1 ر.س | الحد الأقصى: 100,000 ر.س</small>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">الوصف (اختياري)</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">شحن</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('depositForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'جاري الشحن...';
    
    fetch('{{ route("student.wallet.deposit") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert(data.message || 'حدث خطأ أثناء شحن المحفظة');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'شحن';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ أثناء شحن المحفظة');
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'شحن';
    });
});
</script>
@endpush
@endsection
