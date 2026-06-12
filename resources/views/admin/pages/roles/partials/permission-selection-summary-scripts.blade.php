<script>
(function () {
    function refreshCategoryBadges() {
        document.querySelectorAll('.permission-category-card').forEach(function (card) {
            var badge = card.querySelector('.permission-category-badge');
            if (!badge) {
                return;
            }
            var total = parseInt(badge.getAttribute('data-total') || '0', 10);
            var boxes = card.querySelectorAll('input[type="checkbox"][name^="permissions"]');
            var selected = Array.prototype.filter.call(boxes, function (cb) { return cb.checked; }).length;
            badge.textContent = selected + ' / ' + total;
        });
    }

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

        var heroCountEl = document.getElementById('role-form-selected-count');
        if (heroCountEl) {
            heroCountEl.textContent = String(items.length);
        }

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

        refreshCategoryBadges();
    }

    function getCategoryCard(el) {
        return el ? el.closest('.permission-category-card') : null;
    }

    function expandCategoryCollapse(card) {
        if (!card || typeof bootstrap === 'undefined') {
            return;
        }
        var collapseEl = card.querySelector('.accordion-collapse');
        if (!collapseEl) {
            return;
        }
        var instance = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
        instance.show();
    }

    function collapseCategoryCollapse(card) {
        if (!card || typeof bootstrap === 'undefined') {
            return;
        }
        var collapseEl = card.querySelector('.accordion-collapse');
        if (!collapseEl) {
            return;
        }
        var instance = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
        instance.hide();
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
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var categoryCard = getCategoryCard(this);
                if (!categoryCard) {
                    return;
                }
                categoryCard.querySelectorAll('input[type="checkbox"][name^="permissions"]').forEach(function (checkbox) {
                    checkbox.checked = true;
                });
                refreshRolePermissionSummary();
            });
        });

        document.querySelectorAll('.deselect-all-category').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var categoryCard = getCategoryCard(this);
                if (!categoryCard) {
                    return;
                }
                categoryCard.querySelectorAll('input[type="checkbox"][name^="permissions"]').forEach(function (checkbox) {
                    checkbox.checked = false;
                });
                refreshRolePermissionSummary();
            });
        });

        document.querySelectorAll('.expand-all-categories').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var tabPane = this.closest('.tab-pane');
                if (!tabPane) {
                    return;
                }
                tabPane.querySelectorAll('.permission-category-card').forEach(function (card) {
                    expandCategoryCollapse(card);
                });
            });
        });

        document.querySelectorAll('.collapse-all-categories').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var tabPane = this.closest('.tab-pane');
                if (!tabPane) {
                    return;
                }
                tabPane.querySelectorAll('.permission-category-card').forEach(function (card) {
                    collapseCategoryCollapse(card);
                });
            });
        });

        var deselectAllBtn = document.getElementById('role-permissions-deselect-all');
        if (deselectAllBtn) {
            deselectAllBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
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
                    var permissionItems = card.querySelectorAll('.permission-item');
                    var visibleCount = 0;

                    permissionItems.forEach(function (item) {
                        if (searchTerm === '') {
                            item.style.display = '';
                            visibleCount++;
                            return;
                        }
                        var itemText = item.textContent.toLowerCase();
                        var matches = itemText.indexOf(searchTerm) !== -1;
                        item.style.display = matches ? '' : 'none';
                        if (matches) {
                            visibleCount++;
                        }
                    });

                    if (searchTerm === '') {
                        card.style.display = '';
                    } else if (visibleCount > 0) {
                        card.style.display = '';
                        expandCategoryCollapse(card);
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }
    });
})();
</script>
