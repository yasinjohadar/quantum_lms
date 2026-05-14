@extends('frontend.layouts.master')

@section('content')

@php
    $ibanReceiptRequired = $ibanReceiptRequired ?? true;
    $ibanStudentInstructions = trim((string) ($ibanStudentInstructions ?? ''));
    $ibanInstructionsDisplay = $ibanStudentInstructions !== ''
        ? $ibanStudentInstructions
        : 'سيتم مراجعة الوصل من قبل الإدارة';
@endphp

<!-- Payment Section Start -->
<section class="payment-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <!-- Purchase Info -->
                <div class="payment-card mb-4">
                    <h3 class="payment-card-title">
                        <i class="fa-solid fa-shopping-bag me-2"></i>
                        معلومات الشراء
                    </h3>
                    
                    <div class="payment-info">
                        <div class="payment-info-item">
                            <span class="payment-info-label">العنصر:</span>
                            <strong class="payment-info-value">{{ $purchase->purchasable->name ?? 'غير محدد' }}</strong>
                        </div>
                        <div class="payment-info-item">
                            <span class="payment-info-label">النوع:</span>
                            <strong class="payment-info-value">{{ $purchase->purchase_type === 'class' ? 'صف كامل' : 'مادة' }}</strong>
                        </div>
                        <div class="payment-info-item">
                            <span class="payment-info-label">المبلغ:</span>
                            <strong class="payment-info-value payment-amount">{{ number_format($purchase->price, 2) }} ر.س</strong>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="payment-card">
                    <h3 class="payment-card-title">
                        <i class="fa-solid fa-credit-card me-2"></i>
                        اختر طريقة الدفع
                    </h3>
                    
                    <form id="paymentForm">
                        @csrf
                        <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
                        
                        <!-- Wallet -->
                        <div class="payment-method mb-3">
                            <label class="payment-method-label">
                                <input type="radio" name="payment_method" value="wallet" id="wallet" class="payment-method-radio" {{ $wallet && $wallet->balance >= $purchase->price ? '' : 'disabled' }}>
                                <div class="payment-method-content">
                                    <div class="payment-method-header">
                                        <div>
                                            <i class="fa-solid fa-wallet me-2"></i>
                                            <strong>المحفظة الإلكترونية</strong>
                                            @if($wallet)
                                                <small class="payment-method-desc">الرصيد المتاح: {{ number_format($wallet->balance, 2) }} ر.س</small>
                                            @endif
                                        </div>
                                        @if($wallet && $wallet->balance >= $purchase->price)
                                            <span class="payment-method-badge badge-success">متاح</span>
                                        @else
                                            <span class="payment-method-badge badge-danger">رصيد غير كافٍ</span>
                                        @endif
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- IBAN -->
                        <div class="payment-method mb-3">
                            <label class="payment-method-label">
                                <input type="radio" name="payment_method" value="iban" id="iban" class="payment-method-radio">
                                <div class="payment-method-content">
                                    <div class="payment-method-header">
                                        <div>
                                            <i class="fa-solid fa-university me-2"></i>
                                            <strong>تحويل بنكي (IBAN)</strong>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            <div id="ibanDetails" class="payment-method-details" style="display: none;">
                                <div class="alert alert-light border mb-3 text-muted small" role="note">
                                    {!! nl2br(e($ibanInstructionsDisplay)) !!}
                                </div>
                                <div class="alert alert-info">
                                    <p class="mb-2"><strong>معلومات الحساب:</strong></p>
                                    <p class="mb-0">IBAN: SA1234567890123456789012</p>
                                    <p class="mb-0">اسم البنك: البنك الأهلي السعودي</p>
                                </div>
                                @if($ibanReceiptRequired)
                                    <div class="mb-3">
                                        <label for="receipt_file" class="form-label">رفع الوصل <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control" id="receipt_file" name="receipt_file" accept="image/*,application/pdf">
                                        <small class="text-muted">صيغ مدعومة: JPG, PNG, PDF (حجم أقصى 5MB)</small>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Custom Payment Methods -->
                        @foreach($customPaymentMethods as $method)
                            <div class="payment-method mb-3">
                                <label class="payment-method-label">
                                    <input type="radio" name="payment_method" value="custom" id="custom_{{ $method->id }}" class="payment-method-radio" data-method-id="{{ $method->id }}">
                                    <div class="payment-method-content">
                                        <div class="payment-method-header">
                                            <div>
                                                <i class="fa-solid fa-credit-card me-2"></i>
                                                <strong>{{ $method->name }}</strong>
                                                @if($method->instructions)
                                                    <small class="payment-method-desc">{{ $method->instructions }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </label>
                                <div id="custom_{{ $method->id }}_details" class="payment-method-details" style="display: none;">
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

                        <!-- Stripe (Disabled) -->
                        <div class="payment-method mb-3 payment-method-disabled">
                            <label class="payment-method-label">
                                <input type="radio" name="payment_method" value="stripe" id="stripe" class="payment-method-radio" disabled>
                                <div class="payment-method-content">
                                    <div class="payment-method-header">
                                        <div>
                                            <i class="fa-brands fa-cc-stripe me-2"></i>
                                            <strong>بطاقة ائتمانية (Stripe)</strong>
                                            <small class="payment-method-desc">قريباً</small>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- PayPal (Disabled) -->
                        <div class="payment-method mb-3 payment-method-disabled">
                            <label class="payment-method-label">
                                <input type="radio" name="payment_method" value="paypal" id="paypal" class="payment-method-radio" disabled>
                                <div class="payment-method-content">
                                    <div class="payment-method-header">
                                        <div>
                                            <i class="fa-brands fa-paypal me-2"></i>
                                            <strong>PayPal</strong>
                                            <small class="payment-method-desc">قريباً</small>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary btn-lg w-100 payment-submit-btn" id="submitBtn">
                                <i class="fa-solid fa-check-circle me-2"></i>
                                تأكيد الدفع
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="payment-card">
                    <h3 class="payment-card-title">
                        <i class="fa-solid fa-receipt me-2"></i>
                        ملخص الطلب
                    </h3>
                    
                    <div class="payment-summary">
                        <div class="payment-summary-item">
                            <span>العنصر:</span>
                            <strong>{{ $purchase->purchasable->name ?? 'غير محدد' }}</strong>
                        </div>
                        <div class="payment-summary-item">
                            <span>المبلغ:</span>
                            <strong class="payment-summary-amount">{{ number_format($purchase->price, 2) }} ر.س</strong>
                        </div>
                        <hr class="payment-summary-divider">
                        <div class="payment-summary-item payment-summary-total">
                            <span>الإجمالي:</span>
                            <strong class="payment-summary-total-amount">{{ number_format($purchase->price, 2) }} ر.س</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Payment Section End -->

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ibanReceiptRequired = @json($ibanReceiptRequired);
    // إظهار/إخفاء تفاصيل طريقة الدفع
    document.querySelectorAll('.payment-method-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            // إخفاء جميع التفاصيل
            document.querySelectorAll('.payment-method-details').forEach(detail => {
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
        
        if (!paymentMethod || paymentMethod.disabled) {
            alert('يرجى اختيار طريقة الدفع');
            return;
        }
        
        // التحقق من وجود الملف للـ IBAN (عند تفعيل الإلزام من الإعدادات)
        if (paymentMethod.value === 'iban' && ibanReceiptRequired) {
            const receiptFile = document.getElementById('receipt_file');
            if (!receiptFile || !receiptFile.files || receiptFile.files.length === 0) {
                alert('يرجى رفع وصل الدفع');
                return;
            }
            
            // التحقق من نوع الملف
            const file = receiptFile.files[0];
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
            if (!allowedTypes.includes(file.type)) {
                alert('نوع الملف غير مدعوم. يرجى رفع ملف JPG, PNG, أو PDF');
                return;
            }
            
            // التحقق من حجم الملف (5MB)
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (file.size > maxSize) {
                alert('حجم الملف كبير جداً. الحد الأقصى 5MB');
                return;
            }
            
            // إضافة الملف إلى FormData
            formData.append('receipt_file', file);
        }
        
        // التحقق من وجود الملف للـ Custom methods
        if (paymentMethod.value === 'custom') {
            const methodId = paymentMethod.getAttribute('data-method-id');
            formData.append('custom_payment_method_id', methodId);
            
            // التحقق من وجود ملف إذا كان مطلوباً
            const receiptFileInput = document.querySelector(`#custom_${methodId}_details input[type="file"]`);
            if (receiptFileInput && receiptFileInput.files && receiptFileInput.files.length > 0) {
                const file = receiptFileInput.files[0];
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
                if (!allowedTypes.includes(file.type)) {
                    alert('نوع الملف غير مدعوم. يرجى رفع ملف JPG, PNG, أو PDF');
                    return;
                }
                const maxSize = 5 * 1024 * 1024;
                if (file.size > maxSize) {
                    alert('حجم الملف كبير جداً. الحد الأقصى 5MB');
                    return;
                }
                formData.append('receipt_file', file);
            }
        }
        
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>جاري المعالجة...';
        
        fetch('{{ route("frontend.payment.process", $purchase->id) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // إذا لم يكن JSON، محاولة قراءة النص
                const text = await response.text();
                console.error('Non-JSON response:', text);
                throw new Error('استجابة غير صحيحة من الخادم');
            }
        })
        .then(data => {
            if (data.success) {
                alert(data.message);
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    @if($class)
                        window.location.href = '{{ route("frontend.class.show", $class->slug) }}';
                    @else
                        window.location.href = '/';
                    @endif
                }
            } else {
                // عرض رسالة الخطأ من الـ server
                const errorMessage = data.message || data.error || 'حدث خطأ أثناء معالجة الدفع';
                alert(errorMessage);
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-check-circle me-2"></i>تأكيد الدفع';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            let errorMessage = 'حدث خطأ أثناء معالجة الدفع';
            if (error.message) {
                errorMessage = error.message;
            }
            alert(errorMessage);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-check-circle me-2"></i>تأكيد الدفع';
        });
    });
});
</script>
@endpush
