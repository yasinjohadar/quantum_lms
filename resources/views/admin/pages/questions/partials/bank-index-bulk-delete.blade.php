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

<script>
(function () {
    function getCheckboxes(root) {
        var scope = root || document;
        return Array.from(scope.querySelectorAll('.question-bulk-checkbox:not(:disabled)'));
    }

    function getSelectAll(root) {
        var scope = root || document;
        return scope.querySelector('#selectAllQuestionsOnPage');
    }

    window.initQuestionBulkDelete = function (root) {
        var scope = root || document;
        var selectAll = getSelectAll(scope);
        var bulkBar = scope.querySelector('#questionBulkActionsBar')
            || document.getElementById('questionBulkActionsBar');
        var selectedCountEl = scope.querySelector('#questionSelectedCount')
            || document.getElementById('questionSelectedCount');
        var clearBtn = scope.querySelector('#clearQuestionSelectionBtn')
            || document.getElementById('clearQuestionSelectionBtn');
        var bulkDeleteCountEl = document.getElementById('bulkDeleteQuestionsCount');
        var confirmBtn = document.getElementById('confirmBulkDeleteQuestionsBtn');
        var bulkForm = document.getElementById('bulkDeleteQuestionsForm');

        function updateBulkBar() {
            var boxes = getCheckboxes(scope);
            var checked = boxes.filter(function (cb) { return cb.checked; });
            var count = checked.length;

            if (selectedCountEl) {
                selectedCountEl.textContent = String(count);
            }
            if (bulkDeleteCountEl) {
                bulkDeleteCountEl.textContent = String(count);
            }
            if (bulkBar) {
                bulkBar.classList.toggle('d-none', count === 0);
                bulkBar.classList.toggle('d-flex', count > 0);
            }
            if (selectAll) {
                selectAll.indeterminate = count > 0 && count < boxes.length;
                selectAll.checked = boxes.length > 0 && count === boxes.length;
            }

            boxes.forEach(function (cb) {
                var card = cb.closest('.question-card');
                if (card) {
                    card.classList.toggle('question-card-selected', cb.checked);
                }
            });
        }

        function attachCheckboxListeners() {
            getCheckboxes(scope).forEach(function (cb) {
                if (cb.dataset.bulkBound === '1') {
                    return;
                }
                cb.dataset.bulkBound = '1';
                cb.addEventListener('change', updateBulkBar);
            });
        }

        if (selectAll && selectAll.dataset.bulkBound !== '1') {
            selectAll.dataset.bulkBound = '1';
            selectAll.addEventListener('change', function () {
                var checked = selectAll.checked;
                getCheckboxes(scope).forEach(function (cb) {
                    cb.checked = checked;
                });
                updateBulkBar();
            });
        }

        if (clearBtn && clearBtn.dataset.bulkBound !== '1') {
            clearBtn.dataset.bulkBound = '1';
            clearBtn.addEventListener('click', function () {
                if (selectAll) {
                    selectAll.checked = false;
                    selectAll.indeterminate = false;
                }
                getCheckboxes(scope).forEach(function (cb) {
                    cb.checked = false;
                });
                updateBulkBar();
            });
        }

        if (confirmBtn && confirmBtn.dataset.bulkBound !== '1') {
            confirmBtn.dataset.bulkBound = '1';
            confirmBtn.addEventListener('click', function () {
                if (!bulkForm) {
                    return;
                }
                var ids = getCheckboxes(scope).filter(function (cb) { return cb.checked; }).map(function (cb) { return cb.value; });
                if (ids.length === 0) {
                    return;
                }
                bulkForm.querySelectorAll('input[name="question_ids[]"]').forEach(function (el) { el.remove(); });
                ids.forEach(function (id) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'question_ids[]';
                    input.value = id;
                    bulkForm.appendChild(input);
                });
                bulkForm.submit();
            });
        }

        attachCheckboxListeners();
        updateBulkBar();
    };

    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('bulkDeleteQuestionsForm')) {
            window.initQuestionBulkDelete(document.getElementById('questionBankResults') || document);
        }
    });
})();
</script>
