@extends('student.layouts.master')

@section('page-title')
    معالجة الدفع
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">معالجة الدفع</h4>
                <p class="mb-0 text-muted">اختر طريقة الدفع المناسبة</p>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <div class="col-xl-8">
                <div class="card custom-card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-credit-card me-2"></i>
                            معلومات الشراء
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">العنصر:</span>
                            <strong>{{ $purchase->purchasable->name ?? 'غير محدد' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">النوع:</span>
                            <strong>{{ $purchase->purchase_type === 'class' ? 'صف كامل' : 'مادة' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">المبلغ:</span>
                            <h5 class="mb-0 text-primary">{{ number_format($purchase->price, 2) }} ر.س</h5>
                        </div>
                    </div>
                </div>

                <!-- طرق الدفع -->
                <div class="card custom-card">
                    <div class="card-header">
                        <h6 class="mb-0">اختر طريقة الدفع</h6>
                    </div>
                    <div class="card-body">
                        <form id="paymentForm">
                            @csrf
                            <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
                            
                            <!-- المحفظة الإلكترونية -->
                            <div class="payment-method mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="wallet" value="wallet" {{ $wallet && $wallet->balance >= $purchase->price ? '' : 'disabled' }}>
                                    <label class="form-check-label w-100" for="wallet">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="bi bi-wallet2 me-2 text-primary"></i>
                                                <strong>المحفظة الإلكترونية</strong>
                                                @if($wallet)
                                                    <small class="text-muted d-block">الرصيد المتاح: {{ number_format($wallet->balance, 2) }} ر.س</small>
                                                @endif
                                            </div>
                                            @if($wallet && $wallet->balance >= $purchase->price)
                                                <span class="badge bg-success">متاح</span>
                                            @else
                                                <span class="badge bg-danger">رصيد غير كافٍ</span>
                                            @endif
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- IBAN -->
                            <div class="payment-method mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="iban" value="iban">
                                    <label class="form-check-label w-100" for="iban">
                                        <div>
                                            <i class="bi bi-bank me-2 text-info"></i>
                                            <strong>تحويل بنكي (IBAN)</strong>
                                            <small class="text-muted d-block">سيتم مراجعة الوصل من قبل الإدارة</small>
                                        </div>
                                    </label>
                                </div>
                                <div id="ibanDetails" class="mt-3" style="display: none;">
                                    <div class="alert alert-info">
                                        <p class="mb-2"><strong>معلومات الحساب:</strong></p>
                                        <p class="mb-0">IBAN: SA1234567890123456789012</p>
                                        <p class="mb-0">اسم البنك: البنك الأهلي السعودي</p>
                                    </div>
                                    <div class="mb-3">
                                        <label for="receipt_file" class="form-label">رفع الوصل <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control" id="receipt_file" name="receipt_file" accept="image/*,application/pdf">
                                        <small class="text-muted">صيغ مدعومة: JPG, PNG, PDF (حجم أقصى 5MB)</small>
                                    </div>
                                </div>
                            </div>

                            <!-- وسائل الدفع المخصصة -->
                            @foreach($customPaymentMethods as $method)
                                <div class="payment-method mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="custom_{{ $method->id }}" value="custom" data-method-id="{{ $method->id }}">
                                        <label class="form-check-label w-100" for="custom_{{ $method->id }}">
                                            <div>
                                                <i class="bi bi-credit-card-2-front me-2 text-warning"></i>
                                                <strong>{{ $method->name }}</strong>
                                                @if($method->instructions)
                                                    <small class="text-muted d-block">{{ $method->instructions }}</small>
                                                @endif
                                            </div>
                                        </label>
                                    </div>
                                    <div id="custom_{{ $method->id }}_details" class="mt-3" style="display: none;">
                                        @if($method->account_info)
                                            <div class="alert alert-info">
                                                @foreach($method->account_info as $key => $value)
                                                    <p class="mb-1"><strong>{{ $key }}:</strong> {{ $value }}</p>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if($method->requires_receipt)
                                            <div class="mb-3">
                                                <label for="receipt_file_{{ $method->id }}" class="form-label">رفع الوصل <span class="text-danger">*</span></label>
                                                <input type="file" class="form-control receipt-file" data-method-id="{{ $method->id }}" name="receipt_file" accept="image/*,application/pdf">
                                                <small class="text-muted">صيغ مدعومة: JPG, PNG, PDF (حجم أقصى 5MB)</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            <!-- Stripe (مؤقتاً معطل) -->
                            <div class="payment-method mb-3 opacity-50">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="stripe" value="stripe" disabled>
                                    <label class="form-check-label" for="stripe">
                                        <i class="bi bi-credit-card me-2"></i>
                                        <strong>بطاقة ائتمانية (Stripe)</strong>
                                        <small class="text-muted d-block">قريباً</small>
                                    </label>
                                </div>
                            </div>

                            <!-- PayPal (مؤقتاً معطل) -->
                            <div class="payment-method mb-3 opacity-50">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal" disabled>
                                    <label class="form-check-label" for="paypal">
                                        <i class="bi bi-paypal me-2"></i>
                                        <strong>PayPal</strong>
                                        <small class="text-muted d-block">قريباً</small>
                                    </label>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                                    <i class="bi bi-check-circle me-2"></i>
                                    تأكيد الدفع
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ملخص -->
            <div class="col-xl-4">
                <div class="card custom-card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">ملخص الطلب</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>العنصر:</span>
                            <strong>{{ $purchase->purchasable->name ?? 'غير محدد' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>المبلغ:</span>
                            <strong class="text-primary">{{ number_format($purchase->price, 2) }} ر.س</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>الإجمالي:</strong>
                            <strong class="text-primary">{{ number_format($purchase->price, 2) }} ر.س</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End::app-content -->

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // إظهار/إخفاء تفاصيل طريقة الدفع
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            // إخفاء جميع التفاصيل
            document.querySelectorAll('[id$="_details"]').forEach(detail => {
                detail.style.display = 'none';
            });
            
            // إظهار تفاصيل الطريقة المختارة
            if (this.value === 'iban') {
                document.getElementById('ibanDetails').style.display = 'block';
            } else if (this.value === 'custom') {
                const methodId = this.getAttribute('data-method-id');
                if (methodId) {
                    document.getElementById('custom_' + methodId + '_details').style.display = 'block';
                }
            }
        });
    });

    // معالجة النموذج
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
        
        if (!paymentMethod) {
            alert('يرجى اختيار طريقة الدفع');
            return;
        }
        
        if (paymentMethod.value === 'custom') {
            const methodId = paymentMethod.getAttribute('data-method-id');
            formData.append('custom_payment_method_id', methodId);
        }
        
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>جاري المعالجة...';
        
        fetch('{{ route("student.purchases.process-payment", $purchase->id) }}', {
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
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    window.location.href = '{{ route("student.classes") }}';
                }
            } else {
                alert(data.message || 'حدث خطأ أثناء معالجة الدفع');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>تأكيد الدفع';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء معالجة الدفع');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>تأكيد الدفع';
        });
    });
});
</script>
@endpush
@endsection
