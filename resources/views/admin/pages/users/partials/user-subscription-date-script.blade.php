<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.user-edit-sub-date').forEach(function(input) {
            const row = input.closest('[data-update-url]');
            if (!row) return;

            const statusEl = row.querySelector('.user-edit-sub-row__status');
            const updateUrl = row.dataset.updateUrl;
            const classId = row.dataset.classId;
            let purchaseId = row.dataset.purchaseId || '';
            let saving = false;
            let lastSaved = input.value;

            const setStatus = function(html, className) {
                if (!statusEl) return;
                statusEl.className = 'user-sub-status user-edit-sub-row__status' + (className ? ' ' + className : '');
                statusEl.innerHTML = html;
            };

            const save = function() {
                const newValue = input.value;
                if (!newValue || newValue === lastSaved || saving) return;

                saving = true;
                input.disabled = true;
                setStatus('<span class="spinner-border spinner-border-sm"></span>', 'user-sub-status--loading');

                const payload = { expires_at: newValue };
                if (purchaseId) {
                    payload.purchase_id = Number(purchaseId);
                } else if (classId) {
                    payload.class_id = Number(classId);
                }

                fetch(updateUrl, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                })
                    .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, json: j }; }); })
                    .then(function(result) {
                        if (result.ok && result.json.success) {
                            lastSaved = result.json.expires_at;
                            input.value = lastSaved;
                            if (result.json.purchase_id) {
                                purchaseId = String(result.json.purchase_id);
                                row.dataset.purchaseId = purchaseId;
                            }
                            row.classList.toggle('user-edit-sub-row--expired', !!result.json.is_expired);
                            setStatus('<i class="bi bi-check2"></i>', 'user-sub-status--ok');
                            setTimeout(function() { setStatus(''); }, 2000);
                        } else {
                            input.value = lastSaved;
                            setStatus('<i class="bi bi-x-lg"></i>', 'user-sub-status--err');
                            alert((result.json && result.json.message) ? result.json.message : 'تعذر حفظ التاريخ');
                        }
                    })
                    .catch(function() {
                        input.value = lastSaved;
                        setStatus('<i class="bi bi-x-lg"></i>', 'user-sub-status--err');
                        alert('حدث خطأ في الاتصال');
                    })
                    .finally(function() {
                        saving = false;
                        input.disabled = false;
                    });
            };

            input.addEventListener('change', save);
        });
    });
</script>
