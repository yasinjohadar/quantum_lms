(function () {
    'use strict';

    const STORAGE_KEY = 'teachersAssignmentsVisibleCols_v2';

    const COLUMN_DEFS = [
        { key: 'name', label: 'الاسم', locked: true },
        { key: 'email', label: 'البريد' },
        { key: 'roles', label: 'الأدوار' },
        { key: 'classes', label: 'الصفوف' },
        { key: 'subjects', label: 'المواد' },
        { key: 'status', label: 'الحالة' },
        { key: 'last_login', label: 'آخر دخول' },
        { key: 'online', label: 'الاتصال' },
        { key: 'quizzes', label: 'الاختبارات' },
        { key: 'progress', label: 'التقدم' },
    ];

    const ALL_KEYS = COLUMN_DEFS.map(function (c) { return c.key; });

    const PRESETS = {
        minimal: ['name', 'email', 'classes', 'subjects', 'progress'],
        standard: ['name', 'email', 'classes', 'subjects', 'status', 'quizzes', 'progress'],
        full: ALL_KEYS.slice(),
    };

    let visibleCols = [];

    function loadVisibleCols() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (raw) {
                const parsed = JSON.parse(raw);
                if (Array.isArray(parsed) && parsed.length) {
                    return ALL_KEYS.filter(function (key) {
                        return parsed.includes(key);
                    });
                }
            }
        } catch (e) {}

        return PRESETS.standard.slice();
    }

    function saveVisibleCols(cols) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(cols));
        } catch (e) {}
    }

    function getTable() {
        return document.getElementById('teachersAssignmentsTable');
    }

    function applyColumnVisibility() {
        const table = getTable();
        if (!table) {
            return;
        }

        table.querySelectorAll('[data-tv-col]').forEach(function (cell) {
            const col = cell.getAttribute('data-tv-col');
            cell.classList.toggle('tv-col-hidden', visibleCols.indexOf(col) === -1);
        });
    }

    function updateVisibleCountBadge() {
        const badge = document.getElementById('teachersColumnsVisibleCount');
        if (!badge) {
            return;
        }
        badge.textContent = visibleCols.length + '/' + ALL_KEYS.length;
    }

    function syncPresetButtons() {
        document.querySelectorAll('[data-tv-columns-preset]').forEach(function (btn) {
            const presetKey = btn.getAttribute('data-tv-columns-preset');
            const preset = PRESETS[presetKey] || [];
            const isActive = preset.length === visibleCols.length
                && preset.every(function (key) { return visibleCols.indexOf(key) !== -1; });
            btn.classList.toggle('active', isActive);
        });
    }

    function syncChecklistInputs() {
        document.querySelectorAll('#teachersColumnsChecklist input[data-tv-col-key]').forEach(function (input) {
            const key = input.getAttribute('data-tv-col-key');
            input.checked = visibleCols.indexOf(key) !== -1;
        });
    }

    function setVisibleCols(nextCols, options) {
        options = options || {};
        const locked = COLUMN_DEFS.filter(function (c) { return c.locked; }).map(function (c) { return c.key; });
        visibleCols = ALL_KEYS.filter(function (key) {
            return locked.indexOf(key) !== -1 || nextCols.indexOf(key) !== -1;
        });
        saveVisibleCols(visibleCols);
        applyColumnVisibility();
        updateVisibleCountBadge();
        syncPresetButtons();
        if (!options.skipChecklist) {
            syncChecklistInputs();
        }
    }

    function buildChecklist() {
        const list = document.getElementById('teachersColumnsChecklist');
        if (!list) {
            return;
        }

        list.innerHTML = '';

        COLUMN_DEFS.forEach(function (col) {
            if (col.locked) {
                return;
            }

            const label = document.createElement('label');
            label.className = 'tv-columns-menu__item';

            const input = document.createElement('input');
            input.type = 'checkbox';
            input.className = 'form-check-input';
            input.setAttribute('data-tv-col-key', col.key);
            input.checked = visibleCols.indexOf(col.key) !== -1;

            input.addEventListener('change', function () {
                const key = col.key;
                let next = visibleCols.slice();
                if (input.checked) {
                    if (next.indexOf(key) === -1) {
                        next.push(key);
                    }
                } else {
                    next = next.filter(function (k) { return k !== key; });
                }
                next = ALL_KEYS.filter(function (k) { return next.indexOf(k) !== -1; });
                setVisibleCols(next);
            });

            const text = document.createElement('span');
            text.textContent = col.label;

            label.appendChild(input);
            label.appendChild(text);
            list.appendChild(label);
        });
    }

    function bindPresets() {
        document.querySelectorAll('[data-tv-columns-preset]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const presetKey = btn.getAttribute('data-tv-columns-preset');
                const preset = PRESETS[presetKey];
                if (!preset) {
                    return;
                }
                setVisibleCols(preset);
            });
        });
    }

    function init() {
        visibleCols = loadVisibleCols();
        buildChecklist();
        bindPresets();
        setVisibleCols(visibleCols, { skipChecklist: true });
        syncChecklistInputs();
    }

    window.TeachersTableColumns = {
        init: init,
        refresh: applyColumnVisibility,
    };

    document.addEventListener('DOMContentLoaded', init);
})();
