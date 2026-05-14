<div class="inline-purchase-payment-root">
    @include('student.pages.purchases.partials.payment-form-body', [
        'purchase' => $purchase,
        'wallet' => $wallet,
        'customPaymentMethods' => $customPaymentMethods,
        'formId' => 'enrollInlinePaymentForm',
        'processPaymentUrl' => route('student.purchases.process-payment', $purchase),
        'successUrl' => $afterSuccessUrl,
    ])
</div>
