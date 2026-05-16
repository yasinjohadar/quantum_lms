{{-- مودال واضح: الطلب أُرسل للإدارة وبانتظار القبول --}}
<div class="modal fade" id="enrollmentPendingReviewModal" tabindex="-1"
     aria-labelledby="enrollmentPendingReviewModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 640px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body text-center p-5 p-md-6">
                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-4"
                     aria-hidden="true"
                     style="width: clamp(96px, 22vw, 140px); height: clamp(96px, 22vw, 140px);">
                    <i class="bi bi-clock-history text-primary" style="font-size: clamp(3rem, 10vw, 4.25rem);"></i>
                </div>
                <h4 class="fw-bold mb-3" id="enrollmentPendingReviewModalLabel">
                    تم إرسال طلبك للإدارة
                </h4>
                <p class="fs-5 text-dark mb-2 px-md-4" id="enrollmentPendingReviewModalMessage"></p>
                <p class="text-muted mb-4 small px-md-2">
                    طلب الانضمام قيد المراجعة وبانتظار قبول الإدارة. ستُفعِّل المواد تلقائياً بعد الموافقة؛ يمكنك إغلاق هذه النافذة ومتابعة التصفح وسيُحدَّث الوضع عند تحديث الصفحة.
                </p>
                <button type="button" class="btn btn-primary btn-lg px-5 rounded-pill" data-bs-dismiss="modal">
                    <i class="bi bi-check2-circle me-2" aria-hidden="true"></i>
                    حسناً، فهمت
                </button>
            </div>
        </div>
    </div>
</div>
