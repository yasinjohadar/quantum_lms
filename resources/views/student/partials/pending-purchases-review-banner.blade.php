@once('student-pending-purchases-banner-layout-fix')
    {{-- الشريط ليس «عمود المحتوى الرئيسي»: تجنّب تطبيق .app-content الذي يفرض min-height ≈ viewport --}}
    <style>
        @media (min-width: 992px) {
            .student-pending-purchases-banner {
                margin-inline-start: 15rem;
                margin-block-start: 3.85rem;
            }

            /* لا نكرّر هوامش رأس المحتوى بين الشريط ومحتوى الصفحة التالي */
            .student-pending-purchases-banner + .main-content.app-content {
                margin-block-start: 0 !important;
            }
        }
    </style>
@endonce

{{-- مشتريات قيد المراجعة: يظهر في تخطيط الطالب على كل الصفحات عند وجود طلبات --}}@if(isset($pendingPurchases) && $pendingPurchases->isNotEmpty())
    @php
        $hasSupervisorWa = isset($supervisorWhatsappDigits) && $supervisorWhatsappDigits !== '';
    @endphp
    <div class="student-pending-purchases-banner main-content pt-2 pb-0">        <div class="container-fluid">
            <div class="card custom-card mb-3 mb-md-4 border-warning">
                <div class="card-header bg-warning-transparent d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-hourglass-split fs-5 me-2 text-warning"></i>
                        <h5 class="mb-0 text-warning">مشتريات قيد المراجعة</h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border-0 d-flex align-items-start gap-3 flex-wrap mb-3">
                        <i class="fab fa-whatsapp fs-4 text-success flex-shrink-0 mt-1"></i>
                        <div class="flex-grow-1">
                            <strong class="d-block mb-1">متابعة التفعيل</strong>
                            <p class="mb-0 small">
                                لمتابعة تفعيل اشتراكك، يرجى التواصل مع المشرفة عبر واتساب.
                                @unless($hasSupervisorWa)
                                    <span class="text-muted d-block mt-1">سيتم تفعيل رابط التواصل عند ضبط رقم المشرفة من إعدادات النظام.</span>
                                @endunless
                            </p>
                        </div>
                        @if($hasSupervisorWa)
                            <a href="https://wa.me/{{ $supervisorWhatsappDigits }}" target="_blank" rel="noopener noreferrer"
                               class="btn btn-success btn-sm align-self-center d-inline-flex align-items-center gap-1">
                                <i class="fab fa-whatsapp"></i>
                                واتساب المشرفة
                            </a>
                        @endif
                    </div>
                    <div class="row" id="pendingPurchasesGrid">
                        @foreach($pendingPurchases as $purchase)
                            <div class="col-md-6 col-lg-4 mb-3 pending-purchase-card" data-purchase-id="{{ $purchase->id }}">
                                <div class="d-flex align-items-center gap-2 p-3 rounded bg-light">
                                    <i class="bi bi-{{ $purchase->purchase_type === 'class' ? 'building' : 'book' }} fs-4 text-warning flex-shrink-0"></i>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-semibold">{{ $purchase->purchasable->name ?? '—' }}</div>
                                        <small class="text-muted">{{ $purchase->purchase_type === 'class' ? 'صف كامل' : 'مادة' }}</small>
                                    </div>
                                    <div class="d-flex flex-column align-items-end gap-2 flex-shrink-0">
                                        <span class="badge bg-warning">قيد المراجعة</span>
                                        <button type="button" class="btn btn-outline-danger btn-sm pending-purchase-cancel"
                                                data-purchase-id="{{ $purchase->id }}"
                                                title="إلغاء الطلب">
                                            <i class="bi bi-x-circle me-1"></i> إلغاء
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmPendingPurchaseCancelModal" tabindex="-1"
         aria-labelledby="confirmPendingPurchaseCancelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <div class="avatar avatar-xl bg-danger-transparent rounded-circle mx-auto d-flex align-items-center justify-content-center">
                            <i class="bi bi-x-octagon fs-1 text-danger"></i>
                        </div>
                    </div>
                    <h5 class="modal-title mb-3" id="confirmPendingPurchaseCancelModalLabel">تأكيد إلغاء الطلب</h5>
                    <p class="text-muted mb-4">سيتم إلغاء طلب الشراء نهائياً ولن يظهر للمراجعة. هل أنت متأكد؟</p>
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-arrow-right me-1"></i> تراجع
                        </button>
                        <button type="button" class="btn btn-danger" id="confirmPendingPurchaseCancelBtn">
                            <i class="bi bi-trash me-1"></i> إلغاء الطلب نهائياً
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
            <script>
                (function () {
                    var cancelUrlTpl = @json(route('student.purchases.cancel', ['purchase' => '__pid__']));
                    var pendingPurchaseIdToCancel = null;

                    function getCsrfToken() {
                        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    }

                    function showPendingCancelModal(modalEl) {
                        if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            var inst = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                            inst.show();
                            return;
                        }
                        if (confirm('سيتم إلغاء طلب الشراء نهائياً. هل أنت متأكد؟')) {
                            executePendingPurchaseCancel();
                        }
                    }

                    function hidePendingCancelModal(modalEl) {
                        if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            var m = bootstrap.Modal.getInstance(modalEl);
                            if (m) m.hide();
                        }
                    }

                    function executePendingPurchaseCancel(confirmBtn) {
                        var id = pendingPurchaseIdToCancel;
                        if (!id) return;
                        var btn = confirmBtn || null;
                        if (btn) btn.disabled = true;
                        var url = cancelUrlTpl.replace('__pid__', String(id));
                        fetch(url, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        })
                            .then(function (r) {
                                return r.json().catch(function () { return {}; }).then(function (j) { return { ok: r.ok, json: j }; });
                            })
                            .then(function (res) {
                                if (res.ok && res.json && res.json.success) {
                                    hidePendingCancelModal(document.getElementById('confirmPendingPurchaseCancelModal'));
                                    var card = document.querySelector('.pending-purchase-card[data-purchase-id="' + String(id) + '"]');
                                    if (card) card.remove();
                                    var grid = document.getElementById('pendingPurchasesGrid');
                                    if (grid && !grid.querySelector('.pending-purchase-card')) {
                                        window.location.reload();
                                    }
                                    pendingPurchaseIdToCancel = null;
                                } else if (res.json && res.json.message) {
                                    alert(res.json.message);
                                } else {
                                    alert('تعذر إلغاء الطلب');
                                }
                            })
                            .catch(function () {
                                alert('حدث خطأ في الاتصال');
                            })
                            .finally(function () {
                                if (btn) btn.disabled = false;
                            });
                    }

                    document.addEventListener('click', function (e) {
                        var trigger = e.target.closest('.pending-purchase-cancel');
                        if (!trigger) return;
                        e.preventDefault();
                        e.stopPropagation();
                        pendingPurchaseIdToCancel = trigger.getAttribute('data-purchase-id');
                        showPendingCancelModal(document.getElementById('confirmPendingPurchaseCancelModal'));
                    });

                    document.getElementById('confirmPendingPurchaseCancelBtn')?.addEventListener('click', function () {
                        executePendingPurchaseCancel(this);
                    });
                })();
            </script>
    @endpush
@endif
