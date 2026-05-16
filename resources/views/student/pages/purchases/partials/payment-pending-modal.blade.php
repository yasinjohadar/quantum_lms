<div class="modal fade" id="paymentPendingReviewModal" tabindex="-1"
     aria-labelledby="paymentPendingReviewModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 640px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body text-center p-5 p-md-6">
                <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-4"
                     aria-hidden="true"
                     style="width: clamp(96px, 22vw, 140px); height: clamp(96px, 22vw, 140px);">
                    <i class="bi bi-hourglass-split text-warning" style="font-size: clamp(3rem, 10vw, 4.25rem);"></i>
                </div>
                <h4 class="fw-bold mb-3" id="paymentPendingReviewModalLabel">
                    تم إرسال طلب الدفع
                </h4>
                <p class="fs-5 text-dark mb-2 px-md-4" id="paymentPendingReviewModalMessage"></p>
                <p class="text-muted mb-4 small px-md-2">
                    سيتم مراجعة طلبك من الإدارة. يمكنك إغلاق هذه النافذة ومتابعة التصفح؛ سيُحدَّث الوضع بعد الموافقة.
                </p>
                <button type="button" class="btn btn-primary btn-lg px-5 rounded-pill" id="paymentPendingReviewModalOk">
                    <i class="bi bi-check2-circle me-2" aria-hidden="true"></i>
                    حسناً، فهمت
                </button>
            </div>
        </div>
    </div>
</div>
