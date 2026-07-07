@php
    $subscription = \App\Support\StudentSubscriptionExpiryResolver::resolveForUser(
        $user,
        $selectedClassId ?? null
    );
    $isExpired = $subscription['expires_at'] && $subscription['expires_at']->isPast();
    $canEdit = ($subscription['editable'] ?? false) && auth()->user()?->can('user-edit');
@endphp
<td class="subscription-expires-col">
    @if ($canEdit && (($subscription['purchase_id'] ?? null) || ($subscription['settable'] ?? false)))
        <button type="button"
                class="subscription-expires-inline {{ $isExpired ? 'subscription-expires-inline--expired' : '' }} {{ ($subscription['settable'] ?? false) && !($subscription['expires_at'] ?? null) ? 'subscription-expires-inline--unset' : '' }}"
                data-user-id="{{ $user->id }}"
                data-purchase-id="{{ $subscription['purchase_id'] ?? '' }}"
                data-class-id="{{ $subscription['class_id'] ?? ($selectedClassId ?? '') }}"
                data-expires-at="{{ $subscription['expires_at_input'] }}"
                data-max-expires="{{ $subscription['max_expires_at'] ?? '' }}"
                data-update-url="{{ route('users.update-subscription-expires', $user) }}"
                title="{{ ($subscription['settable'] ?? false) && !($subscription['expires_at'] ?? null) ? 'انقر لتعيين تاريخ نهاية الاشتراك' : 'انقر لتعديل تاريخ نهاية الاشتراك' }}">
            <i class="bi bi-calendar-event me-1"></i>
            <span class="subscription-expires-inline__value">
                {{ ($subscription['expires_at_input'] ?? '') !== '' ? $subscription['expires_at_input'] : 'تعيين التاريخ' }}
            </span>
            @if ($subscription['multiple'] ?? false)
                <span class="subscription-expires-inline__hint">+{{ ($subscription['multiple_count'] ?? 1) - 1 }}</span>
            @endif
        </button>
    @elseif ($subscription['expires_at'])
        <span class="subscription-expires-readonly {{ $isExpired ? 'text-danger' : 'text-muted' }}"
              title="{{ ($subscription['source'] ?? '') === 'class' ? 'من نهاية اشتراك الصف: '.($subscription['class_name'] ?? '') : '' }}">
            <i class="bi bi-calendar-x me-1"></i>
            {{ $subscription['expires_at']->format('Y-m-d') }}
            @if (($subscription['source'] ?? '') === 'class')
                <span class="badge bg-light text-muted border ms-1">صف</span>
            @endif
        </span>
    @else
        <span class="text-muted small" title="{{ $selectedClassId ? 'اختر صفاً من الفلتر لتعيين تاريخ فردي' : 'فلتر حسب الصف لتعيين التاريخ' }}">غير محدد</span>
    @endif
</td>
