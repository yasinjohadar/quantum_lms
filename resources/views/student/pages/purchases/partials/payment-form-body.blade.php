@php
    $formId = $formId ?? 'paymentForm';
    $processPaymentUrl = $processPaymentUrl ?? route('student.purchases.process-payment', $purchase);
    $successUrl = $successUrl ?? route('student.classes');
    $ibanReceiptRequired = $ibanReceiptRequired ?? \App\Models\SystemSetting::ibanReceiptRequired();
    $ibanStudentInstructions = trim((string) ($ibanStudentInstructions ?? \App\Models\SystemSetting::ibanStudentInstructions()));
    $ibanInstructionsDisplay = $ibanStudentInstructions !== ''
        ? $ibanStudentInstructions
        : 'سيتم مراجعة الوصل من قبل الإدارة';
@endphp
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

        <div class="card custom-card">
            <div class="card-header">
                <h6 class="mb-0">اختر طريقة الدفع</h6>
            </div>
            <div class="card-body">
                <form
                    id="{{ $formId }}"
                    class="js-inline-purchase-payment-form"
                    action="#"
                    method="post"
                    enctype="multipart/form-data"
                    data-process-url="{{ $processPaymentUrl }}"
                    data-success-url="{{ $successUrl }}"
                >
                    @csrf
                    <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">

                    <div class="payment-method mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="{{ $formId }}_wallet" value="wallet" {{ $wallet && $wallet->balance >= $purchase->price ? '' : 'disabled' }}>
                            <label class="form-check-label w-100" for="{{ $formId }}_wallet">
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

                    <div class="payment-method mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="{{ $formId }}_iban" value="iban">
                            <label class="form-check-label w-100" for="{{ $formId }}_iban">
                                <div>
                                    <i class="bi bi-bank me-2 text-info"></i>
                                    <strong>تحويل بنكي (IBAN)</strong>
                                </div>
                            </label>
                        </div>
                        <div id="{{ $formId }}_ibanDetails" class="mt-3 js-iban-details" style="display: none;">
                            <div class="alert alert-light border mb-3 text-muted small" role="note">
                                {!! nl2br(e($ibanInstructionsDisplay)) !!}
                            </div>
                            <div class="alert alert-info mt-3">
                                <p class="mb-2"><strong>معلومات الحساب:</strong></p>
                                <p class="mb-0">IBAN: SA1234567890123456789012</p>
                                <p class="mb-0">اسم البنك: البنك الأهلي السعودي</p>
                            </div>
                            @if($ibanReceiptRequired)
                                <div class="mb-3">
                                    <label for="{{ $formId }}_receipt_file" class="form-label">رفع الوصل <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="{{ $formId }}_receipt_file" name="receipt_file" accept="image/*,application/pdf">
                                    <small class="text-muted">صيغ مدعومة: JPG, PNG, PDF (حجم أقصى 5MB)</small>
                                </div>
                            @endif
                        </div>
                    </div>

                    @foreach($customPaymentMethods as $method)
                        <div class="payment-method mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="{{ $formId }}_custom_{{ $method->id }}" value="custom" data-method-id="{{ $method->id }}">
                                <label class="form-check-label w-100" for="{{ $formId }}_custom_{{ $method->id }}">
                                    <div>
                                        <i class="bi bi-credit-card-2-front me-2 text-warning"></i>
                                        <strong>{{ $method->name }}</strong>
                                        @if($method->instructions)
                                            <small class="text-muted d-block">{{ $method->instructions }}</small>
                                        @endif
                                    </div>
                                </label>
                            </div>
                            <div id="{{ $formId }}_custom_{{ $method->id }}_details" class="mt-3 js-custom-details" style="display: none;">
                                @if($method->account_info)
                                    <div class="alert alert-info">
                                        @foreach($method->account_info as $key => $value)
                                            <p class="mb-1"><strong>{{ $key }}:</strong> {{ $value }}</p>
                                        @endforeach
                                    </div>
                                @endif
                                @if($method->requires_receipt)
                                    <div class="mb-3">
                                        <label for="{{ $formId }}_receipt_file_{{ $method->id }}" class="form-label">رفع الوصل <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control receipt-file" id="{{ $formId }}_receipt_file_{{ $method->id }}" data-method-id="{{ $method->id }}" name="receipt_file" accept="image/*,application/pdf">
                                        <small class="text-muted">صيغ مدعومة: JPG, PNG, PDF (حجم أقصى 5MB)</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <div class="payment-method mb-3 opacity-50">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="{{ $formId }}_stripe" value="stripe" disabled>
                            <label class="form-check-label" for="{{ $formId }}_stripe">
                                <i class="bi bi-credit-card me-2"></i>
                                <strong>بطاقة ائتمانية (Stripe)</strong>
                                <small class="text-muted d-block">قريباً</small>
                            </label>
                        </div>
                    </div>

                    <div class="payment-method mb-3 opacity-50">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="{{ $formId }}_paypal" value="paypal" disabled>
                            <label class="form-check-label" for="{{ $formId }}_paypal">
                                <i class="bi bi-paypal me-2"></i>
                                <strong>PayPal</strong>
                                <small class="text-muted d-block">قريباً</small>
                            </label>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary w-100 js-inline-payment-submit">
                            <i class="bi bi-check-circle me-2"></i>
                            تأكيد الدفع
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
