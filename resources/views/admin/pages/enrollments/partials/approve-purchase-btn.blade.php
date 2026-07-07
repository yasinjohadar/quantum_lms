@php
    $approvalDefaults = \App\Support\PurchaseApprovalExpiryDefaults::resolve($purchase);
@endphp
<button type="button"
        class="btn btn-success btn-sm approve-purchase-btn"
        data-action="{{ route('admin.payments.pending-purchases.approve', $purchase) }}"
        data-student="{{ $studentName }}"
        data-item="{{ $itemName }}"
        data-type-label="{{ $typeLabel }}"
        data-default-expires="{{ $approvalDefaults['default_expires_at']->format('Y-m-d') }}"
        data-class-subscription-ends="{{ $approvalDefaults['class_subscription_ends_at']?->format('Y-m-d') }}"
        data-class-name="{{ $approvalDefaults['class']?->name }}"
        data-max-expires="{{ $approvalDefaults['max_expires_at']?->format('Y-m-d') }}">
    <i class="bi bi-check-lg me-1"></i> {{ $buttonLabel ?? 'قبول' }}
</button>
