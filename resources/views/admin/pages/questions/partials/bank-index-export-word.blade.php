@php
    $exportWordUrl = $exportWordUrl ?? (isset($subject)
        ? route('admin.subjects.questions.export-word', $subject)
        : route('admin.questions.export-word'));
    $filteredQuestionCount = $filteredQuestionCount ?? 0;
@endphp

@can('question-export')
<form id="exportQuestionsWordForm" method="POST" action="{{ $exportWordUrl }}" class="d-none">
    @csrf
    <input type="hidden" name="scope" id="exportWordScope" value="filtered">
    <input type="hidden" name="order" id="exportWordOrder" value="list_order">
    <div id="exportWordSelectedIds"></div>
    <div id="exportWordFilterFields"></div>
</form>

<div class="modal fade" id="exportQuestionsWordModal" tabindex="-1" aria-labelledby="exportQuestionsWordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="exportQuestionsWordModalLabel">
                    <i class="bi bi-file-earmark-word text-primary me-2"></i>
                    تصدير Word
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted small mb-3">ملف مفتاح معلم — يتضمن الإجابات الصحيحة والشرح، منسّق حسب نوع كل سؤال.</p>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">نطاق التصدير</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="export_word_scope_choice" id="exportWordScopeFiltered" value="filtered" checked>
                        <label class="form-check-label" for="exportWordScopeFiltered">
                            كل الأسئلة المطابقة للفلاتر الحالية (<span id="exportWordFilteredCountLabel">{{ $filteredQuestionCount }}</span>)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="export_word_scope_choice" id="exportWordScopeSelected" value="selected">
                        <label class="form-check-label" for="exportWordScopeSelected">
                            الأسئلة المحددة فقط (<span id="exportWordSelectedCountLabel">0</span>)
                        </label>
                    </div>
                </div>

                <div class="mb-0">
                    <label class="form-label fw-semibold small">ترتيب الأسئلة في الملف</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="export_word_order_choice" id="exportWordOrderList" value="list_order" checked>
                        <label class="form-check-label" for="exportWordOrderList">بنفس ترتيب القائمة</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="export_word_order_choice" id="exportWordOrderByType" value="by_type">
                        <label class="form-check-label" for="exportWordOrderByType">تجميع حسب نوع السؤال</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" id="confirmExportQuestionsWordBtn">
                    <i class="bi bi-download me-1"></i> تنزيل Word
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var exportWordBound = false;

    function getResultsRoot() {
        return document.getElementById('questionBankResults') || document;
    }

    function getCheckboxes() {
        return Array.prototype.slice.call(getResultsRoot().querySelectorAll('.question-bulk-checkbox:not(:disabled)'));
    }

    function getSelectedIds() {
        if (typeof window.getQuestionBulkSelectedIds === 'function') {
            return window.getQuestionBulkSelectedIds();
        }
        return getCheckboxes().filter(function (cb) { return cb.checked; }).map(function (cb) { return cb.value; });
    }

    function refreshFilteredCount() {
        var badge = document.getElementById('questionBankTotalBadge');
        var label = document.getElementById('exportWordFilteredCountLabel');
        if (!label) {
            return;
        }
        if (!badge) {
            return;
        }
        var text = badge.textContent || '';
        var match = text.match(/(\d+)/);
        if (match) {
            label.textContent = match[1];
        }
    }

    function refreshSelectedCount() {
        var count = getSelectedIds().length;
        var selectedCountLabel = document.getElementById('exportWordSelectedCountLabel');
        if (selectedCountLabel) {
            selectedCountLabel.textContent = String(count);
        }
        var selectedRadio = document.getElementById('exportWordScopeSelected');
        if (selectedRadio) {
            selectedRadio.disabled = count === 0;
        }
    }

    function syncFilterFields() {
        var filterForm = document.getElementById('questionBankFilters');
        var fieldsWrap = document.getElementById('exportWordFilterFields');
        if (!fieldsWrap) {
            return;
        }
        fieldsWrap.innerHTML = '';
        if (!filterForm) {
            return;
        }
        var formData = new FormData(filterForm);
        formData.forEach(function (value, key) {
            if (key === 'page' || value === '') {
                return;
            }
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            fieldsWrap.appendChild(input);
        });
    }

    function bindExportWord() {
        refreshFilteredCount();
        refreshSelectedCount();

        if (exportWordBound) {
            return;
        }

        var form = document.getElementById('exportQuestionsWordForm');
        var modal = document.getElementById('exportQuestionsWordModal');
        if (!form || !modal) {
            return;
        }

        exportWordBound = true;

        var confirmBtn = document.getElementById('confirmExportQuestionsWordBtn');
        var scopeInput = document.getElementById('exportWordScope');
        var orderInput = document.getElementById('exportWordOrder');
        var idsWrap = document.getElementById('exportWordSelectedIds');

        document.addEventListener('change', function (e) {
            if (e.target && e.target.classList && e.target.classList.contains('question-bulk-checkbox')) {
                refreshSelectedCount();
            }
        });

        modal.addEventListener('show.bs.modal', function (event) {
            refreshFilteredCount();
            refreshSelectedCount();
            var trigger = event.relatedTarget;
            if (trigger && trigger.getAttribute('data-export-scope') === 'selected') {
                var selectedRadio = document.getElementById('exportWordScopeSelected');
                if (selectedRadio && !selectedRadio.disabled) {
                    selectedRadio.checked = true;
                }
            }
        });

        if (confirmBtn) {
            confirmBtn.addEventListener('click', function () {
                var scopeChoice = document.querySelector('input[name="export_word_scope_choice"]:checked');
                var orderChoice = document.querySelector('input[name="export_word_order_choice"]:checked');
                if (!scopeChoice || !orderChoice) {
                    return;
                }

                scopeInput.value = scopeChoice.value;
                orderInput.value = orderChoice.value;
                idsWrap.innerHTML = '';
                syncFilterFields();

                if (scopeChoice.value === 'selected') {
                    var ids = getSelectedIds();
                    if (!ids.length) {
                        alert('يرجى تحديد سؤال واحد على الأقل.');
                        return;
                    }
                    ids.forEach(function (id) {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = id;
                        idsWrap.appendChild(input);
                    });
                } else {
                    var filteredCount = parseInt(document.getElementById('exportWordFilteredCountLabel')?.textContent || '0', 10);
                    if (!filteredCount) {
                        alert('لا توجد أسئلة مطابقة للفلاتر الحالية.');
                        return;
                    }
                }

                form.submit();
            });
        }
    }

    window.refreshExportWordSelectedCount = refreshSelectedCount;
    window.refreshExportWordFilteredCount = refreshFilteredCount;
    window.bindQuestionBankExportWord = bindExportWord;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindExportWord);
    } else {
        bindExportWord();
    }
})();
</script>
@endcan
