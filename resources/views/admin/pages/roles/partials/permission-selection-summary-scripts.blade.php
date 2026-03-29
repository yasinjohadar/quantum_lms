<script>
(function () {
    function refreshRolePermissionSummary() {
        var form = document.querySelector('[data-role-permissions-form]');
        var countEl = document.getElementById('role-permissions-summary-count');
        var listEl = document.getElementById('role-permissions-summary-list');
        var emptyEl = document.getElementById('role-permissions-summary-empty');
        if (!form || !countEl || !listEl) {
            return;
        }

        var boxes = form.querySelectorAll('input[type="checkbox"][name^="permissions"]');
        var items = Array.prototype.slice.call(boxes)
            .filter(function (cb) { return cb.checked; })
            .map(function (cb) {
                var desc = cb.getAttribute('data-permission-description');
                return {
                    name: cb.value,
                    description: (desc != null ? String(desc) : '').trim()
                };
            })
            .sort(function (a, b) {
                return a.name.localeCompare(b.name);
            });

        countEl.textContent = String(items.length);

        listEl.innerHTML = '';
        items.forEach(function (item, index) {
            var li = document.createElement('li');

            var idxSpan = document.createElement('span');
            idxSpan.className = 'role-permissions-summary-index';
            idxSpan.textContent = String(index + 1) + '.';
            idxSpan.setAttribute('aria-hidden', 'true');
            li.appendChild(idxSpan);

            var textWrap = document.createElement('div');
            textWrap.className = 'role-permissions-summary-item-text';

            var nameSpan = document.createElement('span');
            nameSpan.className = 'fw-semibold d-block';
            nameSpan.textContent = item.name;
            textWrap.appendChild(nameSpan);

            if (item.description) {
                var descSmall = document.createElement('small');
                descSmall.className = 'text-muted d-block mt-1';
                descSmall.textContent = item.description;
                textWrap.appendChild(descSmall);
            }

            li.appendChild(textWrap);
            listEl.appendChild(li);
        });

        if (emptyEl) {
            emptyEl.style.display = items.length ? 'none' : '';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var form = document.querySelector('[data-role-permissions-form]');
        if (!form) {
            return;
        }

        form.addEventListener('change', function (e) {
            var t = e.target;
            if (t && t.matches && t.matches('input[type="checkbox"][name^="permissions"]')) {
                refreshRolePermissionSummary();
            }
        });

        document.querySelectorAll('.select-all-category').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var categoryCard = this.closest('.card');
                if (!categoryCard) {
                    return;
                }
                categoryCard.querySelectorAll('input[type="checkbox"]').forEach(function (checkbox) {
                    checkbox.checked = true;
                });
                refreshRolePermissionSummary();
            });
        });

        document.querySelectorAll('.deselect-all-category').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var categoryCard = this.closest('.card');
                if (!categoryCard) {
                    return;
                }
                categoryCard.querySelectorAll('input[type="checkbox"]').forEach(function (checkbox) {
                    checkbox.checked = false;
                });
                refreshRolePermissionSummary();
            });
        });

        var deselectAllBtn = document.getElementById('role-permissions-deselect-all');
        if (deselectAllBtn) {
            deselectAllBtn.addEventListener('click', function () {
                form.querySelectorAll('input[type="checkbox"][name^="permissions"]').forEach(function (cb) {
                    cb.checked = false;
                });
                refreshRolePermissionSummary();
            });
        }

        refreshRolePermissionSummary();

        var searchInput = document.getElementById('permissionSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                var searchTerm = this.value.toLowerCase().trim();
                var categoryCards = document.querySelectorAll('.permission-category-card');

                categoryCards.forEach(function (card) {
                    var cardText = card.textContent.toLowerCase();
                    var hasMatch = cardText.indexOf(searchTerm) !== -1;

                    if (searchTerm === '') {
                        card.style.display = '';
                        card.querySelectorAll('.col-md-6.col-lg-4.mb-3').forEach(function (item) {
                            item.style.display = '';
                        });
                    } else if (hasMatch) {
                        card.style.display = '';
                        card.querySelectorAll('.col-md-6.col-lg-4.mb-3').forEach(function (item) {
                            var itemText = item.textContent.toLowerCase();
                            item.style.display = itemText.indexOf(searchTerm) !== -1 ? '' : 'none';
                        });
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }
    });
})();
</script>
