<div class="modal fade" id="approvePurchaseModal" tabindex="-1" aria-labelledby="approvePurchaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approvePurchaseModalLabel">
                    <i class="bi bi-check-circle-fill me-2"></i> قبول طلب الانضمام المدفوع
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form id="approvePurchaseForm" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-calendar-check text-success" style="font-size: 3rem;"></i>
                    </div>
                    <p class="text-center mb-1">
                        الطالب: <strong id="approvePurchaseStudentName">—</strong>
                    </p>
                    <p class="text-center text-muted mb-4">
                        <span id="approvePurchaseTypeLabel">—</span>:
                        <strong id="approvePurchaseItemName">—</strong>
                    </p>

                    <div class="alert alert-info small mb-3" id="approvePurchaseInfoAlert">
                        <i class="bi bi-info-circle me-1"></i>
                        حدّد تاريخ انتهاء الاشتراك. عند وصول هذا التاريخ سيتم إلغاء انضمام الطالب تلقائياً من الصف أو المادة.
                    </div>

                    <div class="alert alert-warning small mb-3 d-none" id="approvePurchaseClassCapAlert">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        نهاية اشتراك الصف
                        <strong id="approvePurchaseClassName">—</strong>
                        محددة في
                        <strong id="approvePurchaseClassEndsAt">—</strong>.
                        سيُستخدم تلقائياً <strong>الأقرب</strong> بين التاريخ الذي تختاره وتاريخ نهاية الصف.
                    </div>

                    <div class="mb-3">
                        <label for="approvePurchaseExpiresAt" class="form-label fw-semibold">
                            تاريخ انتهاء الاشتراك <span class="text-danger">*</span>
                        </label>
                        <input type="date"
                               name="expires_at"
                               id="approvePurchaseExpiresAt"
                               class="form-control"
                               required
                               min="{{ now()->format('Y-m-d') }}">
                        <div class="form-text" id="approvePurchaseExpiresHint">سيبقى الطالب مسجلاً حتى نهاية اليوم المحدد.</div>
                    </div>

                    <div class="mb-0">
                        <label for="approvePurchaseNotes" class="form-label">ملاحظات (اختياري)</label>
                        <textarea name="notes"
                                  id="approvePurchaseNotes"
                                  class="form-control"
                                  rows="2"
                                  maxlength="1000"
                                  placeholder="ملاحظات إضافية للإدارة..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> إلغاء
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i> تأكيد القبول
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('approvePurchaseModal');
    if (!modalEl) {
        return;
    }

    const form = document.getElementById('approvePurchaseForm');
    const studentNameEl = document.getElementById('approvePurchaseStudentName');
    const itemNameEl = document.getElementById('approvePurchaseItemName');
    const typeLabelEl = document.getElementById('approvePurchaseTypeLabel');
    const expiresAtEl = document.getElementById('approvePurchaseExpiresAt');
    const expiresHintEl = document.getElementById('approvePurchaseExpiresHint');
    const classCapAlertEl = document.getElementById('approvePurchaseClassCapAlert');
    const classNameEl = document.getElementById('approvePurchaseClassName');
    const classEndsAtEl = document.getElementById('approvePurchaseClassEndsAt');
    const notesEl = document.getElementById('approvePurchaseNotes');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    const today = @json(now()->format('Y-m-d'));

    document.querySelectorAll('.approve-purchase-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            form.action = this.dataset.action || '#';
            studentNameEl.textContent = this.dataset.student || '—';
            itemNameEl.textContent = this.dataset.item || '—';
            typeLabelEl.textContent = this.dataset.typeLabel || 'العنصر';

            const classSubscriptionEnds = this.dataset.classSubscriptionEnds || '';
            const className = this.dataset.className || '';
            const maxExpires = this.dataset.maxExpires || '';
            const defaultExpires = this.dataset.defaultExpires || '';

            if (expiresAtEl) {
                expiresAtEl.min = today;
                expiresAtEl.max = maxExpires || '';
                expiresAtEl.value = defaultExpires;
            }

            if (classCapAlertEl) {
                if (classSubscriptionEnds) {
                    classCapAlertEl.classList.remove('d-none');
                    if (classNameEl) {
                        classNameEl.textContent = className ? `«${className}»` : 'هذا الصف';
                    }
                    if (classEndsAtEl) {
                        classEndsAtEl.textContent = classSubscriptionEnds;
                    }
                } else {
                    classCapAlertEl.classList.add('d-none');
                }
            }

            if (expiresHintEl) {
                if (classSubscriptionEnds) {
                    expiresHintEl.textContent = 'القيمة الافتراضية هي نهاية اشتراك الصف. لا يمكن تجاوز هذا التاريخ.';
                } else {
                    expiresHintEl.textContent = 'سيبقى الطالب مسجلاً حتى نهاية اليوم المحدد.';
                }
            }

            if (notesEl) {
                notesEl.value = '';
            }

            modal.show();
        });
    });
});
</script>
@endpush
