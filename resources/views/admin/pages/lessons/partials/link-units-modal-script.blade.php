<script>
(function () {
    function esc(s) {
        if (s == null || s === '') return '';
        var div = document.createElement('div');
        div.textContent = String(s);
        return div.innerHTML;
    }

    function syncLessonLinkedTargetsEmptyHint() {
        var listEl = document.getElementById('linkedUnitsListLesson');
        var hint = document.getElementById('linkedUnitsListLessonEmptyHint');
        if (!listEl || !hint) return;
        var hasRows = listEl.querySelectorAll('.linked-unit-target-row').length > 0;
        hint.classList.toggle('d-none', hasRows);
    }

    function appendLessonLinkedTargetRow(listEl, index, unitMeta) {
        if (!listEl || !unitMeta || !unitMeta.id) return;
        var badgeText = (typeof window.formatLinkedUnitBadge === 'function')
            ? window.formatLinkedUnitBadge(unitMeta)
            : (unitMeta.title || '#' + unitMeta.id);
        var row = document.createElement('div');
        row.className = 'd-flex align-items-center gap-2 mb-1 linked-unit-target-row';
        row.setAttribute('data-unit-id', String(unitMeta.id));
        row.innerHTML = '<span class="badge bg-secondary text-wrap text-start" style="max-width: 100%; white-space: normal;">' + esc(badgeText) + '</span>' +
            '<input type="hidden" name="linked_targets[' + index + '][unit_id]" value="' + esc(String(unitMeta.id)) + '">' +
            '<button type="button" class="btn btn-sm btn-outline-danger py-0 remove-lesson-linked-unit" title="إزالة"><i class="bi bi-x"></i></button>';
        listEl.appendChild(row);
        syncLessonLinkedTargetsEmptyHint();
    }

    function reindexLessonLinkedTargetRows(listEl) {
        if (!listEl) return;
        listEl.querySelectorAll('.linked-unit-target-row').forEach(function (row, index) {
            row.querySelectorAll('input[type="hidden"]').forEach(function (input) {
                input.name = 'linked_targets[' + index + '][unit_id]';
            });
        });
        syncLessonLinkedTargetsEmptyHint();
    }

    function resetLessonLinkUnitPicker() {
        var unitSelect = document.getElementById('lessonLinkUnitSelect');
        var sectionSelect = document.getElementById('lessonLinkSectionSelect');
        var addBtn = document.getElementById('addLessonLinkedUnitBtn');
        if (unitSelect) {
            unitSelect.innerHTML = '<option value="">-- اختر الوحدة --</option>';
            unitSelect.disabled = true;
        }
        if (sectionSelect) {
            sectionSelect.innerHTML = '<option value="">-- اختر القسم --</option>';
            sectionSelect.disabled = true;
        }
        if (addBtn) addBtn.disabled = true;
    }

    function resetLessonLinkPicker() {
        var classSelect = document.getElementById('lessonLinkClassSelect');
        if (classSelect) classSelect.value = '';
        resetLessonLinkUnitPicker();
    }

    function tryAddPendingLessonLinkTarget() {
        var form = document.getElementById('linkLessonUnitsForm');
        var listEl = document.getElementById('linkedUnitsListLesson');
        var unitSelect = document.getElementById('lessonLinkUnitSelect');
        if (!listEl || !unitSelect || !form) return false;
        var primaryUnitId = form.getAttribute('data-primary-unit-id') || '';
        var unitId = unitSelect.value;
        if (!unitId) return false;
        if (String(unitId) === String(primaryUnitId)) {
            alert('لا يمكن ربط الدرس بنفس الوحدة الأصلية. اختر وحدة أخرى.');
            return false;
        }

        var sectionSelect = document.getElementById('lessonLinkSectionSelect');
        var subjectSelect = document.getElementById('lessonLinkSubjectSelect');
        var classSelect = document.getElementById('lessonLinkClassSelect');
        var selection = window.getCurriculumCascadeSelection
            ? window.getCurriculumCascadeSelection(classSelect, subjectSelect, sectionSelect, unitSelect)
            : null;

        var existingRows = listEl.querySelectorAll('.linked-unit-target-row');
        for (var i = 0; i < existingRows.length; i++) {
            if (String(existingRows[i].getAttribute('data-unit-id')) === String(unitId)) {
                existingRows[i].remove();
                break;
            }
        }

        var meta = {
            id: unitId,
            title: selection && selection.unit_option ? (selection.unit_option.dataset.unitTitle || selection.unit_label) : '',
            section_title: selection ? selection.section_label : '',
            subject_name: selection ? selection.subject_label : '',
            class_name: selection ? selection.class_label : '',
            stage_name: '',
            label: selection
                ? [selection.class_label, selection.subject_label, selection.section_label, selection.unit_label].filter(Boolean).join(' — ')
                : ''
        };
        if (typeof window.formatLinkedUnitBadge === 'function' && !meta.label) {
            meta.label = window.formatLinkedUnitBadge(meta);
        }

        var nextIndex = listEl.querySelectorAll('.linked-unit-target-row').length;
        appendLessonLinkedTargetRow(listEl, nextIndex, meta);
        reindexLessonLinkedTargetRows(listEl);
        resetLessonLinkUnitPicker();
        return true;
    }

    document.addEventListener('DOMContentLoaded', function () {
        var linkLessonUnitsModalEl = document.getElementById('linkLessonUnitsModal');
        if (linkLessonUnitsModalEl && window.adminLessonsLinkUnitsBase) {
            linkLessonUnitsModalEl.addEventListener('show.bs.modal', function (e) {
                var form = document.getElementById('linkLessonUnitsForm');
                var titleEl = document.getElementById('linkLessonUnitsModalTitle');
                var currentLinkedEl = document.getElementById('currentLinkedUnitsLesson');
                var listEl = document.getElementById('linkedUnitsListLesson');
                var trigger = e.relatedTarget;
                if (!form || !titleEl || !listEl) return;
                var lessonId = trigger && trigger.getAttribute('data-lesson-id');
                var lessonTitle = trigger && trigger.getAttribute('data-lesson-title') || '';
                var primaryUnitId = trigger && trigger.getAttribute('data-lesson-primary-unit-id') || '';
                if (lessonId) {
                    form.action = window.adminLessonsLinkUnitsBase + '/' + lessonId + '/link-units';
                    form.setAttribute('data-primary-unit-id', primaryUnitId);
                    titleEl.textContent = 'ربط الدرس بوحدات إضافية' + (lessonTitle ? ': ' + lessonTitle : '');
                }
                function fillLinkedUnitsUI(linkedUnits) {
                    linkedUnits = Array.isArray(linkedUnits) ? linkedUnits : [];
                    if (currentLinkedEl) {
                        if (linkedUnits.length === 0) {
                            currentLinkedEl.innerHTML = '<span class="text-muted">لا يوجد ربط لوحدات إضافية</span>';
                        } else {
                            var parts = linkedUnits.map(function (u) {
                                var badge = (typeof window.formatLinkedUnitBadge === 'function') ? window.formatLinkedUnitBadge(u) : (u.title || '#' + u.id);
                                return '<span class="badge bg-secondary me-1 mb-1">' + esc(badge || u.title || '#' + u.id) + '</span>';
                            });
                            currentLinkedEl.innerHTML = parts.join('');
                        }
                    }
                    listEl.querySelectorAll('.linked-unit-target-row').forEach(function (row) { row.remove(); });
                    linkedUnits.forEach(function (u, index) {
                        if (primaryUnitId && String(u.id) === String(primaryUnitId)) return;
                        appendLessonLinkedTargetRow(listEl, index, u);
                    });
                    reindexLessonLinkedTargetRows(listEl);
                    window.lessonLinkInitialRowCount = listEl.querySelectorAll('.linked-unit-target-row').length;
                }
                if (currentLinkedEl) currentLinkedEl.innerHTML = '<span class="text-muted">جاري التحميل...</span>';
                listEl.querySelectorAll('.linked-unit-target-row').forEach(function (row) { row.remove(); });
                syncLessonLinkedTargetsEmptyHint();
                resetLessonLinkPicker();
                var linkedUrl = window.adminLessonsLinkUnitsBase + '/' + lessonId + '/linked-units';
                fetch(linkedUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (res) { return res.json(); })
                    .then(function (linkedUnits) { fillLinkedUnitsUI(linkedUnits); })
                    .catch(function () { fillLinkedUnitsUI([]); });
            });
        }

        document.addEventListener('click', function (e) {
            var addBtn = e.target.closest('#addLessonLinkedUnitBtn');
            if (addBtn) {
                if (!tryAddPendingLessonLinkTarget()) {
                    var unitSelect = document.getElementById('lessonLinkUnitSelect');
                    if (!unitSelect || !unitSelect.value) {
                        alert('يرجى اختيار الصف والمادة والقسم والوحدة قبل الإضافة');
                    }
                }
                return;
            }
            if (e.target.closest('.remove-lesson-linked-unit')) {
                var row = e.target.closest('.linked-unit-target-row');
                var listEl = document.getElementById('linkedUnitsListLesson');
                if (row) row.remove();
                reindexLessonLinkedTargetRows(listEl);
            }
        });

        var linkLessonUnitsForm = document.getElementById('linkLessonUnitsForm');
        if (linkLessonUnitsForm) {
            linkLessonUnitsForm.addEventListener('submit', function (e) {
                var listEl = document.getElementById('linkedUnitsListLesson');
                var unitSelect = document.getElementById('lessonLinkUnitSelect');
                var hadPendingSelection = !!(unitSelect && unitSelect.value);
                if (hadPendingSelection) {
                    if (!tryAddPendingLessonLinkTarget()) {
                        e.preventDefault();
                        return;
                    }
                }
                var rowCount = listEl ? listEl.querySelectorAll('.linked-unit-target-row').length : 0;
                var initialCount = window.lessonLinkInitialRowCount || 0;
                if (rowCount === 0 && initialCount === 0) {
                    e.preventDefault();
                    alert('لم تُضف أي وحدة للربط. اختر الصف والمادة والقسم والوحدة الهدف ثم اضغط حفظ الربط.');
                    return;
                }
                if (rowCount === 0 && initialCount > 0) {
                    if (!confirm('سيتم إزالة كل الروابط الحالية لهذا الدرس من الوحدات الأخرى. متابعة؟')) {
                        e.preventDefault();
                    }
                }
            });
        }
    });
})();
</script>
