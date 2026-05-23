@php
    $formId = $formId ?? 'paymentForm';
    $processPaymentUrl = $processPaymentUrl ?? route('student.purchases.process-payment', $purchase);
    $successUrl = $successUrl ?? route('student.classes');
    $ibanReceiptRequired = $ibanReceiptRequired ?? \App\Models\SystemSetting::ibanReceiptRequired();
    $ibanStudentInstructions = trim((string) ($ibanStudentInstructions ?? \App\Models\SystemSetting::ibanStudentInstructions()));
    $ibanInstructionsDisplay = $ibanStudentInstructions !== ''
        ? $ibanStudentInstructions
        : 'سيتم مراجعة الوصل من قبل الإدارة';
    $ibanDisplayName = $ibanDisplayName ?? \App\Models\SystemSetting::ibanDisplayName();
    $ibanAccount = $ibanAccount ?? \App\Models\SystemSetting::ibanAccountDetails();
    $ibanPendingMessage = $ibanPendingMessage ?? \App\Models\SystemSetting::ibanPendingMessage();
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
                <h6 class="mb-0">طريقة الدفع</h6>
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
                    <input type="hidden" name="payment_method" value="iban">

                    <div class="alert alert-warning border mb-2" role="status">
                        <i class="bi bi-info-circle me-2"></i>
                        {!! linkify_plain_text($ibanPendingMessage) !!}
                    </div>
                    @include('student.partials.supervisor-whatsapp-cta', ['wrapperClass' => 'mb-4'])

                    <div class="payment-method mb-3">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-bank me-2 text-info fs-4"></i>
                            <strong class="fs-5">{{ $ibanDisplayName }}</strong>
                        </div>

                        <div class="js-iban-details">
                            <div class="alert alert-light border mb-2 text-muted small" role="note">
                                {!! linkify_plain_text($ibanInstructionsDisplay) !!}
                            </div>
                            @include('student.partials.supervisor-whatsapp-cta', ['wrapperClass' => 'mb-3'])
                            <div class="alert alert-info">
                                <p class="mb-2"><strong>معلومات الحساب:</strong></p>
                                @if(!empty($ibanAccount['iban']))
                                    <p class="mb-0">IBAN: {{ $ibanAccount['iban'] }}</p>
                                @endif
                                @if(!empty($ibanAccount['bank_name']))
                                    <p class="mb-0">اسم البنك: {{ $ibanAccount['bank_name'] }}</p>
                                @endif
                                @if(!empty($ibanAccount['account_holder']))
                                    <p class="mb-0">صاحب الحساب: {{ $ibanAccount['account_holder'] }}</p>
                                @endif
                            </div>
                            @if($ibanReceiptRequired)
                                <div class="mb-3">
                                    <label for="{{ $formId }}_receipt_file" class="form-label">رفع الوصل <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="{{ $formId }}_receipt_file" name="receipt_file" accept="image/*,application/pdf" {{ $ibanReceiptRequired ? 'required' : '' }}>
                                    <small class="text-muted">صيغ مدعومة: JPG, PNG, PDF (حجم أقصى 5MB)</small>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary w-100 js-inline-payment-submit">
                            <i class="bi bi-check-circle me-2"></i>
                            تأكيد الدفع وإرسال الطلب
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
