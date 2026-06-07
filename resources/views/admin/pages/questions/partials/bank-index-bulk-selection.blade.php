<script>
(function () {
    if (!window.questionBulkSelectedIds) {
        window.questionBulkSelectedIds = new Set();
    }

    function getResultsRoot() {
        return document.getElementById('questionBankResults') || document;
    }

    function getCheckboxes(root) {
        var scope = root || getResultsRoot();
        return Array.from(scope.querySelectorAll('.question-bulk-checkbox:not(:disabled)'));
    }

    function getToolbar(root) {
        var scope = root || getResultsRoot();
        return scope.querySelector('#questionBulkToolbar') || document.getElementById('questionBulkToolbar');
    }

    function getSelectedCount() {
        return window.questionBulkSelectedIds.size;
    }

    window.getQuestionBulkSelectedIds = function () {
        return Array.from(window.questionBulkSelectedIds);
    };

    window.clearQuestionBulkSelection = function () {
        window.questionBulkSelectedIds.clear();
        getCheckboxes().forEach(function (cb) {
            cb.checked = false;
        });
        if (typeof window.refreshExportWordSelectedCount === 'function') {
            window.refreshExportWordSelectedCount();
        }
    };

    function buildFilterQueryString() {
        var filterForm = document.getElementById('questionBankFilters');
        if (!filterForm) {
            return '';
        }
        var params = new URLSearchParams(new FormData(filterForm));
        params.delete('page');
        return params.toString();
    }

    function syncCheckboxesFromSelection(root) {
        getCheckboxes(root).forEach(function (cb) {
            cb.checked = window.questionBulkSelectedIds.has(cb.value);
            var card = cb.closest('.question-card');
            if (card) {
                card.classList.toggle('question-card-selected', cb.checked);
            }
        });
    }

    window.initQuestionBulkDelete = function (root) {
        var scope = root || getResultsRoot();
        var toolbar = getToolbar(scope);
        var bulkBar = scope.querySelector('#questionBulkActionsBar')
            || document.getElementById('questionBulkActionsBar');
        var selectedCountEl = scope.querySelector('#questionSelectedCount')
            || document.getElementById('questionSelectedCount');
        var clearBtn = scope.querySelector('#clearQuestionSelectionBtn')
            || document.getElementById('clearQuestionSelectionBtn');
        var selectPageBtn = scope.querySelector('#selectAllQuestionsOnPageBtn')
            || document.getElementById('selectAllQuestionsOnPageBtn');
        var selectFilteredBtn = scope.querySelector('#selectAllQuestionsFilteredBtn')
            || document.getElementById('selectAllQuestionsFilteredBtn');
        var bulkDeleteCountEl = document.getElementById('bulkDeleteQuestionsCount');
        var confirmBtn = document.getElementById('confirmBulkDeleteQuestionsBtn');
        var bulkForm = document.getElementById('bulkDeleteQuestionsForm');

        function updateBulkBar() {
            var count = getSelectedCount();

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

            if (typeof window.refreshExportWordSelectedCount === 'function') {
                window.refreshExportWordSelectedCount();
            }
        }

        function attachCheckboxListeners() {
            getCheckboxes(scope).forEach(function (cb) {
                if (cb.dataset.bulkBound === '1') {
                    return;
                }
                cb.dataset.bulkBound = '1';
                cb.addEventListener('change', function () {
                    if (cb.checked) {
                        window.questionBulkSelectedIds.add(cb.value);
                    } else {
                        window.questionBulkSelectedIds.delete(cb.value);
                    }
                    var card = cb.closest('.question-card');
                    if (card) {
                        card.classList.toggle('question-card-selected', cb.checked);
                    }
                    updateBulkBar();
                });
            });
        }

        if (selectPageBtn && selectPageBtn.dataset.bulkBound !== '1') {
            selectPageBtn.dataset.bulkBound = '1';
            selectPageBtn.addEventListener('click', function () {
                getCheckboxes(scope).forEach(function (cb) {
                    window.questionBulkSelectedIds.add(cb.value);
                    cb.checked = true;
                    var card = cb.closest('.question-card');
                    if (card) {
                        card.classList.add('question-card-selected');
                    }
                });
                updateBulkBar();
            });
        }

        if (selectFilteredBtn && selectFilteredBtn.dataset.bulkBound !== '1') {
            selectFilteredBtn.dataset.bulkBound = '1';
            selectFilteredBtn.addEventListener('click', function () {
                var bulkIdsUrl = toolbar ? toolbar.getAttribute('data-bulk-ids-url') : null;
                if (!bulkIdsUrl) {
                    return;
                }

                selectFilteredBtn.disabled = true;
                var originalHtml = selectFilteredBtn.innerHTML;
                selectFilteredBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> جاري التحديد...';

                var qs = buildFilterQueryString();
                var url = bulkIdsUrl + (qs ? '?' + qs : '');

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    credentials: 'same-origin',
                })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (!data || !Array.isArray(data.ids)) {
                            return;
                        }
                        data.ids.forEach(function (id) {
                            window.questionBulkSelectedIds.add(String(id));
                        });
                        syncCheckboxesFromSelection(scope);
                        updateBulkBar();
                        if (data.capped) {
                            alert('تم تحديد أول ' + data.returned + ' سؤال فقط (الحد الأقصى للتصدير/التحديد الجماعي).');
                        }
                    })
                    .catch(function () {
                        alert('تعذّر تحميل قائمة الأسئلة. حاول مرة أخرى.');
                    })
                    .finally(function () {
                        selectFilteredBtn.disabled = false;
                        selectFilteredBtn.innerHTML = originalHtml;
                    });
            });
        }

        if (clearBtn && clearBtn.dataset.bulkBound !== '1') {
            clearBtn.dataset.bulkBound = '1';
            clearBtn.addEventListener('click', function () {
                window.clearQuestionBulkSelection();
                updateBulkBar();
            });
        }

        if (confirmBtn && confirmBtn.dataset.bulkBound !== '1') {
            confirmBtn.dataset.bulkBound = '1';
            confirmBtn.addEventListener('click', function () {
                if (!bulkForm) {
                    return;
                }
                var ids = window.getQuestionBulkSelectedIds();
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

        syncCheckboxesFromSelection(scope);
        attachCheckboxListeners();
        updateBulkBar();
    };

    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('questionBulkToolbar')) {
            window.initQuestionBulkDelete(getResultsRoot());
        }

        var filterForm = document.getElementById('questionBankFilters');
        if (filterForm) {
            filterForm.addEventListener('submit', function () {
                window.clearQuestionBulkSelection();
            });
        }
    });
})();
</script>
