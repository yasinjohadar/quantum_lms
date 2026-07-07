@php
    $student = $purchase->user;
    $schoolClass = $purchase->purchasable;
    $schoolClass?->loadMissing('stage');
    $initial = $student ? mb_strtoupper(mb_substr(trim($student->name), 0, 1)) : '—';
    $approvalDefaults = \App\Support\PurchaseApprovalExpiryDefaults::resolve($purchase);
    $defaultExpires = $approvalDefaults['default_expires_at']->format('Y-m-d');
    $classEndsAt = $approvalDefaults['class_subscription_ends_at']?->format('Y-m-d');
@endphp

<div class="col-12 col-md-6 col-xl-4">
    <article class="pending-purchase-card">
        <div class="pending-purchase-card__top">
            <div class="pending-purchase-card__student">
                <span class="pending-purchase-card__avatar">{{ $initial }}</span>
                <div class="pending-purchase-card__identity">
                    <h6 class="pending-purchase-card__name">{{ $student->name ?? 'غير محدد' }}</h6>
                    @if ($student?->phone)
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $student->phone) }}"
                           class="pending-purchase-card__phone"
                           target="_blank"
                           rel="noopener"
                           dir="ltr">
                            <i class="fab fa-whatsapp"></i>{{ $student->phone }}
                        </a>
                    @elseif ($student?->email)
                        <span class="pending-purchase-card__meta">{{ $student->email }}</span>
                    @else
                        <span class="pending-purchase-card__meta text-muted">—</span>
                    @endif
                </div>
            </div>
            <div class="pending-purchase-card__price-wrap">
                <div class="pending-purchase-card__price">{{ number_format((float) $purchase->price, 2) }} <small>ر.س</small></div>
                <span class="pending-purchase-card__badge">طلب مدفوع</span>
            </div>
        </div>

        <div class="pending-purchase-card__body">
            <div class="pending-purchase-card__row">
                <i class="bi bi-mortarboard"></i>
                <div>
                    <strong>{{ $schoolClass->name ?? 'صف غير محدد' }}</strong>
                    @if ($schoolClass?->stage)
                        <span class="pending-purchase-card__meta"> — {{ $schoolClass->stage->name }}</span>
                    @endif
                </div>
            </div>
            <div class="pending-purchase-card__row">
                <i class="bi bi-clock"></i>
                <span>تاريخ الطلب: <strong>{{ $purchase->created_at->format('Y-m-d H:i') }}</strong></span>
            </div>
            <div class="pending-purchase-card__row pending-purchase-card__row--expiry">
                <i class="bi bi-calendar2-check"></i>
                <div>
                    <span>انتهاء مقترح عند القبول:</span>
                    <strong dir="ltr">{{ $defaultExpires }}</strong>
                    @if ($classEndsAt)
                        <span class="pending-purchase-card__hint">(حد الصف: {{ $classEndsAt }})</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="pending-purchase-card__actions">
            @include('admin.pages.enrollments.partials.approve-purchase-btn', [
                'purchase' => $purchase,
                'studentName' => $student->name ?? 'غير محدد',
                'itemName' => $schoolClass->name ?? 'صف غير محدد',
                'typeLabel' => 'الصف',
            ])
            <form action="{{ route('admin.payments.pending-purchases.reject', $purchase) }}"
                  method="POST"
                  class="d-inline"
                  onsubmit="return confirm('هل تريد رفض طلب الصف المدفوع هذا؟');">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-x-lg me-1"></i> رفض
                </button>
            </form>
        </div>
    </article>
</div>
