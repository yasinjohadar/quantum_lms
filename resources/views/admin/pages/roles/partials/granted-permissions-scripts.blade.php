<script>
(function () {
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
        document.querySelectorAll('.expand-all-categories').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var pane = btn.closest('.tab-pane');
                if (!pane) {
                    return;
                }
                pane.querySelectorAll('.permission-category-card').forEach(expandCategoryCollapse);
            });
        });

        document.querySelectorAll('.collapse-all-categories').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var pane = btn.closest('.tab-pane');
                if (!pane) {
                    return;
                }
                pane.querySelectorAll('.permission-category-card').forEach(collapseCategoryCollapse);
            });
        });

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
