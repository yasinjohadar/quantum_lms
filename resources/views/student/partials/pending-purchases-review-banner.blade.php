@once('student-pending-purchases-banner-layout-fix')
    {{-- الشريط ليس «عمود المحتوى الرئيسي»: تجنّب تطبيق .app-content الذي يفرض min-height ≈ viewport --}}
    <style>
        .student-pending-purchases-banner .student-pending-review-panel {
            border: 1px solid var(--default-border);
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.04) 0%, var(--custom-white) 45%, rgba(239, 68, 68, 0.04) 100%);
            box-shadow: 0 0.35rem 1.25rem rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .student-pending-purchases-banner .student-pending-review-panel__header {
            padding: 1rem 1.15rem;
            border-bottom: 1px solid var(--default-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .student-pending-purchases-banner .student-pending-review-panel__title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 700;
            color: var(--default-text-color);
        }

        .student-pending-purchases-banner .student-pending-review-panel__icon {
            width: 3rem;
            height: 3rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(245, 158, 11, 0.14);
            color: #f59e0b;
            font-size: 1.35rem;
            flex-shrink: 0;
        }

        .student-pending-purchases-banner .student-pending-review-panel__body {
            padding: 1rem 1.15rem 1.15rem;
        }

        .student-pending-purchases-banner .student-pending-review-alert {
            border: 1px solid rgba(239, 68, 68, 0.18);
            border-radius: 0.9rem;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.08) 0%, rgba(239, 68, 68, 0.03) 100%);
            padding: 0.95rem 1rem;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.9rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .student-pending-purchases-banner .student-pending-review-alert__icon {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(239, 68, 68, 0.14);
            color: #ef4444;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .student-pending-purchases-banner .student-pending-review-grid {
            row-gap: 1rem;
        }

        .student-pending-purchases-banner .student-pending-review-card {
            height: 100%;
            border: 1px solid var(--default-border);
            border-radius: 1rem;
            background: var(--custom-white);
            box-shadow: 0 0.2rem 0.9rem rgba(15, 23, 42, 0.06);
            padding: 1rem;
        }

        .student-pending-purchases-banner .student-pending-review-card__top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.9rem;
        }

        .student-pending-purchases-banner .student-pending-review-card__item {
            display: flex;
            align-items: flex-start;
            gap: 0.8rem;
            min-width: 0;
        }

        .student-pending-purchases-banner .student-pending-review-card__item-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.2rem;
            flex-shrink: 0;
            background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.95) 0%, rgba(var(--primary-rgb), 0.7) 100%);
        }

        .student-pending-purchases-banner .student-pending-review-card__meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
            margin-top: 0.95rem;
            padding-top: 0.95rem;
            border-top: 1px solid var(--default-border);
        }

        .student-pending-purchases-banner .student-pending-review-card__meta-label {
            display: block;
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 0.2rem;
        }

        .student-pending-purchases-banner .student-pending-review-card__meta-value {
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--default-text-color);
        }

        .student-pending-purchases-banner .student-pending-review-card__actions {
            display: flex;
            gap: 0.55rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .student-pending-purchases-banner .student-pending-review-card__actions .btn {
            border-radius: 999px;
        }

        [data-theme-mode="dark"] .student-pending-purchases-banner .student-pending-review-panel,
        [data-bs-theme="dark"] .student-pending-purchases-banner .student-pending-review-panel,
        [data-theme-mode="dark"] .student-pending-purchases-banner .student-pending-review-card,
        [data-bs-theme="dark"] .student-pending-purchases-banner .student-pending-review-card {
            background: rgba(17, 24, 39, 0.92);
            box-shadow: 0 0.45rem 1.5rem rgba(2, 6, 23, 0.35);
        }

        [data-theme-mode="dark"] .student-pending-purchases-banner .student-pending-review-alert,
        [data-bs-theme="dark"] .student-pending-purchases-banner .student-pending-review-alert {
            background: linear-gradient(135deg, rgba(127, 29, 29, 0.35) 0%, rgba(69, 10, 10, 0.18) 100%);
            border-color: rgba(248, 113, 113, 0.2);
        }

        .student-pending-purchases-banner {
            width: 100%;
        }

        .student-pending-purchases-banner .container-fluid {
            padding-inline: 0;
        }

        .student-pending-purchases-banner .student-pending-review-grid {
            display: flex;
            flex-wrap: wrap;
        }

        .student-pending-purchases-banner .student-pending-review-grid > [class*="col-"] {
            display: flex;
        }

        .student-pending-purchases-banner .student-pending-review-grid > [class*="col-"] .student-pending-review-card {
            width: 100%;
        }

        @media (max-width: 767.98px) {
            .student-pending-purchases-banner .student-pending-review-card__meta {
                grid-template-columns: 1fr;
            }

            .student-pending-purchases-banner .student-pending-review-card__actions {
                flex-direction: column;
                align-items: stretch;
            }

            .student-pending-purchases-banner .student-pending-review-card__actions .btn,
            .student-pending-purchases-banner .student-pending-review-card__actions .d-flex {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endonce

{{-- مشتريات قيد المراجعة: يظهر في تخطيط الطالب على كل الصفحات عند وجود طلبات --}}@if(isset($pendingPurchases) && $pendingPurchases->isNotEmpty())
    @php
        $supervisorWhatsappDigits = $supervisorWhatsappDigits ?? \App\Models\SystemSetting::supervisorWhatsappDigits();
        $hasSupervisorWa = $supervisorWhatsappDigits !== '';
    @endphp
    <div class="student-pending-purchases-banner pt-2 pb-0">
        <div class="container-fluid">
            <div class="student-pending-review-panel mb-3 mb-md-4">
                <div class="student-pending-review-panel__header">
                    <div class="student-pending-review-panel__title">
                        <span class="student-pending-review-panel__icon">
                            <i class="bi bi-hourglass-split"></i>
                        </span>
                        <div>
                            <div>طلباتك قيد المراجعة</div>
                            <div class="small text-muted fw-normal">تم إرسال طلب الانضمام إلى الإدارة. راجع الخطوات التالية حتى يكتمل القبول.</div>
                        </div>
                    </div>
                    <span class="badge bg-warning text-dark rounded-pill px-3 py-2">{{ $pendingPurchases->count() }} طلب</span>
                </div>
                <div class="student-pending-review-panel__body">
                    <div class="student-pending-review-alert">
                        <div class="d-flex align-items-start gap-3 flex-grow-1">
                            <span class="student-pending-review-alert__icon">
                                <i class="fab fa-whatsapp"></i>
                            </span>
                            <div class="flex-grow-1">
                                <strong class="d-block mb-1 text-danger">للمتابعة السريعة يرجى التواصل مع المشرفة عبر واتساب</strong>
                                <p class="mb-0 small text-muted">
                                    لن تحتاج إلى رفع إيصال أو إدخال بيانات دفع من هذه الصفحة. بعد إرسال الطلب اتبع الآتي:
                                </p>
                                <ol class="small text-muted mb-0 mt-2 ps-3">
                                    <li>تأكد أن طلبك ظاهر في هذه القائمة.</li>
                                    <li>اضغط على زر <strong>واتساب المشرفة</strong>.</li>
                                    <li>أرسل اسمك والصف أو المادة التي طلبت الانضمام إليها.</li>
                                    <li>انتظر اعتماد الإدارة، ثم ستظهر لك المواد والدروس تلقائيًا.</li>
                                </ol>
                            </div>
                        </div>
                        @if($hasSupervisorWa)
                            @include('student.partials.supervisor-whatsapp-cta', [
                                'supervisorWhatsappDigits' => $supervisorWhatsappDigits,
                                'wrapperClass' => 'align-self-center mb-0',
                                'btnSize' => 'lg',
                            ])
                        @else
                            <span class="small text-muted">سيظهر زر الواتساب هنا بعد ضبط رقم المشرفة.</span>
                        @endif
                    </div>
                    <div class="row student-pending-review-grid" id="pendingPurchasesGrid">
                        @foreach($pendingPurchases as $purchase)
                            <div class="{{ $pendingPurchases->count() === 1 ? 'col-12' : 'col-12 col-md-6 col-xl-4' }} mb-3 pending-purchase-card" data-purchase-id="{{ $purchase->id }}">
                                <article class="student-pending-review-card">
                                    <div class="student-pending-review-card__top">
                                        <div class="student-pending-review-card__item flex-grow-1 min-w-0">
                                            <span class="student-pending-review-card__item-icon">
                                                <i class="bi bi-{{ $purchase->purchase_type === 'class' ? 'building' : 'book' }}"></i>
                                            </span>
                                            <div class="min-w-0">
                                                <div class="fw-bold text-truncate">{{ $purchase->purchasable->name ?? '—' }}</div>
                                                <div class="small text-muted">{{ $purchase->purchase_type === 'class' ? 'صف مدفوع' : 'كورس/مادة مدفوعة' }}</div>
                                            </div>
                                        </div>
                                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2">بانتظار المراجعة</span>
                                    </div>
                                    <div class="student-pending-review-card__meta">
                                        <div>
                                            <span class="student-pending-review-card__meta-label">نوع الطلب</span>
                                            <span class="student-pending-review-card__meta-value">{{ $purchase->purchase_type === 'class' ? 'انضمام صف كامل' : 'انضمام مادة/كورس' }}</span>
                                        </div>
                                        <div>
                                            <span class="student-pending-review-card__meta-label">تاريخ الإرسال</span>
                                            <span class="student-pending-review-card__meta-value">{{ $purchase->created_at->format('Y-m-d') }}</span>
                                        </div>
                                        <div>
                                            <span class="student-pending-review-card__meta-label">الوقت</span>
                                            <span class="student-pending-review-card__meta-value">{{ $purchase->created_at->format('H:i') }}</span>
                                        </div>
                                        <div>
                                            <span class="student-pending-review-card__meta-label">القيمة</span>
                                            <span class="student-pending-review-card__meta-value">{{ number_format((float) $purchase->price, 2) }} ر.س</span>
                                        </div>
                                    </div>
                                    <div class="student-pending-review-card__actions">
                                        @if($hasSupervisorWa)
                                            @include('student.partials.supervisor-whatsapp-cta', [
                                                'supervisorWhatsappDigits' => $supervisorWhatsappDigits,
                                                'wrapperClass' => 'mb-0',
                                            ])
                                        @endif
                                        <button type="button" class="btn btn-outline-danger btn-sm pending-purchase-cancel"
                                                data-purchase-id="{{ $purchase->id }}"
                                                title="إلغاء الطلب">
                                            <i class="bi bi-x-circle me-1"></i> إلغاء الطلب
                                        </button>
                                    </div>
                                </article>
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
