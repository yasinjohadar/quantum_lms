<script>
(function () {
    function syncForm(form) {
        var items = form.querySelectorAll('[data-rq-item]');
        var selected = Array.prototype.filter.call(items, function (cb) { return cb.checked; });
        var countEl = form.querySelector('[data-rq-selected-count]');
        var selectedBtn = form.querySelector('[data-rq-approve-selected]');
        var selectAll = form.querySelector('[data-rq-select-all]');

        if (countEl) {
            countEl.textContent = String(selected.length);
        }
        if (selectedBtn) {
            selectedBtn.disabled = selected.length === 0;
        }
        if (selectAll && items.length) {
            selectAll.checked = selected.length === items.length;
            selectAll.indeterminate = selected.length > 0 && selected.length < items.length;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.rq-bulk-form').forEach(function (form) {
            syncForm(form);

            form.addEventListener('change', function (e) {
                var target = e.target;
                if (!target) {
                    return;
                }

                if (target.matches('[data-rq-select-all]')) {
                    form.querySelectorAll('[data-rq-item]').forEach(function (cb) {
                        cb.checked = target.checked;
                    });
                }

                if (target.matches('[data-rq-select-all], [data-rq-item]')) {
                    syncForm(form);
                }
            });

            form.addEventListener('submit', function (e) {
                var submitter = e.submitter;
                var approveAllInput = form.querySelector('[data-rq-approve-all]');
                if (!approveAllInput) {
                    return;
                }

                if (submitter && submitter.hasAttribute('data-rq-approve-all-btn')) {
                    var confirmMsg = submitter.getAttribute('data-confirm') || 'هل تريد قبول الكل؟';
                    if (!window.confirm(confirmMsg)) {
                        e.preventDefault();
                        return;
                    }
                    approveAllInput.value = '1';
                    form.querySelectorAll('[data-rq-item]').forEach(function (cb) {
                        cb.disabled = true;
                    });
                    return;
                }

                approveAllInput.value = '0';
                var selected = form.querySelectorAll('[data-rq-item]:checked');
                if (!selected.length) {
                    e.preventDefault();
                    alert('يرجى تحديد عنصر واحد على الأقل.');
                    return;
                }
                if (!window.confirm('هل تريد قبول العناصر المحددة؟')) {
                    e.preventDefault();
                }
            });
        });
    });
})();
</script>
