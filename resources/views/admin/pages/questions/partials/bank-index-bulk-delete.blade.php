<form id="bulkDeleteQuestionsForm" method="POST" action="{{ $bulkDeleteUrl }}">
    @csrf
    @method('DELETE')
</form>

<div class="modal fade" id="confirmBulkDeleteQuestionsModal" tabindex="-1" aria-labelledby="confirmBulkDeleteQuestionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="border-0 text-center pt-4 px-4">
                <div class="d-inline-flex align-items-center justify-content-center mb-3">
                    <span class="me-2 fs-4 text-warning"><i class="bi bi-exclamation-triangle-fill"></i></span>
                    <h5 class="modal-title mb-0 fw-bold" id="confirmBulkDeleteQuestionsModalLabel">حذف الأسئلة المحددة</h5>
                </div>
                <button type="button" class="btn-close position-absolute top-0 start-0 m-3" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body text-center pt-0 pb-3 px-4">
                <p class="mb-1 text-muted">هل أنت متأكد من حذف</p>
                <p class="fw-bold mb-1"><span id="bulkDeleteQuestionsCount">0</span> سؤال؟</p>
                <p class="text-muted small mb-0">لا يمكن التراجع عن هذا الإجراء. الأسئلة المستخدمة في اختبارات لن تُحذف.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-outline-secondary px-4 me-2" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-danger px-4" id="confirmBulkDeleteQuestionsBtn">
                    <i class="bi bi-trash me-1"></i> حذف
                </button>
            </div>
        </div>
    </div>
</div>
