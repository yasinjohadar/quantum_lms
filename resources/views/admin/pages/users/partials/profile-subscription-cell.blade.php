@php
    $canEditSubscription = auth()->user()?->can('user-edit');
    $isExpired = ($subscription['expires_at'] ?? null) && $subscription['expires_at']->isPast();
    $canEditRow = $canEditSubscription
        && (($subscription['editable'] ?? false) && (($subscription['purchase_id'] ?? null) || ($subscription['settable'] ?? false)));
    $isClassSource = ($subscription['source'] ?? '') === 'class';
    $hasDate = ($subscription['expires_at_input'] ?? '') !== '';
@endphp

<div class="user-sub-cell-wrap user-edit-sub-row {{ $isExpired ? 'user-edit-sub-row--expired' : '' }}"
     data-class-id="{{ $subscription['class_id'] ?? '' }}"
     data-purchase-id="{{ $subscription['purchase_id'] ?? '' }}"
     data-update-url="{{ route('users.update-subscription-expires', $user) }}">

    @if ($canEditRow)
        <input type="date"
               class="user-sub-date user-edit-sub-date"
               value="{{ $subscription['expires_at_input'] ?? '' }}"
               min="{{ now()->format('Y-m-d') }}"
               @if (! empty($subscription['max_expires_at'])) max="{{ $subscription['max_expires_at'] }}" @endif
               title="{{ $hasDate ? 'تعديل تاريخ الانتهاء' : 'تعيين تاريخ الانتهاء' }}">
        <span class="user-sub-status user-edit-sub-row__status" aria-live="polite"></span>
    @elseif ($subscription['expires_at'] ?? null)
        <span class="user-sub-readonly {{ $isExpired ? 'user-sub-readonly--expired' : '' }}">
            <i class="bi bi-calendar-event"></i>
            {{ $subscription['expires_at']->format('Y-m-d') }}
        </span>
        @if ($isClassSource)
            <span class="user-sub-pill">من الصف</span>
        @endif
        @if ($isExpired)
            <span class="user-sub-pill user-sub-pill--danger">منتهي</span>
        @endif
    @else
        <span class="user-sub-readonly user-sub-readonly--class text-muted">غير محدد</span>
    @endif
</div>
