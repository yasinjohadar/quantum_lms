@php
    $canEditSubscription = auth()->user()?->can('user-edit');
@endphp

<div class="user-edit-subscriptions">
    <div class="user-edit-subscriptions__head">
        <i class="bi bi-calendar2-check"></i>
        <div>
            <strong>اشتراكات الصفوف</strong>
            <span>تاريخ انتهاء الوصول لكل صف مسجّل فيه الطالب</span>
        </div>
    </div>

    @forelse ($classSubscriptions as $subscription)
        @php
            $isExpired = ($subscription['expires_at'] ?? null) && $subscription['expires_at']->isPast();
            $canEditRow = $canEditSubscription && (($subscription['editable'] ?? false) && (($subscription['purchase_id'] ?? null) || ($subscription['settable'] ?? false)));
            $isClassSource = ($subscription['source'] ?? '') === 'class';
            $hasDate = ($subscription['expires_at_input'] ?? '') !== '';
        @endphp

        <div class="user-edit-sub-row {{ $isExpired ? 'user-edit-sub-row--expired' : '' }}"
             data-class-id="{{ $subscription['class_id'] ?? '' }}"
             data-purchase-id="{{ $subscription['purchase_id'] ?? '' }}"
             data-update-url="{{ route('users.update-subscription-expires', $user) }}">

            <div class="user-edit-sub-row__class">
                <span class="user-edit-sub-row__name">{{ $subscription['class_name'] ?? '—' }}</span>
                @if ($isClassSource)
                    <span class="user-edit-sub-row__badge">من الصف</span>
                @endif
                @if ($isExpired)
                    <span class="user-edit-sub-row__badge user-edit-sub-row__badge--danger">منتهي</span>
                @endif
            </div>

            <div class="user-edit-sub-row__date">
                @if ($canEditRow)
                    <input type="date"
                           class="user-edit-sub-date"
                           value="{{ $subscription['expires_at_input'] ?? '' }}"
                           min="{{ now()->format('Y-m-d') }}"
                           @if (! empty($subscription['max_expires_at'])) max="{{ $subscription['max_expires_at'] }}" @endif
                           placeholder="تعيين التاريخ"
                           title="{{ $hasDate ? 'تعديل تاريخ الانتهاء' : 'تعيين تاريخ الانتهاء' }}">
                    <span class="user-edit-sub-row__status" aria-live="polite"></span>
                @elseif ($subscription['expires_at'] ?? null)
                    <span class="user-edit-sub-row__readonly {{ $isExpired ? 'text-danger' : '' }}">
                        <i class="bi bi-calendar-event me-1"></i>
                        {{ $subscription['expires_at']->format('Y-m-d') }}
                    </span>
                @else
                    <span class="user-edit-sub-row__readonly text-muted">غير محدد</span>
                @endif
            </div>
        </div>
    @empty
        <div class="user-edit-subscriptions__empty">
            <i class="bi bi-inbox"></i>
            <span>لا توجد صفوف معتمدة لهذا الطالب</span>
        </div>
    @endforelse
</div>
